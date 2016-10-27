<?php
/**
 * Created by PhpStorm.
 * User: daniil
 * Date: 25.10.16
 * Time: 18:34
 */

namespace chat\interfaces;

use chat\external\User;

/**
 * Interface SystemIterface
 * @package chat\interfaces
 */
interface SystemIterface
{
    const TYPE_USER_DISCONNECTED = 'user_disconnected';
    const TYPE_USER_CONNECTED = 'user_connected';
    const TYPE_USER_LIST = 'user_list';
    const TYPE_USER_HISTORY = 'user_history';
    const TYPE_USER_REMOVED = 'user_removed';

    const COMMAND_GET_USER_LIST = 'getUserList';
    const COMMAND_GET_MESSAGE_HISTORY = 'getMessageHistory';

    /**
     * @param string $system
     * @param int $user_id
     *
     * @return string
     */
    public static function prepareToSend($type, $data = [], User $user = null);
}
