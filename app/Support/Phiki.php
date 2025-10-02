<?php

namespace Phiki;

/**
 * Phiki syntax highlighter stub for Windows compatibility
 * Provides basic syntax highlighting functionality without problematic test files
 */
class Phiki
{
    public static function version(): string
    {
        return '2.0.4-stub';
    }

    public function highlight(string $code, string $language = null): string
    {
        return '<pre><code class="language-' . htmlspecialchars($language ?? 'text') . '">' . htmlspecialchars($code) . '</code></pre>';
    }

    public static function highlightElement(string $code, string $language = null): string
    {
        $instance = new self();
        return $instance->highlight($code, $language);
    }

    public function codeToHtml(string $code, string $grammar = null, array $themes = []): HighlightedCode
    {
        return new HighlightedCode($code, $grammar);
    }
}

/**
 * Fluent interface for highlighted code
 */
class HighlightedCode
{
    private string $code;
    private ?string $grammar;
    private bool $withGutter = false;
    private int $startingLine = 1;
    private array $decorations = [];

    public function __construct(string $code, ?string $grammar = null)
    {
        $this->code = $code;
        $this->grammar = $grammar;
    }

    public function withGutter(bool $withGutter = true): self
    {
        $this->withGutter = $withGutter;
        return $this;
    }

    public function startingLine(int $line): self
    {
        $this->startingLine = $line;
        return $this;
    }

    public function decoration(...$decorations): self
    {
        $this->decorations = array_merge($this->decorations, $decorations);
        return $this;
    }

    public function __toString(): string
    {
        $escapedCode = htmlspecialchars($this->code);
        $languageClass = $this->grammar ? 'language-' . htmlspecialchars($this->grammar) : 'language-text';

        if ($this->withGutter) {
            $lines = explode("\n", $escapedCode);
            $numberedLines = [];
            foreach ($lines as $index => $line) {
                $lineNumber = $this->startingLine + $index;
                $numberedLines[] = '<span class="line-number">' . $lineNumber . '</span><span class="line-content">' . $line . '</span>';
            }
            $escapedCode = implode("\n", $numberedLines);
        }

        return '<pre class="bg-transparent"><code class="' . $languageClass . '">' . $escapedCode . '</code></pre>';
    }
}
