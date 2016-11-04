<?php
/**
 * Created by PhpStorm.
 * User: daniil
 * Date: 25.10.16
 * Time: 20:21
 */

namespace chat\interfaces;

/**
 * Interface EventInterface
 * @package chat\interfaces
 */
interface EventInterface
{
    const CONTAINER = 'data';

    const TYPING = 'typing';
    const CHANGE_RECIPIENT = 'change_recipient';

    /**
     * @param string $event
     * @param UserInterface $sender
     * @param array $data
     *
     * @return array
     */
    public static function prepareToSend($event, UserInterface $sender, $data);
}
