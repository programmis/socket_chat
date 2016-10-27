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

    /** @var StreamSelectLoop */
    protected $loop;
    /** @var \React\Socket\Server */
    protected $socket;

    /** @var int $port */
    public $port = 1337;
    /** @var string $listen_host */
    public $listen_host = '0.0.0.0';
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
        $config = self::getConfigClass();
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
        $chat = $chat::getInstance();
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

        $this->loop = Factory::create();
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
                    self::log(print_r($ex->getMessage()), LogLevel::CRITICAL);
                }
            });
            $conn->on('close', function () use ($info) {
                $this->chat->cleanConnections($info);

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
        $this->socket->listen($this->port);
        self::$instance = $this;
    }

    /**
     * Start server
     */
    public function start()
    {
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
    }

    /**
     * @param            $message
     * @param Connection $conn
     */
    public static function write($message, Connection $conn)
    {
        if (!$conn->isWritable()) {
            return;
        }
        $conn->write(Security::encode($message));
        self::log('Send message: ' . $message);
    }

    /**
     * @param       $level
     * @param       $message
     * @param array $context
     */
    public static function log($message, $level = LogLevel::INFO, array $context = array())
    {
        if (!self::$logger) {
            $config = self::getConfigClass();
            self::$logger = $config::getLoggerClass();
        }
        self::$logger->log($level, $message, $context);
    }

    /**
     * @return string
     */
    public static function fillJavaConstants()
    {
        $config = self::getConfigClass();
        $message = $config::getMessageClass();
        $chat = $config::getChatClass();
        $system = $config::getSystemClass();
        $event = $config::getEventClass();
        $user = $config::getUserClass();

        $default_room = $chat::DEFAULT_ROOM;
        $event_typing = $event::TYPING;
        $message_type_event = $message::TYPE_EVENT;
        $message_type_system = $message::TYPE_SYSTEM;
        $message_type_text = $message::TYPE_TEXT;
        $message_container = $message::CONTAINER;
        $user_container = $user::CONTAINER;
        $system_command_get_user_list = $system::COMMAND_GET_USER_LIST;
        $system_command_get_message_history = $system::COMMAND_GET_MESSAGE_HISTORY;
        $system_type_user_list = $system::TYPE_USER_LIST;
        $system_type_user_connected = $system::TYPE_USER_CONNECTED;
        $system_type_user_disconnected = $system::TYPE_USER_DISCONNECTED;
        $system_type_user_removed = $system::TYPE_USER_REMOVED;
        $system_type_user_history = $system::TYPE_USER_HISTORY;

        $js = <<<JS
                socketChat.DEFAULT_ROOM = "$default_room";
                socketChat.EVENT_TYPING = "$event_typing";
                socketChat.MESSAGE_TYPE_EVENT = "$message_type_event";
                socketChat.MESSAGE_TYPE_SYSTEM = "$message_type_system";
                socketChat.MESSAGE_TYPE_TEXT = "$message_type_text";
                socketChat.MESSAGE_CONTAINER = "$message_container";
                socketChat.USER_CONTAINER = "$user_container";
                socketChat.SYSTEM_COMMAND_GET_USER_LIST = "$system_command_get_user_list";
                socketChat.SYSTEM_COMMAND_GET_MESSAGE_HISTORY = "$system_command_get_message_history";
                socketChat.SYSTEM_TYPE_USER_CONNECTED = "$system_type_user_connected";
                socketChat.SYSTEM_TYPE_USER_LIST = "$system_type_user_list";
                socketChat.SYSTEM_TYPE_USER_DISCONNECTED = "$system_type_user_disconnected";
                socketChat.SYSTEM_TYPE_USER_REMOVED = "$system_type_user_removed";
                socketChat.SYSTEM_TYPE_USER_HISTORY = "$system_type_user_history";
JS;

        return $js;
    }
}
