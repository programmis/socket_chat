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
    /** @var User[] $list */
    private static $list = [];

    /**
     * User constructor.
     */
    public function __construct()
    {
        self::$list[] = $this;
    }

    /** @inheritdoc */
    public static function findOne($user_id)
    {
        foreach (self::$list as $user) {
            if ($user->id == $user_id) {
                return $user;
            }
        }

        return false;
    }

    /** @inheritdoc */
    public function online()
    {
        $this->is_online = true;
    }

    /** @inheritdoc */
    public function offline()
    {
        $this->is_online = false;
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
        $this->online();

        return true;
    }
}
