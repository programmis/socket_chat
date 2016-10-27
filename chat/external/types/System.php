<?php
/**
 * Created by PhpStorm.
 * User: daniil
 * Date: 25.10.16
 * Time: 13:05
 */

namespace chat\external\types;

use chat\external\User;
use chat\interfaces\SystemIterface;

/**
 * Class System
 * @package chat\libs
 */
class System implements SystemIterface
{
    /** @inheritdoc */
    public static function prepareToSend($type, $data = [], User $user = null)
    {
        $message = [
            'system' => $type,
            'data' => $data
        ];
        switch ($type) {
            case self::TYPE_USER_CONNECTED:
            case self::TYPE_USER_DISCONNECTED:
                $message = array_merge($message, [
                    User::CONTAINER => $user->getInfo()
                ]);
                break;
        }

        return $message;
    }
}
