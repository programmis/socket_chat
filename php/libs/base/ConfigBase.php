<?php
/**
 * Created by PhpStorm.
 * User: daniil
 * Date: 25.10.16
 * Time: 18:01
 */

namespace php\libs\base;

use php\interfaces\ChatInterface;
use php\interfaces\ConfigInterface;
use Psr\Log\LoggerInterface;

/**
 * Class ConfigBase
 * @package php\libs\base
 */
abstract class ConfigBase implements ConfigInterface
{
    /**
     * @return LoggerInterface
     */
    public static function getLoggerClass()
    {
        $config = self::class;

        return $config::LOGGER_CLASS;
    }

    /**
     * @return ChatInterface
     */
    public static function getChatClass()
    {
        $config = self::class;

        return $config::CHAT_CLASS;
    }

    /** @inheritdoc */
    public static function getMessageClass()
    {
        $config = self::class;

        return $config::MESSAGE_CLASS;
    }

    /** @inheritdoc */
    public static function getUserClass()
    {
        $config = self::class;

        return $config::USER_CLASS;
    }

    /** @inheritdoc */
    public static function getSystemClass()
    {
        $config = self::class;

        return $config::SYSTEM_CLASS;
    }

    /** @inheritdoc */
    public static function getEventClass()
    {
        $config = self::class;

        return $config::EVENT_CLASS;
    }
}
