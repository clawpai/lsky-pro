<?php

namespace App\Forms\Components;

use Filament\Forms\Components\Component;

class Code extends Component
{
    protected string $view = 'forms.components.code';

    protected mixed $content = null;

    public static function make(): static
    {
        return app(static::class);
    }

    public function content(mixed $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getContent(): mixed
    {
        return $this->evaluate($this->content);
    }
}
