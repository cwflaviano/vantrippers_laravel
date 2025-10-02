<?php

namespace Phiki\Transformers\Decorations;

class GutterDecoration
{
    public static function make(): self
    {
        return new self();
    }

    public function class(string $class): self
    {
        return $this;
    }
}
