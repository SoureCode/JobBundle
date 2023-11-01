<?php

namespace SoureCode\Bundle\Job\Job;

use Closure;

readonly class HandlerDescriptor
{
    public function __construct(
        private JobHandlerInterface $handler,
        private string              $payloadClassName,
    )
    {
    }

    public function getHandler(): JobHandlerInterface
    {
        return $this->handler;
    }

    public function getPayloadClassName(): string
    {
        return $this->payloadClassName;
    }
}