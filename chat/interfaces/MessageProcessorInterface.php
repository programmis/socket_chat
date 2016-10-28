<?php
/**
 * Created by PhpStorm.
 * User: daniil
 * Date: 21.10.16
 * Time: 13:02
 */

namespace chat\interfaces;

/**
 * Interface MessageInterface
 *
 * @package chat\interfaces
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
     * @param UserInterface $sender
     *
     * @return array ['system_type', [system data]]
     */
    public function system($message, $room, UserInterface $sender);

    /**
     * @param array $event
     * @param string $room
     * @param UserInterface $sender
     *
     * @return array ['event_type', [event data]]
     */
    public function event($event, $room, UserInterface $sender);
}
