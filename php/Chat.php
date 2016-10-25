<?php
/**
 * Created by PhpStorm.
 * User: daniil
 * Date: 21.10.16
 * Time: 11:23
 */

namespace php;

use php\external\Message;
use php\external\MessageProcessor;
use php\external\types\Event;
use php\external\types\System;
use php\external\types\Text;
use php\external\User;
use php\external\UserProcessor;
use php\interfaces\ChatInterface;
use php\interfaces\MessageProcessorInterface;
use php\interfaces\UserProcessorInterface;
use React\Socket\Connection;

/**
 * Class Chat
 *
 * @package php
 */
class Chat implements ChatInterface
{
    /** @var  Chat $instance */
    private static $instance;
    /** @var bool $is_create */
    private static $is_create = false;

    /** @var array $roomUsers [string $room][int user_id][
     *      'connection' => Connection,
     *      'class' => User
     * ]*/
    public $roomUsers = [];

    /** @var MessageProcessor $messageProcessor */
    public $messageProcessor = null;
    /** @var UserProcessor $userProcessor */
    public $userProcessor = null;


    /**
     * @return string
     */
    public static function getMessageProcessorClass()
    {
        return MessageProcessor::class;
    }

    /**
     * @return string
     */
    public static function getUserProcessorClass()
    {
        return UserProcessor::class;
    }


    /**
     * Chat constructor.
     *
     * @throws \Exception
     */
    public function __construct()
    {
        if (!self::$is_create) {
            throw new \Exception('Cannot create new object. Please use getInstance method');
        }
        $messageProcessor = self::getMessageProcessorClass();
        $messageProcessor = new $messageProcessor;
        if (!($messageProcessor instanceof MessageProcessorInterface)) {
            throw new \Exception('MessageProcessor class must implement MessageProcessorInterface');
        }
        $this->messageProcessor = $messageProcessor;

        $userProcessor = self::getUserProcessorClass();
        $userProcessor = new $userProcessor;
        if (!($userProcessor instanceof UserProcessorInterface)) {
            throw new \Exception('UserProcessor class must implement UserProcessorInterface');
        }
        $this->userProcessor = $userProcessor;

        self::$is_create = false;
    }

