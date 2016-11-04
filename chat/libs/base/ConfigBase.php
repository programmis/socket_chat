<?php
/**
 * Created by PhpStorm.
 * User: daniil
 * Date: 25.10.16
 * Time: 18:01
 */

namespace chat\libs\base;

use chat\interfaces\ConfigInterface;

/**
 * Class ConfigBase
 * @package chat\libs\base
 */
abstract class ConfigBase implements ConfigInterface
{
    /** @inheritdoc */
    public static function getTextClass()
    {
        $config = self::class;

        return $config::TEXT_CLASS;
    }

    /** @inheritdoc */
    public static function getMessageProcessorClass()
    {
        $config = self::class;

        return $config::MESSAGE_PROCESSOR_CLASS;
    }

    /** @inheritdoc */
    public static function getUserProcessorClass()
    {
        $config = self::class;

        return $config::USER_PROCESSOR_CLASS;
    }

    /** @inheritdoc */
    public static function getLoggerClass()
    {
        $config = self::class;

        return $config::LOGGER_CLASS;
    }

    /** @inheritdoc */
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

    /** @inheritdoc */
    public static function getSecurityClass()
    {
        $config = self::class;

        return $config::SECURITY_CLASS;
    }
}
