<?php
/**
 * Created by PhpStorm.
 * User: daniil
 * Date: 25.10.16
 * Time: 15:19
 */

namespace php\external;

use php\external\base\UserBase;
use php\interfaces\UserInterface;

/**
 * Class User
 * @package php\external
 */
class User extends UserBase implements UserInterface
{
    /** @inheritdoc */
    public static function findOne($user_id)
    {
        $user = new self();
        $user->id = $user_id;

        return $user;
    }

    /** @inheritdoc */
    public function getInfo()
    {
        return [
            'id' => $this->id,
            'avatar' => $this->avatar,
            'last_name' => $this->last_name,
            'name' => $this->name,
            'is_online' => $this->is_online
        ];
    }

    /** @inheritdoc */
    public function fillByInfo(array $connection_info)
    {
        $this->id = rand(1, 999);

        return true;
    }
}
