<?php
/**
 * Created by PhpStorm.
 * User: daniil
 * Date: 21.10.16
 * Time: 15:17
 */

namespace php\libs;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;

/**
 * Class Logger
 *
 * @package php
 */
class Logger implements LoggerInterface
{
    use LoggerTrait;

    /** @inheritdoc */
    public function log($level, $message, array $context = array())
    {
        echo date('Y/m/d H:i:s') . ") " . $message . "\n";
    }
}
