<?php
/**
 * Created by PhpStorm.
 * User: daniil
 * Date: 26.10.16
 * Time: 14:56
 */

namespace chat\external\base;

/**
 * Class MessageBase
 * @package chat\external\base
 */
abstract class MessageBase
{
    /** @var int $sender_id */
    public $sender_id;
    /** @var int $recipient_id */
    public $recipient_id;
    /** @var string $text */
    public $text;
    public $date;
}
