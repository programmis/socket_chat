<?php
/**
 * Created by PhpStorm.
 * User: daniil
 * Date: 21.10.16
 * Time: 12:01
 */

namespace php\external;

use php\interfaces\UserInterface;
use php\interfaces\UserProcessorInterface;
use php\Server;

/**
 * Class User
 *
 * @package php
 */
class UserProcessor implements UserProcessorInterface
{
    /** @inheritdoc */
    public function createUser(array $connection_info)
    {
        $config = Server::$config;

        $user = $config::getUserClass();

        /** @var User $user */
        $user = new $user;
        if (!($user instanceof UserInterface)) {
            throw new \Exception('User class must implement UserInterface');
        }
        $user->fillByInfo($connection_info);

        return $user;
    }
}
