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
     * @param array $user_info [
     *  'user' => UserInterface,
     *  'connection' => Connection,
     *  'recipient' => UserInterface (maybe null),
     *  'info' => array (connection info)
     * ]
     */
    public function onConnect(array $user_info);

    /**
     * @param array $user_info [
     *  'user' => UserInterface,
     *  'connection' => Connection,
     *  'recipient' => UserInterface (maybe null),
     *  'info' => array (connection info)
     * ]
     */
    public function onDisconnect(array $user_info);

    /**
     * @param array $user_info [
     *  'user' => UserInterface,
     *  'connection' => Connection,
     *  'recipient' => UserInterface (maybe null),
     *  'info' => array (connection info)
     * ]
     */
    public function onChangeRecipient(array $user_info);
}
