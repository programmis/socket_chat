<?php
/**
 * Created by PhpStorm.
 * User: daniil
 * Date: 25.10.16
 * Time: 16:48
 */

namespace chat\external;

use chat\external\base\MessageBase;
use chat\interfaces\MessageInterface;

/**
 * Class Message
 * @package chat\external
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

    /** @inheritdoc */
    public static function addMessage($sender_id, $recipient_id, $text, $params)
    {
        $message = new Message();
        $message->text = $text;
        $message->sender_id = $sender_id;
        $message->recipient_id = $recipient_id;
        $message->date = date('Y/m/d H:i:s');

        return $message->save();
    }
}
