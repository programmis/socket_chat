<?php
/**
 * Created by PhpStorm.
 * User: daniil
 * Date: 21.10.16
 * Time: 11:23
 */

namespace chat;

use chat\external\Message;
use chat\external\MessageProcessor;
use chat\external\types\Event;
use chat\external\types\System;
use chat\external\types\Text;
use chat\external\User;
use chat\external\UserProcessor;
use chat\interfaces\ChatInterface;
use chat\interfaces\ConfigInterface;
use chat\interfaces\MessageProcessorInterface;
use chat\interfaces\UserInterface;
use chat\interfaces\UserProcessorInterface;
use React\Socket\Connection;

/**
 * Class Chat
 *
 * @package chat
 */
class Chat implements ChatInterface
{
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

    /** @var Server $server */
    public $server;

    /**
     * Chat constructor.
     *
     * @throws \Exception
     */
    public function __construct(Server $server)
    {
        $this->server = $server;
        $config = $this->getConfigClass();

        $messageProcessor = $config::getMessageProcessorClass();
        $messageProcessor = new $messageProcessor;
        if (!($messageProcessor instanceof MessageProcessorInterface)) {
            throw new \Exception('MessageProcessor class must implement MessageProcessorInterface');
        }
        $this->messageProcessor = $messageProcessor;

        $userProcessor = $config::getUserProcessorClass();
        $userProcessor = new $userProcessor;
        if (!($userProcessor instanceof UserProcessorInterface)) {
            throw new \Exception('UserProcessor class must implement UserProcessorInterface');
        }
        $this->userProcessor = $userProcessor;

        self::$is_create = false;
    }

    /**
     * @return ConfigInterface
     */
    private function getConfigClass()
    {
        $server = $this->server;

        return $server::getConfigClass();
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
     * @param UserInterface $sender
     */
    protected function textProcessing($data, $room, UserInterface $sender)
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
     * @param UserInterface $sender
     */
    protected function eventReceived($data, $room, UserInterface $sender)
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
     * @param UserInterface|null $user
     * @param bool $exclude
     */
    protected function sendMessageToRoomUsers(
        $message_array,
        string $room,
        UserInterface $user = null,
        $exclude = false
    ) {
        /** @var User $user */
        $server = $this->server;
        $message_json = json_encode($message_array);
        $config = $this->getConfigClass();
        $message = $config::getMessageClass();

        if ($user && !$exclude) {
            if ($message_array['type'] == Message::TYPE_TEXT) {
                $message::addMessage(
                    $message_array[Message::CONTAINER][User::CONTAINER]['id'],
                    $user->id,
                    $message_array[Message::CONTAINER][Message::TYPE_TEXT],
                    []
                );
            }
            $server::write($message_json, $this->getUserConnection($room, $user->id));
        } else {
            foreach ($this->roomUsers[$room] as $key => $roomUser) {
                if ($user && $exclude && $key == $user->id) {
                    continue;
                }
                $sender_id = $message_array[Message::CONTAINER][User::CONTAINER]['id'] ?? 0;
                if ($message_array['type'] == Message::TYPE_TEXT &&
                    $sender_id != $key
                ) {
                    $message::addMessage(
                        $sender_id,
                        $key,
                        $message_array[Message::CONTAINER][Message::TYPE_TEXT],
                        []
                    );
                }
                $server::write($message_json, $this->getUserConnection($room, $key));
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
        return $this->roomUsers[$room][$user_id][UserProcessor::STRUCTURE_CONNECTION];
    }

    /**
     * @param array $inner_data
     * @param string $room
     * @param UserInterface $sender
     *
     * @throws \Exception
     */
    protected function textReceived($inner_data, $room, UserInterface $sender)
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
     * @param UserInterface $sender
     */
    protected function systemMessageReceived($data, $room, UserInterface $sender)
    {
        $config = $this->getConfigClass();

        /** @var User $sender */
        switch ($data[Message::TYPE_SYSTEM]) {
            case System::COMMAND_GET_USER_LIST:
                $system_data = $this->getRoomUserList($room, $sender->id);
                $system_type = System::TYPE_USER_LIST;
                break;
            case System::COMMAND_GET_USER_INFO:
                $user = $config::getUserClass();
                $user = $user::findOne($data[User::CONTAINER]['id'] ?? 0);
                if ($user) {
                    $system_data = [User::CONTAINER => $user->getInfo()];
                } else {
                    $system_data = [];
                }
                $system_type = System::TYPE_USER_INFO;
                break;
            case System::COMMAND_GET_MESSAGE_HISTORY:
                $message = $config::getMessageClass();
                $system_data = $message::getHistory(
                    $sender->id,
                    $data['data']['with_user_id'],
                    ['period' => $data['data']['period']]
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
