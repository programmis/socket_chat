<?php
/**
 * Created by PhpStorm.
 * User: daniil
 * Date: 21.10.16
 * Time: 12:02
 */

namespace chat\interfaces;

use chat\external\User;

/**
 * Interface UserInterface
 *
 * @package chat\interfaces
 */
interface UserProcessorInterface
{
    const STRUCTURE_CONNECTION = 'connection';
    const STRUCTURE_CLASS = 'class';

    /**
     * @param array $connection_info
     *
     * @return User
     */
    public function createUser(array $connection_info);
}
