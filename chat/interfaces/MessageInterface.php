<?php
/**
 * Created by PhpStorm.
 * User: daniil
 * Date: 25.10.16
 * Time: 16:47
 */

namespace chat\interfaces;

use chat\external\Message;

/**
 * Interface MessageInterface
 * @package chat\interfaces
 */
interface MessageInterface
{
    const TYPE_TEXT = 'text';
    const TYPE_EVENT = 'event';
    const TYPE_SYSTEM = 'system';

    const CONTAINER = 'message';

    /**
     * @param int $owner_id
     * @param int $opponent_id
     * @param array $options
     *
     * @return Message[]
     */
    public static function getHistory($owner_id, $opponent_id, $options = []);

    /**
     * @param int $sender_id
     * @param int $recipient_id
     * @param array $message_array
     * @param array $users_info
     */
    public static function beforeSend($sender_id, $recipient_id, &$message_array, $users_info);

    /**
     * @param int $sender_id
     * @param int $recipient_id
     * @param array $message_array
     * @param array $users_info
     */
    public static function afterSend($sender_id, $recipient_id, $message_array, $users_info);
}
