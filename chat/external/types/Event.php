<?php
/**
 * Created by PhpStorm.
 * User: daniil
 * Date: 25.10.16
 * Time: 20:21
 */

namespace chat\external\types;

use chat\external\User;
use chat\interfaces\EventInterface;

/**
 * Class Event
 * @package chat\external\types
 */
class Event implements EventInterface
{
    /** @inheritdoc */
    public static function prepareToSend($event, User $sender, $data)
    {
        return [
            User::CONTAINER => $sender->getInfo(),
            'event' => $event,
            'data' => $data
        ];
    }
}
