<?php
/**
 * Created by PhpStorm.
 * User: daniil
 * Date: 25.10.16
 * Time: 15:55
 */

namespace chat\interfaces;

use React\Socket\Connection;

/**
 * Interface ChatInterface
 * @package chat\interfaces
 */
interface ChatInterface
{
    /**
     * Default chat room name
     */
    const DEFAULT_ROOM = 'default';

    const DATA_TYPE_TEXT = 'text';
    const DATA_TYPE_CLOSE = 'close';

    /**
     * @param Connection $conn
     * @param array $connection_info
     *
     * @return UserInterface
     */
    public function createUser(Connection $conn, array $connection_info);

    /**
     * @param array $connection_info
     */
    public function onCloseConnection(array $connection_info);

    /**
     * @param array $data
     * @param array $connection_info
     */
    public function dataProcessing($data, array $connection_info);

    /**
     * @param string $room
     * @param int    $user_id
     *
     * @return Connection
     */
    public function getUserConnection($room, $user_id);

    /**
     * @param UserInterface $sender
     * @param UserInterface $recipient
     * @param string $room
     * @param string $message
     */
    public function sendMessageText($sender, $recipient, $room, $message);
}
