<?php
/**
 * Created by PhpStorm.
 * User: daniil
 * Date: 25.10.16
 * Time: 16:48
 */

namespace php\external;

use php\external\base\MessageBase;
use php\interfaces\MessageInterface;

/**
 * Class Message
 * @package php\external
 */
class Message extends MessageBase implements MessageInterface
{
    private static $list;

    /** @inheritdoc */
    public function save()
    {
        self::$list[] = $this;

        return true;
    }

    /** @inheritdoc */
    public static function getMessageHistoryByPeriod($sender_id, $recipient_id, $period)
    {
        return self::$list;
    }
}
