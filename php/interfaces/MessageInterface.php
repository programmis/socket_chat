<?php
/**
 * Created by PhpStorm.
 * User: daniil
 * Date: 25.10.16
 * Time: 16:47
 */

namespace php\interfaces;

use php\external\Message;

/**
 * Interface MessageInterface
 * @package php\interfaces
 */
interface MessageInterface
{
    const TYPE_TEXT = 'text';
    const TYPE_EVENT = 'event';
    const TYPE_SYSTEM = 'system';

    const CONTAINER = 'message';

    /**
     * @return bool
     */
    public function save();

    /**
     * @param int $sender_id
     * @param int $recipient_id
     * @param int $period
     *
     * @return Message[]
     */
    public static function getMessageHistoryByPeriod($sender_id, $recipient_id, $period);
}
