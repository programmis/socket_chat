<?php
/**
 * Created by PhpStorm.
 * User: daniil
 * Date: 20.10.16
 * Time: 16:19
 */

namespace chat;

use chat\external\User;
use chat\interfaces\ChatInterface;
use chat\interfaces\ConfigInterface;
use chat\interfaces\UserInterface;
use chat\libs\Config;
use chat\libs\Security;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use React\EventLoop\Factory;
use React\EventLoop\StreamSelectLoop;
use React\Socket\Connection;

/**
 * Class Server
 *
 * @package chat
 */
class Server
{
    /** @var Server $instance */
    private static $instance;
    /** @var LoggerInterface $logger */
    private static $logger;
    /** @var ConfigInterface $config */
    private static $config;
    /** @var int $port */
    public static $port = 1337;
    /** @var string $listen_host */
    public static $listen_host = '0.0.0.0';
    /** @var string $server_host */
    public static $server_host = '127.0.0.1';

    /** @var StreamSelectLoop */
    protected $loop;
    /** @var \React\Socket\Server */
    protected $socket;

    /** @var ChatInterface $chat */
    public $chat;

    /**
     * @return ConfigInterface
     */
    public static function getConfigClass()
    {
        return Config::class;
    }

    private function initConfig()
    {
        $config = static::getConfigClass();
        $config = new $config;
        if (!($config instanceof ConfigInterface)) {
            throw new \Exception('Config class must implement ConfigInterface');
        }
        self::$config = $config;
    }

    private function initLogger()
    {
        $config = self::$config;

        $logger = $config::getLoggerClass();
        $logger = new $logger;
        if (!($logger instanceof LoggerInterface)) {
            throw new \Exception('Logger class must implement LoggerInterface');
        }
        self::$logger = $logger;
    }

    private function initChat()
    {
        $config = self::$config;

        /** @var Chat $chat */
        $chat = $config::getChatClass();
        $chat = new $chat($this);
        if (!($chat instanceof ChatInterface)) {
            throw new \Exception('Chat class must implement ChatInterface');
        }
        $this->chat = $chat;
    }

    /**
     * Server constructor.
     */
    public function __construct()
    {
        $this->initConfig();
        $this->initLogger();
        $this->initChat();

        $this->loop   = Factory::create();
        $this->socket = new \React\Socket\Server($this->loop);
        $this->socket->on('connection', function (Connection $conn) {
            $info = Security::handshake($conn);
            if (!$info) {
                $conn->close();

                return false;
            }
            self::log(print_r($info, true), LogLevel::DEBUG);

            $info[User::CONTAINER] = $this->chat->createUser($conn, $info);

            $conn->on('data', function ($data) use ($info) {
                $data = Security::decode($data);

                self::log('data received: ' . print_r($data, true), LogLevel::DEBUG);
                try {
                    $this->chat->dataProcessing($data, $info);
                } catch (\Exception $ex) {
                    self::log(print_r($ex->getMessage(), true), LogLevel::CRITICAL);
                }
            });
            $conn->on('close', function () use ($info) {
                $this->chat->onCloseConnection($info);

                self::log('Connection close');
            });
            $conn->on('end', function () {
                self::log('Connection end');
            });
            $conn->on('error', function () {
                self::log('Connection error');
            });

            return true;
        });
        $this->socket->listen(self::$port, self::$listen_host);
        self::$instance = $this;
    }

    /**
     * Start server
     */
    public function start()
    {
        self::log('Server started');
        $this->loop->run();
    }

    /**
     * Daemon tick
     */
    public function tick()
    {
        $this->loop->tick();
    }

    /**
     * Stop server
     */
    public function stop()
    {
        $this->socket->shutdown();
        $this->loop->stop();
        self::log('Server stopped');
    }

