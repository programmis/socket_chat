<?php
/**
 * Created by PhpStorm.
 * User: daniil
 * Date: 25.10.16
 * Time: 18:34
 */

namespace php\interfaces;

/**
 * Interface SystemIterface
 * @package php\interfaces
 */
interface SystemIterface
{
    const TYPE_USER_CONNECTED = 'user_connected';
    const TYPE_USER_LIST = 'user_list';

    const COMMAND_GET_USER_LIST = 'getUserList';

    /**
     * @param string $system
     * @param int $user_id
     *
     * @return string
     */
    public static function prepareToSend($type, $data = [], int $user_id = null);
}
