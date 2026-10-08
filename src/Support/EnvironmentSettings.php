<?php

namespace jeremykenedy\LaravelLogger\Support;

use Illuminate\Filesystem\Filesystem;
use RuntimeException;

class EnvironmentSettings
{
    private $files;

    public function __construct(Filesystem $files)
    {
        $this->files = $files;
    }

    public function write(string $path, array $settings): void
    {
        if (! $this->files->exists($path) || ! $this->files->isWritable($path)) {
            throw new RuntimeException('The environment file must exist and be writable: '.$path);
        }

        $contents = $this->files->get($path);
        $newline = strpos($contents, "\r\n") !== false ? "\r\n" : "\n";
        foreach ($settings as $key => $value) {
            $contents = $this->assignment($contents, $key, $value, $newline);
        }

        if ($this->files->put($path, $contents, true) === false) {
            throw new RuntimeException('Unable to save the environment file.');
        }
    }

    private function assignment(string $contents, string $key, $value, string $newline): string
    {
        $assignment = $key.'='.$value;
        $pattern = '/^[\t ]*(?:export[\t ]+)?'.preg_quote($key, '/').'[\t ]*=.*$/m';
        if (! preg_match($pattern, $contents)) {
            return rtrim($contents, "\r\n").$newline.$assignment.$newline;
        }

        return preg_replace_callback($pattern, function () use ($assignment, $newline) {
            return $assignment.($newline === "\r\n" ? "\r" : '');
        }, $contents);
    }
}
