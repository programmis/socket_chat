<?php
/**
 * Created by PhpStorm.
 * User: daniil
 * Date: 25.10.16
 * Time: 18:34
 */

namespace chat\interfaces;

/**
 * Interface SystemIterface
 *
 * @package chat\interfaces
 */
interface SystemIterface
{
    const CONTAINER = 'data';

    const TYPE_USER_DISCONNECTED = 'user_disconnected';
    const TYPE_USER_CONNECTED = 'user_connected';
    const TYPE_USER_LIST = 'user_list';
    const TYPE_USER_INFO = 'user_info';
    const TYPE_USER_ABOUT_ME_INFO = 'user_about_me_info';
    const TYPE_USER_HISTORY = 'user_history';
    const TYPE_USER_REMOVED = 'user_removed';

    const COMMAND_GET_USER_LIST = 'getUserList';
    const COMMAND_GET_USER_INFO = 'getUserInfo';
    const COMMAND_GET_INFO_ABOUT_ME = 'getInfoAboutMe';
    const COMMAND_GET_MESSAGE_HISTORY = 'getMessageHistory';

    /**
     * @param string        $type
     * @param array         $data
     * @param UserInterface $user
     *
     * @return array
     */
    public static function prepareToSend($type, $data = [], UserInterface $user = null);
}
