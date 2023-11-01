<?php

namespace SoureCode\Bundle\Job\Model;

use InvalidArgumentException;
use JsonException;
use Stringable;

/**
 * @internal
 */
class EncodedPayload implements Stringable
{
    public function __construct(
        private string $type,
        private string $body,
    )
    {
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): EncodedPayload
    {
        $this->type = $type;
        return $this;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function setBody(string $body): EncodedPayload
    {
        $this->body = $body;
        return $this;
    }

    /**
     * @throws JsonException
     */
    public static function fromString(string $encoded): EncodedPayload
    {
        /**
         * @var EncodedPayload $payload
         */
        $data = json_decode($encoded, true, 512, JSON_THROW_ON_ERROR);

        if (!is_array($data)) {
            throw new InvalidArgumentException('Invalid payload.');
        }

        if (!isset($data['type'])) {
            throw new InvalidArgumentException('Invalid payload. Missing type.');
        }

        if (!isset($data['body'])) {
            throw new InvalidArgumentException('Invalid payload. Missing body.');
        }

        return new EncodedPayload(
            $data['type'],
            $data['body'],
        );
    }

    public function decode(object $body): Payload
    {
        return new Payload(
            $this->type,
            $body,
        );
    }

    /**
     * @throws JsonException
     */
    public function toString(): string
    {
        return json_encode([
            'type' => $this->type,
            'body' => $this->body,
        ], JSON_THROW_ON_ERROR);
    }

    /**
     * @throws JsonException
     */
    public function __toString()
    {
        return $this->toString();
    }
}