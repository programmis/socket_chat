<?php
/**
 * Created by PhpStorm.
 * User: daniil
 * Date: 25.10.16
 * Time: 15:19
 */

namespace chat\interfaces;

use chat\external\User;

/**
 * Interface UserInterface
 * @package chat\interfaces
 */
interface UserInterface
{
    const CONTAINER = 'user';

    /**
     * @return array [
     *      id => 1,
     *      name => 'name',
     *      last_name => 'last_name',
     *      avatar => 'http://url/to/avatar/image.png'
     *      is_online => true
     * ]
     */
    public function getInfo();

    /**
     * @param array $connection_info
     *
     * @return bool
     */
    public function fillByInfo(array $connection_info);

    /**
     * @param int $user_id
     *
     * @return User
     */
    public static function findOne($user_id);

    /**
     * set is_online param to true
     */
    public function online();

    /**
     * set is_online param to false
     */
    public function offline();
}
