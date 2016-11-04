<?php
/**
 * Created by PhpStorm.
 * User: alfred
 * Date: 04.11.16
 * Time: 15:58
 */

namespace chat\interfaces;

use React\Socket\Connection;

/**
 * Interface SecurityInterface
 * @package chat\interfaces
 */
interface SecurityInterface
{
    const FRAME_TYPE_TEXT = 'text';
    const FRAME_TYPE_BINARY = 'binary';
    const FRAME_TYPE_CLOSE = 'close';
    const FRAME_TYPE_PING = 'ping';
    const FRAME_TYPE_PONG = 'pong';

    const GUID = '258EAFA5-E914-47DA-95CA-C5AB0DC85B11';

    /**
     * @param string $room
     *
     * @return string
     */
    public static function parseRoomString($room);

    /**
     * @param string $hash
     *
     * @return string
     */
    public static function parseHashString($hash);

    /**
     * @param Connection $connect
     *
     * @return array|bool
     */
    public static function handshake(Connection $connect);

    /**
     * @param $data
     *
     * @return array|bool
     */
    public static function decode($data);

    /**
     * @param        $payload
     * @param string $type
     * @param bool   $masked
     *
     * @return string
     */
    public static function encode($payload, $type = 'text', $masked = false);
}
