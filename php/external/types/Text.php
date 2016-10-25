<?php
/**
 * Created by PhpStorm.
 * User: daniil
 * Date: 25.10.16
 * Time: 21:15
 */

namespace php\external\types;

use php\interfaces\TextInterface;

/**
 * Class Text
 * @package php\external\types
 */
class Text implements TextInterface
{
    /** @inheritdoc */
    public static function prepareToSend(int $user_id, $message)
    {
        return [
            'user' => [
                //TODO: GET FULL USER INFO
                'id' => $user_id
            ],
            'text' => $message,
            'date' => date('Y-m-d H:i:s')
        ];
    }
}
