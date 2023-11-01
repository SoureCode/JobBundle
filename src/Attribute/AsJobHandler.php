<?php

namespace SoureCode\Bundle\Job\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS)]
class AsJobHandler
{
    public function __construct(
        /**
         * @var class-string
         */
        public string $handle,
    )
    {
    }
}