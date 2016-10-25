<?php
/**
 * Created by PhpStorm.
 * User: daniil
 * Date: 21.10.16
 * Time: 13:02
 */

namespace php\interfaces;

/**
 * Interface MessageInterface
 *
 * @package php\interfaces
 */

interface MessageProcessorInterface
{
    /**
     * @param array $message
     *
     * @return string $message
     */
    public function text($message);

    /**
     * @param array $message
     * @param string $room
     * @param int $from_user_id
     *
     * @return array ['system_type', [system data]]
     */
    public function system($message, $room, $from_user_id);

    /**
     * @param array $event
     * @param string $room
     * @param int $from_user_id
     *
     * @return array ['event_type', [event data]]
     */
    public function event($event, $room, $from_user_id);
}
