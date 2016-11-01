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

    /** is default */
    const RIGHT_SEND_TO_ANY_USER_IN_ROOM = 1;
    /** get list from User->getAccessList */
    const RIGHT_SEND_TO_ANY_USER_IN_LIST = 2;

    /** @return int const RIGHT_SEND_TO_ANY_USER_IN_* */
    public function getSendRight();

    /** @return array [user.id, user.id, ...] */
    public function getAccessList();

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
