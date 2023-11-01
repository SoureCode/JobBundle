<?php

namespace SoureCode\Bundle\Job\Tests\app\src\Job;

readonly class WaitJob
{
    public function __construct(
        public int $seconds = 2,
    )
    {

    }
}