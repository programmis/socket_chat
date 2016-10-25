<?php
/**
 * Created by PhpStorm.
 * User: daniil
 * Date: 21.10.16
 * Time: 12:02
 */

namespace php\interfaces;

use php\external\User;

/**
 * Interface UserInterface
 *
 * @package php\interfaces
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

    /**
     * @return string
     */
    public static function getUserClass();
}
