<?php
/**
 * Created by PhpStorm.
 * User: daniil
 * Date: 21.10.16
 * Time: 13:02
 */

namespace php\interfaces;

use php\external\User;

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
     * @param User $sender
     *
     * @return array ['system_type', [system data]]
     */
    public function system($message, $room, User $sender);

    /**
     * @param array $event
     * @param string $room
     * @param User $sender
     *
     * @return array ['event_type', [event data]]
     */
    public function event($event, $room, User $sender);
}
