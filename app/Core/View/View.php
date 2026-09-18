<?php

declare(strict_types=1);

namespace App\Core\View;

use RuntimeException;
use Throwable;

final class View
{
    private ?string $layout = null;

    public function __construct(
        private readonly string $template,
        private readonly array $data = [],
    ) {
    }

    public static function make(string $template, array $data = []): self
    {
        return new self($template, $data);
    }

    /**
     * @throws Throwable
     */
    public function render(): string
    {
        $content = $this->capture();

        if ($this->layout === null) {
            return $content;
        }

        return self::make($this->layout, [...$this->data, 'content' => $content])->render();
    }

    protected function extend(string $layout): void
    {
        $this->layout = $layout;
    }

    /**
     * @throws Throwable
     */
    protected function include(string $template, array $data = []): void
    {
        echo self::make($template, [...$this->data, ...$data])->render();
    }

    private function capture(): string
    {
        $__path = $this->resolvePath();
        $__data = $this->data;

        extract($__data, EXTR_SKIP);

        ob_start();

        try {
            require $__path;
        } catch (Throwable $e) {
            ob_end_clean();

            throw $e;
        }

        return (string) ob_get_clean();
    }

    private function resolvePath(): string
    {
        $base = realpath(BASE_PATH.'/resources/views');
        $path = realpath($base.'/'.str_replace('.', '/', $this->template).'.php');

        if ($path === false || !str_starts_with($path, $base.DIRECTORY_SEPARATOR)) {
            throw new RuntimeException(sprintf('View [%s] not found.', $this->template));
        }

        return $path;
    }
}
