<?php
/**
 * Created by PhpStorm.
 * User: daniil
 * Date: 25.10.16
 * Time: 20:21
 */

namespace php\interfaces;

use php\external\User;

/**
 * Interface EventInterface
 * @package php\interfaces
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