    /**
     * @return Chat
     */
    public static function getInstance()
    {
        self::$is_create = true;
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /** @inheritdoc */
    public function cleanConnections(array $connection_info)
    {
        $room = $connection_info['room'];

        $this->roomUsers[$room] = array_filter(
            $this->roomUsers[$room],
            function ($user) {
                $conn = $user[UserProcessorInterface::STRUCTURE_CONNECTION] ?? null;
                if (!$conn || !($conn instanceof Connection)) {
                    return false;
                }

                return $conn->isWritable();
            }
        );
    }

    /** @inheritdoc */
    public function createUser(Connection $conn, array $connection_info)
    {
        $room = $connection_info['room'];

        $user = $this->userProcessor->createUser($connection_info);

        $this->roomUsers[$room][$user->id][UserProcessorInterface::STRUCTURE_CONNECTION] = $conn;
        $this->roomUsers[$room][$user->id][UserProcessorInterface::STRUCTURE_CLASS] = $user;

        $data= System::prepareToSend(System::TYPE_USER_CONNECTED, [], $user->id);
        $message_json = $this->prepareDataToSend(Message::TYPE_SYSTEM, $data);

        $this->sendMessageToRoomUsers($message_json, $room, $user->id, true);

        return $user;
    }

    /** @inheritdoc */
    public function dataProcessing($data, array $connection_info)
    {
        $type = $data['type'] ?? false;
        $payload = $data['payload'] ?? '{}';

        switch ($type) {
            case self::DATA_TYPE_TEXT:
                $this->textProcessing(
                    json_decode($payload, true),
                    $connection_info['room'],
                    $connection_info['user']->id
                );
                break;
            default:
                break;
        }
    }

    /**
     * @param array $data
     * @param string $room
     * @param int $from_user_id
     */
    protected function textProcessing($data, $room, $from_user_id)
    {
        switch ($data['type'] ?? '') {
            case Message::TYPE_EVENT:
                $this->eventReceived($data[Message::CONTAINER], $room, $from_user_id);
                break;
            case Message::TYPE_TEXT:
                $this->textReceived($data[Message::CONTAINER], $room, $from_user_id);
                break;
            case Message::TYPE_SYSTEM:
                $this->systemMessageReceived($data[Message::CONTAINER], $room, $from_user_id);
                break;
        }
    }

    /**
     * @param array $data
     * @param string $room
     * @param int $from_user_id
     */
    protected function eventReceived($data, $room, $from_user_id)
    {
        switch ($data[Message::TYPE_EVENT]) {
            case Event::TYPING:
                $event_data = [];
                $event_type = Event::TYPING;
                break;
            default:
                list($event_type, $event_data) = $this->messageProcessor->event($data, $room, $from_user_id);
                break;
        }
        if (!$event_type) {
            return;
        }
        $data = Event::prepareToSend($event_type, $from_user_id, $event_data);
        $message_json = $this->prepareDataToSend(Message::TYPE_EVENT, $data);

        $this->sendMessageToRoomUsers($message_json, $room, $from_user_id, true);
    }

    /**
     * @param string $message_json (result of function Chat::prepareDataToSend)
     * @param string $room
     * @param int|null $user_id
     * @param bool $exclude
     */
    protected function sendMessageToRoomUsers($message_json, string $room, int $user_id = null, $exclude = false)
    {
        if ($user_id && !$exclude) {
            Server::write($message_json, $this->getUserConnection($room, $user_id));
        } else {
            foreach ($this->roomUsers[$room] as $key => $user) {
                if ($exclude && $key == $user_id) {
                    continue;
                }
                Server::write($message_json, $this->getUserConnection($room, $key));
            }
        }
    }

    /**
     * @param string $room
     * @param int $user_id
     *
     * @return Connection
     */
    public function getUserConnection($room, $user_id)
    {
        return $this->roomUsers[$room][$user_id][UserProcessorInterface::STRUCTURE_CONNECTION];
    }

    /**
     * @param array $data
     * @param string $room
     * @param int $from_user_id
     *
     * @throws \Exception
     */
    protected function textReceived($data, $room, $from_user_id)
    {
        $message_text = $this->messageProcessor->text($data);
        if (!$message_text) {
            return;
        }
        $data = Text::prepareToSend($from_user_id, $message_text);
        $message_json = $this->prepareDataToSend(Message::TYPE_TEXT, $data);

        if (!isset($data['user_id']) || !$data['user_id']) {
            $this->sendMessageToRoomUsers($message_json, $room);
        } else {
            if (!isset($this->roomUsers[$room][$data['user_id']])) {
                throw new \Exception('User not found');
            }
            $this->sendMessageToRoomUsers($message_json, $room, $data['user_id']);
        }
    }

    /**
     * @param string $message_type
     * @param array $data
     * @return string
     */
    protected function prepareDataToSend($message_type, $data)
    {
        $json = json_encode([
            'type' => $message_type,
            Message::CONTAINER => $data
        ]);

        return $json;
    }


    /**
     * @param string $room
     * @param int $for_user_id
     *
     * @return array
     */
    public function getRoomUserList($room, $for_user_id)
    {
        $user_list = [];

        foreach ($this->roomUsers[$room] as $user) {
            /** @var User $userClass */
            $userClass = $user[UserProcessorInterface::STRUCTURE_CLASS];
            if ($userClass->id == $for_user_id) {
                continue;
            }
            $user_list[] = $userClass->getInfo();
        }

        return $user_list;
    }

    /**
     * @param array $data
     * @param string $room
     * @param int $from_user_id
     */
    protected function systemMessageReceived($data, $room, $from_user_id)
    {
        switch ($data[Message::TYPE_SYSTEM]) {
            case System::COMMAND_GET_USER_LIST:
                $system_data = $this->getRoomUserList($room, $from_user_id);
                $system_type = System::TYPE_USER_LIST;
                break;
            default:
                list($system_type, $system_data) = $this->messageProcessor->system(
                    $data,
                    $room,
                    $from_user_id
                );
                break;
        }
        if (!$system_type) {
            return;
        }
        $data = System::prepareToSend($system_type, $system_data);
        $message_json = $this->prepareDataToSend(Message::TYPE_SYSTEM, $data);

        $this->sendMessageToRoomUsers($message_json, $room, $from_user_id);
    }
}
