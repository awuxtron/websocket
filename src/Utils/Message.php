<?php

namespace Awuxtron\Websocket\Utils;

use Awuxtron\Websocket\Enums\CloseStatus;
use Awuxtron\Websocket\Enums\Opcode;
use Awuxtron\Websocket\Exceptions\BadMessageException;
use Psr\Http\Message\StreamInterface;

class Message
{
    /**
     * The message payload.
     *
     * @var string
     */
    protected string $payload;

    /**
     * The message header.
     *
     * @var string[]
     */
    protected array $header;

    /**
     * Determines if the message is masked.
     *
     * @var bool
     */
    protected bool $isMasked;

    /**
     * Determines if the message is final message.
     *
     * @var bool
     */
    protected bool $isFinal;

    /**
     * The masking key.
     *
     * @var string
     */
    protected string $maskingKey;

    /**
     * The length of the payload.
     *
     * @var int
     */
    protected int $length;

    /**
     * The message opcode.
     *
     * @var Opcode
     */
    protected Opcode $opcode;

    /**
     * The close status of message.
     *
     * @var CloseStatus
     */
    protected CloseStatus $closeStatus;

    /**
     * Read and decode the message from the stream.
     *
     * @param StreamInterface $stream
     */
    final public function __construct(protected StreamInterface $stream)
    {
        $header = $stream->read(2);

        $this->header = str_split($header);
        $this->maskingKey = $this->isMasked() ? $stream->read(4) : '';

        $this->readPayload();
    }

    /**
     * Create a new message instance from a stream.
     *
     * @param StreamInterface $stream
     *
     * @return static
     */
    public static function from(StreamInterface $stream): static
    {
        return new static($stream);
    }

    /**
     * Get the message payload.
     *
     * @return string
     */
    public function getPayload(): string
    {
        return $this->payload;
    }

    /**
     * Get the length of the payload.
     *
     * @return int
     */
    public function getPayloadLength(): int
    {
        if (empty($this->header)) {
            return 0;
        }

        if (!isset($this->length)) {
            $this->length = ord($this->header[1]) & 127;

            if ($this->length > 125) {
                $this->length = (int) bindec($this->sprintb($this->stream->read($this->length == 126 ? 2 : 8)));
            }
        }

        return $this->length;
    }

    /**
     * Get the message opcode.
     *
     * @return Opcode
     */
    public function getOpcode(): Opcode
    {
        if (empty($this->header)) {
            throw new BadMessageException('Unable to get the opcode of empty message.');
        }

        return $this->opcode ?? Opcode::from(ord($this->header[0]) & 31);
    }

    /**
     * Get the close status of message.
     *
     * @return null|CloseStatus
     */
    public function getCloseStatus(): ?CloseStatus
    {
        return $this->closeStatus ?? null;
    }

    /**
     * Checks if the message is final message.
     *
     * @return bool
     */
    public function isFinal(): bool
    {
        if (empty($this->header)) {
            return true;
        }

        return $this->isFinal ?? $this->isFinal = (bool) (ord($this->header[0]) & 1 << 7);
    }

    /**
     * Checks if the message is masked.
     *
     * @return bool
     */
    public function isMasked(): bool
    {
        if (empty($this->header)) {
            return false;
        }

        return $this->isMasked ?? $this->isMasked = (bool) (ord($this->header[1]) >> 7);
    }

    /**
     * Read the payload from message.
     */
    protected function readPayload(): void
    {
        $this->payload = '';

        if (($length = $this->getPayloadLength()) <= 0) {
            return;
        }

        while (($len = strlen($this->payload)) < $length) {
            $this->payload .= $receive = $this->stream->read(min($length - $len, 8192 * 1024));

            if ($receive == '') {
                throw new BadMessageException(
                    sprintf(
                        'The payload expects %s characters but server returns only %s.',
                        $length,
                        strlen($this->payload)
                    )
                );
            }
        }

        $this->unmask();

        if ($this->getOpcode()->value == Opcode::CLOSE->value) {
            $status = array_map('ord', str_split(substr($this->payload, 0, 2)));

            $this->closeStatus = CloseStatus::from((int) bindec(sprintf('%08b%08b', ...$status)));
            $this->payload = substr($this->payload, 2);
        }
    }

    /**
     * Unmask the message payload if masked.
     *
     * @return static
     */
    protected function unmask(): static
    {
        if (!$this->isMasked()) {
            return $this;
        }

        $result = '';

        for ($i = 0; $i < $this->getPayloadLength(); ++$i) {
            $result .= $this->payload[$i] ^ $this->maskingKey[$i % 4];
        }

        $this->payload = $result;
        $this->isMasked = false;

        return $this;
    }

    /**
     * Helper to convert a binary to a string of '0' and '1'.
     *
     * @param string $string
     *
     * @return string
     */
    protected function sprintb(string $string): string
    {
        $return = '';
        $strLen = strlen($string);

        for ($i = 0; $i < $strLen; ++$i) {
            $return .= sprintf('%08b', ord($string[$i]));
        }

        return $return;
    }
}
