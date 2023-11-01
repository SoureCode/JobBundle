<?php

namespace SoureCode\Bundle\Job\Job;

/**
 * @template T
 */
interface JobHandlerInterface
{
    /**
     * @param T $payload
     *
     * @return void|mixed
     */
    public function handle($payload);
}