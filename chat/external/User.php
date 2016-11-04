<?php
/**
 * Created by PhpStorm.
 * User: daniil
 * Date: 25.10.16
 * Time: 15:19
 */

namespace chat\external;

use chat\external\base\UserBase;
use chat\interfaces\UserInterface;

/**
 * Class User
 * @package chat\external
 */
class User extends UserBase implements UserInterface
{
    /** @var User[] $list */
    private static $list = [];
    /** @var array $access_list */
    public $access_list = [];

    /** @inheritdoc */
    public function getSendRight()
    {
        return self::RIGHT_SEND_TO_ANY_USER_IN_ROOM;
    }

    /** @inheritdoc */
    public function getAccessList()
    {
        return $this->access_list;
    }

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

        return null;
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
    public function onConnect(array $user_info)
    {
        $this->is_online = true;
    }

    /** @inheritdoc */
    public function onDisconnect(array $user_info)
    {
        $this->is_online = false;
    }

    /** @inheritdoc */
    public function onChangeRecipient(array $user_info)
    {
    }
}
