<?php

namespace SoureCode\Bundle\Job\Model;

/**
 * @internal
 */
class Payload
{
    public function __construct(
        private string $type,
        private object $body,
    )
    {
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): Payload
    {
        $this->type = $type;
        return $this;
    }

    public function getBody(): object
    {
        return $this->body;
    }

    public function setBody(object $body): Payload
    {
        $this->body = $body;
        return $this;
    }
}