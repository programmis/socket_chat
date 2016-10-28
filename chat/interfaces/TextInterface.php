<?php
/**
 * Created by PhpStorm.
 * User: daniil
 * Date: 25.10.16
 * Time: 21:15
 */

namespace chat\interfaces;

/**
 * Interface TextInterface
 * @package chat\interfaces
 */
interface TextInterface
{
    /**
     * @param UserInterface $sender
     * @param string $message
     *
     * @return array
     */
    public static function prepareToSend(UserInterface $sender, $message);
}
