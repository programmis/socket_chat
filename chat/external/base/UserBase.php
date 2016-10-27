<?php
/**
 * Created by PhpStorm.
 * User: daniil
 * Date: 25.10.16
 * Time: 16:15
 */

namespace chat\external\base;

/**
 * Class UserBase
 * @package chat\external\base
 */
abstract class UserBase
{
    /** @var  int $id */
    public $id;
    /** @var  string $avatar http://url/to/avatar/image.png */
    public $avatar;
    /** @var string $name */
    public $name;
    /** @var string $last_name */
    public $last_name;
    /** @var bool $is_online */
    protected $is_online;
}
