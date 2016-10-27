<?php
/**
 * Created by PhpStorm.
 * User: daniil
 * Date: 25.10.16
 * Time: 20:21
 */

namespace chat\interfaces;

use chat\external\User;

/**
 * Interface EventInterface
 * @package chat\interfaces
 */
interface EventInterface
{
    const TYPING = 'typing';

    /**
     * @param string $event
     * @param User $sender
     * @param array $data
     *
     * @return array
     */
    public static function prepareToSend($event, User $sender, $data);
}
