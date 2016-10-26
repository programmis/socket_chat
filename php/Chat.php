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

        foreach ($this->roomUsers[$room] as $roomUser) {
            /** @var Connection $connection */
            $connection = $roomUser[UserProcessor::STRUCTURE_CONNECTION];
            if (!$connection->isWritable()) {
                /** @var User $user */
                $user = $roomUser[UserProcessor::STRUCTURE_CLASS];
                $user->offline();

                $data = System::prepareToSend(System::TYPE_USER_DISCONNECTED, [], $user);
                $message_array = $this->prepareDataToSend(Message::TYPE_SYSTEM, $data);

                $this->sendMessageToRoomUsers($message_array, $room, $user, true);
            }
        }
    }

    /** @inheritdoc */
    public function createUser(Connection $conn, array $connection_info)
    {
        $room = $connection_info['room'];

        $user = $this->userProcessor->createUser($connection_info);

        $this->roomUsers[$room][$user->id][UserProcessor::STRUCTURE_CONNECTION] = $conn;
        $this->roomUsers[$room][$user->id][UserProcessor::STRUCTURE_CLASS] = $user;

        $data = System::prepareToSend(System::TYPE_USER_CONNECTED, [], $user);
        $message_array = $this->prepareDataToSend(Message::TYPE_SYSTEM, $data);

        $this->sendMessageToRoomUsers($message_array, $room, $user, true);

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
                    $connection_info[User::CONTAINER]
                );
                break;
            case self::DATA_TYPE_CLOSE:
                $this->closeUserConnection(
                    $connection_info['room'],
                    $connection_info[User::CONTAINER]->id
                );
                break;
            default:
                break;
        }
    }

    /**
     * @param string $room
     * @param int $user_id
     */
    public function closeUserConnection($room, $user_id)
    {
        /** @var Connection $connection */
        $connection = $this->roomUsers[$room][$user_id][UserProcessor::STRUCTURE_CONNECTION];

        $connection->close();
    }

    /**
     * @param array $data
     * @param string $room
     * @param User $sender
     */
    protected function textProcessing($data, $room, User $sender)
    {
        switch ($data['type'] ?? '') {
            case Message::TYPE_EVENT:
                $this->eventReceived($data[Message::CONTAINER], $room, $sender);
                break;
            case Message::TYPE_TEXT:
                $this->textReceived($data, $room, $sender);
                break;
            case Message::TYPE_SYSTEM:
                $this->systemMessageReceived($data[Message::CONTAINER], $room, $sender);
                break;
        }
    }

    /**
     * @param array $data
     * @param string $room
     * @param User $sender
     */
    protected function eventReceived($data, $room, User $sender)
    {
        switch ($data[Message::TYPE_EVENT]) {
            case Event::TYPING:
                $event_data = [];
                $event_type = Event::TYPING;
                break;
            default:
                list($event_type, $event_data) = $this->messageProcessor->event($data, $room, $sender);
                break;
        }
        if (!$event_type) {
            return;
        }
        $data = Event::prepareToSend($event_type, $sender, $event_data);
        $message_array = $this->prepareDataToSend(Message::TYPE_EVENT, $data);

        $this->sendMessageToRoomUsers($message_array, $room, $sender, true);
    }

    /**
     * @param array $message_array (result of function Chat::prepareDataToSend)
     * @param string $room
     * @param User|null $user
     * @param bool $exclude
     */
    protected function sendMessageToRoomUsers($message_array, string $room, User $user = null, $exclude = false)
    {
        $message_json = json_encode($message_array);

        if ($user && !$exclude) {
            if ($message_array['type'] == Message::TYPE_TEXT) {
                $this->saveMessage(
                    $message_array[Message::CONTAINER][Message::TYPE_TEXT],
                    $message_array[Message::CONTAINER][User::CONTAINER]['id'],
                    $user->id,
                    $message_array[Message::CONTAINER]['date']
                );
            }
            Server::write($message_json, $this->getUserConnection($room, $user->id));
        } else {
            foreach ($this->roomUsers[$room] as $key => $roomUser) {
                if ($user && $exclude && $key == $user->id) {
                    continue;
                }
                if ($message_array['type'] == Message::TYPE_TEXT) {
                    $this->saveMessage(
                        $message_array[Message::CONTAINER][Message::TYPE_TEXT],
                        $message_array[Message::CONTAINER][User::CONTAINER]['id'],
                        $key,
                        $message_array[Message::CONTAINER]['date']
                    );
                }
                Server::write($message_json, $this->getUserConnection($room, $key));
            }
        }
    }

    /**
     * @param string $text
     * @param int $sender_id
     * @param int $recipient_id
     * @param $date
     *
     * @return bool
     */
    public function saveMessage($text, $sender_id, $recipient_id, $date)
    {
        $sender = User::findOne($sender_id);
        $recipient = User::findOne($recipient_id);

        $message = new Message();
        $message->text = $text;
        $message->sender = $sender;
        $message->recipient = $recipient;
        $message->date = $date;

        return $message->save();
    }

    /**
     * @param string $room
     * @param int $user_id
     *
     * @return Connection
     */
    public function getUserConnection($room, $user_id)
    {
        return $this->roomUsers[$room][$user_id][UserProcessor::STRUCTURE_CONNECTION];
    }

    /**
     * @param array $inner_data
     * @param string $room
     * @param User $sender
     *
     * @throws \Exception
     */
    protected function textReceived($inner_data, $room, User $sender)
    {
        $message_text = $this->messageProcessor->text($inner_data[Message::CONTAINER]);
        if (!$message_text) {
            return;
        }

        $data = Text::prepareToSend($sender, $message_text);
        $message_array = $this->prepareDataToSend(Message::TYPE_TEXT, $data);

        if (!isset($inner_data['recipient_id']) || !$inner_data['recipient_id']) {
            $this->sendMessageToRoomUsers($message_array, $room);
        } else {
            $recipient = User::findOne($inner_data['recipient_id']);
            if (!$recipient || !isset($this->roomUsers[$room][$recipient->id])) {
                throw new \Exception('User not found');
            }
            $this->sendMessageToRoomUsers($message_array, $room, $recipient);
            $this->sendMessageToRoomUsers($message_array, $room, $sender);
        }
    }

    /**
     * @param string $message_type
     * @param array $data
     *
     * @return array
     */
    protected function prepareDataToSend($message_type, $data)
    {
        return [
            'type' => $message_type,
            Message::CONTAINER => $data
        ];
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
            $userClass = $user[UserProcessor::STRUCTURE_CLASS];
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
     * @param User $sender
     */
    protected function systemMessageReceived($data, $room, User $sender)
    {
        switch ($data[Message::TYPE_SYSTEM]) {
            case System::COMMAND_GET_USER_LIST:
                $system_data = $this->getRoomUserList($room, $sender->id);
                $system_type = System::TYPE_USER_LIST;
                break;
            case System::COMMAND_GET_MESSAGE_HISTORY:
                $system_data = Message::getMessageHistoryByPeriod(
                    $sender->id,
                    $data['data']['with_user_id'],
                    $data['data']['period']
                );
                $system_type = System::TYPE_USER_HISTORY;
                break;
            default:
                list($system_type, $system_data) = $this->messageProcessor->system(
                    $data,
                    $room,
                    $sender
                );
                break;
        }
        if (!$system_type) {
            return;
        }
        $data = System::prepareToSend($system_type, $system_data);
        $message_array = $this->prepareDataToSend(Message::TYPE_SYSTEM, $data);

        $this->sendMessageToRoomUsers($message_array, $room, $sender);
    }
}
