<?php
/**
 * Created by PhpStorm.
 * User: daniil
 * Date: 26.10.16
 * Time: 14:56
 */

namespace php\external\base;

use php\external\User;

/**
 * Class MessageBase
 * @package php\external\base
 */
abstract class MessageBase
{
    /** @var User $sender */
    public $sender;
    /** @var User $recipient */
    public $recipient;
    /** @var string $text */
    public $text;
    public $date;
}
