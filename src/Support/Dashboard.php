<?php

namespace jeremykenedy\LaravelLogger\Support;

class Dashboard
{
    public static function css(): string
    {
        $css = config('LaravelLogger.cssFramework');

        return in_array($css, ['bootstrap3', 'bootstrap4', 'bootstrap5', 'tailwind'], true)
            ? $css : (config('LaravelLogger.bootstapVersion') == '3' ? 'bootstrap3' : 'bootstrap4');
    }

    public static function modern(): bool
    {
        return config('LaravelLogger.viewStyle', 'legacy') === 'modern'
            || in_array(self::css(), ['bootstrap5', 'tailwind'], true);
    }

    public static function view(string $name): string
    {
        return 'LaravelLogger::'.(self::modern() ? 'modern.' : 'logger.').$name;
    }

    public static function classes(): array
    {
        if (self::css() === 'tailwind') {
            return [
                'container' => 'mx-auto max-w-7xl px-4 py-8',
                'card' => 'rounded-lg border',
                'input' => 'block w-full rounded-md border px-3 py-2',
                'button' => 'inline-flex items-center rounded-md border border-slate-300 px-3 py-2 text-sm font-medium',
                'primary' => 'inline-flex items-center rounded-md bg-blue-700 px-3 py-2 text-sm font-medium text-white',
                'table' => 'w-full text-left text-sm',
            ];
        }

        return [
            'container' => 'container-fluid py-4', 'card' => 'card mb-4', 'input' => 'form-control',
            'button' => 'btn btn-outline-secondary btn-sm', 'primary' => 'btn btn-primary btn-sm',
            'table' => 'table table-hover mb-0',
        ];
    }
}
