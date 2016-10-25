<?php
/**
 * Created by PhpStorm.
 * User: daniil
 * Date: 25.10.16
 * Time: 16:51
 */

namespace php\interfaces;

use php\Chat;
use php\external\Message;
use php\external\types\Event;
use php\external\types\System;
use php\external\User;
use php\libs\Logger;
use Psr\Log\LoggerInterface;

/**
 * Interface ConfigInterface
 * @package php\interfaces
 */
interface ConfigInterface
{
    const MESSAGE_CLASS = Message::class;
    const USER_CLASS = User::class;
    const CHAT_CLASS = Chat::class;
    const LOGGER_CLASS = Logger::class;
    const SYSTEM_CLASS = System::class;
    const EVENT_CLASS = Event::class;

    /** @return ChatInterface */
    public static function getChatClass();

    /** @return LoggerInterface */
    public static function getLoggerClass();

    /** @return MessageInterface */
    public static function getMessageClass();

    /** @return UserInterface */
    public static function getUserClass();

    /** @return SystemIterface */
    public static function getSystemClass();

    /** @return EventInterface */
    public static function getEventClass();
}
