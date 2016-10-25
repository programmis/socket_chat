<?php
/**
 * Created by PhpStorm.
 * User: daniil
 * Date: 25.10.16
 * Time: 16:47
 */

namespace php\interfaces;

/**
 * Interface MessageInterface
 * @package php\interfaces
 */
interface MessageInterface
{
    const TYPE_TEXT = 'text';
    const TYPE_EVENT = 'event';
    const TYPE_SYSTEM = 'system';

    const CONTAINER = 'message';
}
