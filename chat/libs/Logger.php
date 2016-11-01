<?php
/**
 * Created by PhpStorm.
 * User: daniil
 * Date: 21.10.16
 * Time: 15:17
 */

namespace chat\libs;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
use Psr\Log\LogLevel;

/**
 * Class Logger
 *
 * @package chat
 */
class Logger implements LoggerInterface
{
    use LoggerTrait;

    /** @inheritdoc */
    public function log($level, $message, array $context = array())
    {
        switch ($level) {
            case LogLevel::DEBUG:
                $color = "\033[1;33m"; //YELLOW
                break;
            case LogLevel::INFO:
                $color = "\033[1;32m"; //GREEN
                break;
            case LogLevel::NOTICE:
                $color = "\033[1;36m"; //CYAN
                break;
            case LogLevel::EMERGENCY:
                $color = "\033[1;34m"; //BLUE
                break;
            case LogLevel::WARNING:
                $color = "\033[1;35m"; //MAGENTA
                break;
            case LogLevel::ALERT:
            case LogLevel::ERROR:
            case LogLevel::CRITICAL:
                $color = "\033[1;31m"; //RED
                break;
            default:
                $color = "\033[0;39m"; //NORMAL
                break;
        }
        echo date('Y/m/d H:i:s') . " " . $color . $level . "\033[0;39m) " . $message . "\n";
    }
}
