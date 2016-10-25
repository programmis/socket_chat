<?php
/**
 * Created by PhpStorm.
 * User: daniil
 * Date: 25.10.16
 * Time: 21:15
 */

namespace php\interfaces;

/**
 * Interface TextInterface
 * @package php\interfaces
 */
interface TextInterface
{
    /**
     * @param int $user_id
     * @param string $message
     *
     * @return array
     */
    public static function prepareToSend(int $user_id, $message);
}
