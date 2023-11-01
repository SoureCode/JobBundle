<?php

namespace SoureCode\Bundle\Job\Tests\app\src\Job;

readonly class FastJob
{
    public function __construct(
        public string $foo = 'bar',
    )
    {
    }
}