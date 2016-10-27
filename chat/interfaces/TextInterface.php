<?php
/**
 * Created by PhpStorm.
 * User: daniil
 * Date: 25.10.16
 * Time: 21:15
 */

namespace chat\interfaces;

use chat\external\User;

/**
 * Interface TextInterface
 * @package chat\interfaces
 */
interface TextInterface
{
    /**
     * @param User $sender
     * @param string $message
     *
     * @return array
     */
    public static function prepareToSend(User $sender, $message);
}
