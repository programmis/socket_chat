<?php
/**
 * Created by PhpStorm.
 * User: daniil
 * Date: 25.10.16
 * Time: 13:05
 */

namespace php\external\types;

use php\external\User;
use php\interfaces\SystemIterface;

/**
 * Class System
 * @package php\libs
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
