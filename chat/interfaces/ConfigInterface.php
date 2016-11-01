<?php
/**
 * Created by PhpStorm.
 * User: daniil
 * Date: 25.10.16
 * Time: 16:51
 */

namespace chat\interfaces;

use chat\Chat;
use chat\external\Message;
use chat\external\MessageProcessor;
use chat\external\types\Event;
use chat\external\types\System;
use chat\external\types\Text;
use chat\external\User;
use chat\external\UserProcessor;
use logger\Logger;
use Psr\Log\LoggerInterface;

/**
 * Interface ConfigInterface
 * @package chat\interfaces
 */
interface ConfigInterface
{
    const MESSAGE_CLASS = Message::class;
    const USER_CLASS = User::class;
    const CHAT_CLASS = Chat::class;
    const LOGGER_CLASS = Logger::class;
    const SYSTEM_CLASS = System::class;
    const TEXT_CLASS = Text::class;
    const EVENT_CLASS = Event::class;
    const MESSAGE_PROCESSOR_CLASS = MessageProcessor::class;
    const USER_PROCESSOR_CLASS = UserProcessor::class;

    /** @return UserProcessorInterface */
    public static function getUserProcessorClass();

    /** @return MessageProcessorInterface */
    public static function getMessageProcessorClass();

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

    /** @return TextInterface */
    public static function getTextClass();
}
