<?php
/**
 * Created by PhpStorm.
 * User: daniil
 * Date: 08.12.16
 * Time: 16:05
 */

namespace chat\libs;

use Psr\Log\LogLevel;
use React\EventLoop\LoopInterface;
use React\Socket\Connection;
use React\Socket\ConnectionException;

/**
 * Class Server
 *
 * @package chat\libs
 */
class Server extends \React\Socket\Server
{
    private $loop;

    /**
     * Server constructor.
     *
     * @param LoopInterface $loop
     */
    public function __construct(LoopInterface $loop)
    {
        $this->loop = $loop;
        parent::__construct($loop);
    }

    /** @inheritdoc */
    public function listen($port, $host = '127.0.0.1', $wss = [], \chat\Server $server = null)
    {
        if (strpos($host, ':') !== false) {
            // enclose IPv6 addresses in square brackets before appending port
            $host = '[' . $host . ']';
        }

        $context = null;
        if ($wss) {
            $context = stream_context_create();
            if (isset($wss['local_cert'])) {
                if (!is_file($wss['local_cert'])) {
                    $server::log('local certificate ' . $wss['local_cert'] . ' is not found!', LogLevel::ERROR);
                } else {
                    stream_context_set_option($context, 'ssl', 'local_cert', $wss['local_cert']);
                    $server::log('connect local certificate ' . $wss['local_cert']);
                }
            }
            if (isset($wss['local_pk'])) {
                if (!is_file($wss['local_pk'])) {
                    $server::log('primary key ' . $wss['local_pk'] . ' is not found!', LogLevel::ERROR);
                } else {
                    stream_context_set_option($context, 'ssl', 'local_pk', $wss['local_pk']);
                    $server::log('connect local primary key ' . $wss['local_pk']);
                }
            }
            stream_context_set_option($context, 'ssl', 'allow_self_signed', true);
            stream_context_set_option($context, 'ssl', 'verify_peer', false);
            $this->master = @stream_socket_server(
                "ssl://$host:$port",
                $errno,
                $errstr,
                STREAM_SERVER_BIND | STREAM_SERVER_LISTEN,
                $context
            );
        } else {
            $this->master = @stream_socket_server("tcp://$host:$port", $errno, $errstr);
        }
        if (false === $this->master) {
            $message = "Could not bind to tcp://$host:$port: $errstr";
            throw new ConnectionException($message, $errno);
        }
        stream_set_blocking($this->master, 0);

        $that = $this;

        $this->loop->addReadStream($this->master, function ($master) use ($that) {
            $newSocket = @stream_socket_accept($master);
            if (false === $newSocket) {
                $that->emit('error', array(new \RuntimeException('Error accepting new connection')));

                return;
            }
            $that->handleConnection($newSocket);
        });
    }

    /** @inheritdoc */
    public function shutdown()
    {
        $this->loop->removeStream($this->master);
        fclose($this->master);
        $this->removeAllListeners();
    }

    /** @inheritdoc */
    public function createConnection($socket)
    {
        return new Connection($socket, $this->loop);
    }
}
