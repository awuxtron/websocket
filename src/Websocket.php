<?php

namespace Awuxtron\Websocket;

use Awuxtron\Websocket\Enums\Opcode;
use Awuxtron\Websocket\Utils\Message;
use Awuxtron\Websocket\Utils\SocketStream;
use Ratchet\RFC6455\Messaging\Frame;

class Websocket
{
    /**
     * The websocket client options.
     *
     * @var Options
     */
    protected Options $options;

    /**
     * The socket stream instance.
     *
     * @var SocketStream
     */
    protected SocketStream $stream;

    /**
     * Create a new websocket instance.
     *
     * @param Client $client
     */
    public function __construct(protected Client $client)
    {
        $this->stream = $client->getStream();
        $this->options = $client->getOptions();
    }

    /**
     * Close the connection when destruct instance.
     */
    public function __destruct()
    {
        $this->client->disconnect();
    }

    /**
     * Read message from websocket server.
     *
     * @return Message
     */
    public function read(): Message
    {
        $message = new Message($this->stream);

        if ($message->getOpcode() == Opcode::CLOSE) {
            $this->client->disconnect();
        }

        return $message;
    }

    /**
     * Send a message to the websocket server.
     *
     * @param string $message
     * @param int    $opcode
     */
    public function send(string $message, int $opcode = Opcode::TEXT): void
    {
        $length = count($chunks = str_split($message, $this->options->max_fragment_size));

        foreach ($chunks as $i => $payload) {
            $frame = (new Frame($payload, $i == $length - 1, $opcode))->maskPayload();

            $this->stream->write($frame->getContents());

            $opcode = Opcode::CONTINUE;
        }
    }

    /**
     * Convenience method to send binary message.
     *
     * @param string $message
     */
    public function binary(string $message): void
    {
        $this->send($message, Opcode::BINARY);
    }

    /**
     * Convenience method to send ping.
     *
     * @param string $message
     */
    public function ping(string $message = ''): void
    {
        $this->send($message, Opcode::PING);
    }

    /**
     * Convenience method to send pong.
     *
     * @param string $message
     */
    public function pong(string $message = ''): void
    {
        $this->send($message, Opcode::PONG);
    }

    /**
     * Send a close message to the websocket server.
     *
     * @param int    $status
     * @param string $reason
     */
    public function close(int $status, string $reason = ''): void
    {
        $this->send(pack('n', $status) . $reason, Opcode::CLOSE);
    }
}
