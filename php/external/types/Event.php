<?php
/**
 * Created by PhpStorm.
 * User: daniil
 * Date: 25.10.16
 * Time: 20:21
 */

namespace php\external\types;

use php\interfaces\EventInterface;

/**
 * Class Event
 * @package php\external\types
 */
class Event implements EventInterface
{
    /** @inheritdoc */
    public static function prepareToSend($event, int $user_id, $data)
    {
        return [
            'user' => [
                //TODO: GET FULL USER INFO
                'id' => $user_id
            ],
            'event' => $event,
            'data' => $data
        ];
    }
}
