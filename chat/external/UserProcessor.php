<?php
/**
 * Created by PhpStorm.
 * User: daniil
 * Date: 21.10.16
 * Time: 12:01
 */

namespace chat\external;

use chat\interfaces\UserInterface;
use chat\interfaces\UserProcessorInterface;
use chat\Server;

/**
 * Class User
 *
 * @package chat
 */
class UserProcessor implements UserProcessorInterface
{
    private static $user_cnt = 1;

    /** @inheritdoc */
    public function createUser(array $connection_info)
    {
        $config = Server::getConfigClass();

        $user = $config::getUserClass();

        /** @var User $user */
        $user = new $user;
        if (!($user instanceof UserInterface)) {
            throw new \Exception('User class must implement UserInterface');
        }
        $user->id = self::$user_cnt;

        self::$user_cnt++;

        return $user;
    }
}
