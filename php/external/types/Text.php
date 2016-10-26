<?php
/**
 * Created by PhpStorm.
 * User: daniil
 * Date: 25.10.16
 * Time: 21:15
 */

namespace php\external\types;

use php\external\User;
use php\interfaces\TextInterface;

/**
 * Class Text
 * @package php\external\types
 */
class Text implements TextInterface
{
    /** @inheritdoc */
    public static function prepareToSend(User $sender, $message)
    {
        $data = [
            'text' => $message,
            'date' => date('Y-m-d H:i:s')
        ];
        if ($sender) {
            $data = array_merge($data, [
                'user' => $sender->getInfo()
            ]);
        }
        return $data;
    }
}
