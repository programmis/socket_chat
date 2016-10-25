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
    public static function prepareToSend($type, $data = [], int $user_id = null)
    {
        $message = [
            'system' => $type,
            'data' => $data
        ];
        $user = User::findOne($user_id);
        if (!$user) {
            throw new \Exception("User #$user_id is not found");
        }
        switch ($type) {
            case self::TYPE_USER_CONNECTED:
                $message = array_merge($message, [
                    'user' => $user->getInfo()
                ]);
                break;
        }

        return $message;
    }
}
