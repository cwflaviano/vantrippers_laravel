<?php

namespace Phiki\Transformers\Decorations;

class LineDecoration
{
    public static function forLine(int $line): self
    {
        return new self();
    }

    public function class(string $class): self
    {
        return $this;
    }
}
