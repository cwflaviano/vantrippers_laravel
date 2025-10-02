<?php

namespace Phiki\Transformers\Decorations;

class PreDecoration
{
    public static function make(): self
    {
        return new self();
    }

    public function class(string ...$classes): self
    {
        return $this;
    }
}
