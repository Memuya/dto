<?php

declare(strict_types=1);

namespace Memuya\Dto\Modifiers;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Label
{
    public function __construct(private string $label)
    {
    }

    public function getLabel(): string
    {
        return $this->label;
    }
}
