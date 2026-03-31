<?php

namespace App\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS)]
class TranslationEntity
{
    public function __construct(
        public readonly string $class
    ) {}
}