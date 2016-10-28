<?php
/**
 * Created by PhpStorm.
 * User: daniil
 * Date: 25.10.16
 * Time: 21:15
 */

namespace chat\external\types;

use chat\external\User;
use chat\interfaces\TextInterface;
use chat\interfaces\UserInterface;

/**
 * Class Text
 * @package chat\external\types
 */
class Text implements TextInterface
{
    /** @inheritdoc */
    public static function prepareToSend(UserInterface $sender, $message)
    {
        $data = [
            'text' => $message,
            'date' => date('Y-m-d H:i:s')
        ];
        if ($sender) {
            $data = array_merge($data, [
                User::CONTAINER => $sender->getInfo()
            ]);
        }
        return $data;
    }
}
