<?php
/**
 * Created by PhpStorm.
 * User: daniil
 * Date: 25.10.16
 * Time: 20:21
 */

namespace php\external\types;

use php\external\User;
use php\interfaces\EventInterface;

/**
 * Class Event
 * @package php\external\types
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