    /**
     * @param array         $message_array
     * @param string        $room
     * @param UserInterface $sender
     * @param UserInterface $recipient
     */
    public static function write($message_array, $room, $sender, $recipient)
    {
        /** @var User $sender */
        /** @var User $recipient */
        if ($sender->id != $recipient->id
            && (
                $sender->getSendRight() == UserInterface::RIGHT_SEND_TO_ANY_USER_IN_LIST
                && !in_array($recipient->id, $sender->getAccessList())
            )
        ) {
            self::log("Can't send message, please check user #" . $sender->id . " right", LogLevel::ERROR);

            return;
        }
        $config       = self::$config;
        $messageClass = $config::getMessageClass();
        $messageClass::beforeSend($sender->id, $recipient->id, $message_array);
        $conn = self::$instance->chat->getUserConnection($room, $recipient->id);
        if (!$conn || !$conn->isWritable()) {
            self::log("Can't send message recipient is offline", LogLevel::ERROR);

            return;
        }
        $message_json = json_encode($message_array);
        $conn->write(Security::encode($message_json));
        $messageClass::afterSend($sender->id, $recipient->id, $message_array);
        self::log('Send message: ' . $message_json);
    }

    /**
     * @param       $level
     * @param       $message
     * @param array $context
     */
    public static function log($message, $level = LogLevel::INFO, array $context = array())
    {
        if (!self::$logger) {
            $config       = self::getConfigClass();
            self::$logger = $config::getLoggerClass();
        }
        self::$logger->log($level, $message, $context);
    }

    /**
     * @return string
     */
    public static function fillJavaConstants()
    {
        $config  = self::getConfigClass();
        $message = $config::getMessageClass();
        $chat    = $config::getChatClass();
        $system  = $config::getSystemClass();
        $event   = $config::getEventClass();
        $user    = $config::getUserClass();

        $default_room                       = $chat::DEFAULT_ROOM;
        $event_typing                       = $event::TYPING;
        $message_type_event                 = $message::TYPE_EVENT;
        $message_type_system                = $message::TYPE_SYSTEM;
        $message_type_text                  = $message::TYPE_TEXT;
        $message_container                  = $message::CONTAINER;
        $user_container                     = $user::CONTAINER;
        $system_command_get_user_list       = $system::COMMAND_GET_USER_LIST;
        $system_command_get_user_info       = $system::COMMAND_GET_USER_INFO;
        $system_command_get_message_history = $system::COMMAND_GET_MESSAGE_HISTORY;
        $system_type_user_list              = $system::TYPE_USER_LIST;
        $system_type_user_connected         = $system::TYPE_USER_CONNECTED;
        $system_type_user_disconnected      = $system::TYPE_USER_DISCONNECTED;
        $system_type_user_removed           = $system::TYPE_USER_REMOVED;
        $system_type_user_history           = $system::TYPE_USER_HISTORY;
        $system_type_user_info              = $system::TYPE_USER_INFO;

        $js = <<<JS
                socketChat.DEFAULT_ROOM = "$default_room";
                socketChat.EVENT_TYPING = "$event_typing";
                socketChat.MESSAGE_TYPE_EVENT = "$message_type_event";
                socketChat.MESSAGE_TYPE_SYSTEM = "$message_type_system";
                socketChat.MESSAGE_TYPE_TEXT = "$message_type_text";
                socketChat.MESSAGE_CONTAINER = "$message_container";
                socketChat.USER_CONTAINER = "$user_container";
                socketChat.SYSTEM_COMMAND_GET_USER_LIST = "$system_command_get_user_list";
                socketChat.SYSTEM_COMMAND_GET_USER_INFO = "$system_command_get_user_info";
                socketChat.SYSTEM_COMMAND_GET_MESSAGE_HISTORY = "$system_command_get_message_history";
                socketChat.SYSTEM_TYPE_USER_CONNECTED = "$system_type_user_connected";
                socketChat.SYSTEM_TYPE_USER_LIST = "$system_type_user_list";
                socketChat.SYSTEM_TYPE_USER_DISCONNECTED = "$system_type_user_disconnected";
                socketChat.SYSTEM_TYPE_USER_REMOVED = "$system_type_user_removed";
                socketChat.SYSTEM_TYPE_USER_HISTORY = "$system_type_user_history";
                socketChat.SYSTEM_TYPE_USER_INFO = "$system_type_user_info";
JS;

        return $js;
    }
}
