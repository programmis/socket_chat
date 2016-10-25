<?php
/**
 * Created by PhpStorm.
 * User: daniil
 * Date: 25.10.16
 * Time: 20:21
 */

namespace php\interfaces;

/**
 * Interface EventInterface
 * @package php\interfaces
 */
interface EventInterface
{
    const TYPING = 'typing';

    /**
     * @param int $user_id
     * @param string $event
     * @param array $data
     *
     * @return array
     */
    public static function prepareToSend($event, int $user_id, $data);
}
