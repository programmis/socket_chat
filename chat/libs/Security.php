<?php
/**
 * Created by PhpStorm.
 * User: daniil
 * Date: 21.10.16
 * Time: 10:06
 */

namespace chat\libs;

use chat\Chat;
use React\Socket\Connection;

/**
 * Class Security
 *
 * @package chat\libs
 */
class Security
{
    const FRAME_TYPE_TEXT = 'text';
    const FRAME_TYPE_BINARY = 'binary';
    const FRAME_TYPE_CLOSE = 'close';
    const FRAME_TYPE_PING = 'ping';
    const FRAME_TYPE_PONG = 'pong';

    const GUID = '258EAFA5-E914-47DA-95CA-C5AB0DC85B11';

    /**
     * @param Connection $connect
     *
     * @return array|bool
     */
    public static function handshake(Connection $connect)
    {
        sleep(1);
        $info = array();
        $stream = $connect->getBuffer()->stream;

        while ($line = rtrim(fgets($stream))) {
            if (preg_match('/\A(\S+): (.*)\z/', $line, $matches)) {
                if (!isset($matches[1], $matches[2])) {
                    continue;
                }
                $info[$matches[1]] = $matches[2];
            } else {
                preg_match('|GET \/(.*)\/(.*) HTTP.+$|', $line, $matches);

                $room = preg_replace("/[^a-zA-Z0-9]/", "", $matches[1] ?? '');
                $room = $room ? $room : Chat::DEFAULT_ROOM;
                $info['room'] = strtolower($room);

                $hash = preg_replace("/[^a-zA-Z0-9]/", "", $matches[2] ?? '');
                $info['hash'] = trim($hash);
            }
        }
        if (empty($info['Sec-WebSocket-Key'])) {
            return false;
        }

        $SecWebSocketAccept = base64_encode(
            pack(
                'H*',
                sha1($info['Sec-WebSocket-Key'] . self::GUID)
            )
        );

        $upgrade = "HTTP/1.1 101 Web Socket Protocol Handshake\r\n" .
            "Upgrade: websocket\r\n" .
            "Connection: Upgrade\r\n" .
            "Sec-WebSocket-Accept:$SecWebSocketAccept\r\n\r\n";
        $connect->write($upgrade);

        if (!isset($info['room'])) {
            $info['room'] = Chat::DEFAULT_ROOM;
        }

        return $info;
    }

    /**
     * @param        $payload
     * @param string $type
     * @param bool $masked
     *
     * @return string
     */
    public static function encode($payload, $type = 'text', $masked = false)
    {
        $frameHead = array();
        $payloadLength = strlen($payload);

        switch ($type) {
            case self::FRAME_TYPE_TEXT:
                $frameHead[0] = 129; //10000001
                break;

            case self::FRAME_TYPE_CLOSE:
                $frameHead[0] = 136; //10001000
                break;

            case self::FRAME_TYPE_PING:
                $frameHead[0] = 137; //10001001
                break;

            case self::FRAME_TYPE_PONG:
                $frameHead[0] = 138; //10001010
                break;
        }

        // set mask and payload length (using 1, 3 or 9 bytes)
        if ($payloadLength > 65535) {
            $payloadLengthBin = str_split(sprintf('%064b', $payloadLength), 8);
            $frameHead[1] = ($masked === true) ? 255 : 127;
            for ($i = 0; $i < 8; $i++) {
                $frameHead[$i + 2] = bindec($payloadLengthBin[$i]);
            }
            // most significant bit MUST be 0
            if ($frameHead[2] > 127) {
                return array('type' => '', 'payload' => '', 'error' => 'frame too large (1004)');
            }
        } elseif ($payloadLength > 125) {
            $payloadLengthBin = str_split(sprintf('%016b', $payloadLength), 8);
            $frameHead[1] = ($masked === true) ? 254 : 126;
            $frameHead[2] = bindec($payloadLengthBin[0]);
            $frameHead[3] = bindec($payloadLengthBin[1]);
        } else {
            $frameHead[1] = ($masked === true) ? $payloadLength + 128 : $payloadLength;
        }

        // convert frame-head to string:
        foreach (array_keys($frameHead) as $i) {
            $frameHead[$i] = chr($frameHead[$i]);
        }
        $mask = array();

        if ($masked === true) {
            // generate a random mask:
            for ($i = 0; $i < 4; $i++) {
                $mask[$i] = chr(rand(0, 255));
            }

            $frameHead = array_merge($frameHead, $mask);
        }
        $frame = implode('', $frameHead);

        // append payload to frame:
        for ($i = 0; $i < $payloadLength; $i++) {
            $frame .= ($masked === true) ? $payload[$i] ^ $mask[$i % 4] : $payload[$i];
        }

        return $frame;
    }

    /**
     * @param $data
     *
     * @return array|bool
     */
    public static function decode($data)
    {
        $unmaskedPayload = '';
        $decodedData = array();

        // estimate frame type:
        $firstByteBinary = sprintf('%08b', ord($data[0]));
        $secondByteBinary = sprintf('%08b', ord($data[1]));
        $opcode = bindec(substr($firstByteBinary, 4, 4));
        $isMasked = ($secondByteBinary[0] == '1') ? true : false;
        $payloadLength = ord($data[1]) & 127;

        // unmasked frame is received:
        if (!$isMasked) {
            return array('type' => '', 'payload' => '', 'error' => 'protocol error (1002)');
        }

        switch ($opcode) {
            case 1:
                $decodedData['type'] = self::FRAME_TYPE_TEXT;
                break;
            case 2:
                $decodedData['type'] = self::FRAME_TYPE_BINARY;
                break;
            case 8:
                $decodedData['type'] = self::FRAME_TYPE_CLOSE;
                break;
            case 9:
                $decodedData['type'] = self::FRAME_TYPE_PING;
                break;
            case 10:
                $decodedData['type'] = self::FRAME_TYPE_PONG;
                break;
            default:
                return array('type' => '', 'payload' => '', 'error' => 'unknown opcode (1003)');
        }

        if ($payloadLength === 126) {
            $mask = substr($data, 4, 4);
            $payloadOffset = 8;
            $dataLength = bindec(sprintf('%08b', ord($data[2])) . sprintf('%08b', ord($data[3]))) + $payloadOffset;
        } elseif ($payloadLength === 127) {
            $mask = substr($data, 10, 4);
            $payloadOffset = 14;
            $tmp = '';
            for ($i = 0; $i < 8; $i++) {
                $tmp .= sprintf('%08b', ord($data[$i + 2]));
            }
            $dataLength = bindec($tmp) + $payloadOffset;
            unset($tmp);
        } else {
            $mask = substr($data, 2, 4);
            $payloadOffset = 6;
            $dataLength = $payloadLength + $payloadOffset;
        }

        /**
         * We have to check for large frames here. socket_recv cuts at 1024 bytes
         * so if websocket-frame is > 1024 bytes we have to wait until whole
         * data is transferd.
         */
        if (strlen($data) < $dataLength) {
            return false;
        }

        if ($isMasked) {
            for ($i = $payloadOffset; $i < $dataLength; $i++) {
                $j = $i - $payloadOffset;
                if (isset($data[$i])) {
                    $unmaskedPayload .= $data[$i] ^ $mask[$j % 4];
                }
            }
            $decodedData['payload'] = $unmaskedPayload;
        } else {
            $payloadOffset = $payloadOffset - 4;
            $decodedData['payload'] = substr($data, $payloadOffset);
        }

        return $decodedData;
    }
}
