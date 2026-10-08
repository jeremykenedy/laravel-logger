<?php

namespace jeremykenedy\LaravelLogger\Console;

use Illuminate\Console\Command;
use jeremykenedy\LaravelLogger\Support\Dashboard;
use jeremykenedy\LaravelLogger\Support\EnvironmentSettings;
use RuntimeException;

abstract class ConfigureCommand extends Command
{
    public function handle(EnvironmentSettings $settings): int
    {
        if (! $this->configurationReady() || ! $this->installationAccepted()) {
            return 1;
        }
        $choices = $this->selection($this->getName() === 'logger:switch');
        if (! $this->validSelection($choices)) {
            return 1;
        }
        if (! $this->writeSettings($settings, $choices)) {
            return 1;
        }
        $this->publishFiles();
        $this->info('Logger settings saved. Run php artisan migrate if needed, then rebuild your application assets.');

        return 0;
    }

    private function selection(bool $switch): array
    {
        $choices = [
            'css' => $this->option('css') ?? Dashboard::css(),
            'views' => $this->option('views') ?? config('LaravelLogger.viewStyle', 'legacy'),
            'theme' => $this->option('theme') ?? config('LaravelLogger.theme', 'system'),
            'frontend' => $this->option('frontend') ?? 'blade',
        ];
        if (! $switch && $this->input->isInteractive()) {
            $choices = $this->promptForChoices($choices);
        }
        if ($this->option('views') === null && in_array($choices['css'], ['bootstrap5', 'tailwind'], true)) {
            $choices['views'] = 'modern';
        }

        return $choices;
    }

    private function configurationReady(): bool
    {
        if ($this->laravel->configurationIsCached()) {
            $this->error('Run php artisan config:clear before changing Logger settings.');

            return false;
        }

        return true;
    }

    private function installationAccepted(): bool
    {
        $existing = $this->getName() === 'logger:install' && is_file(config_path('laravel-logger.php')) && ! $this->option('force');

        return ! $existing || $this->confirmExistingInstallation();
    }

    private function confirmExistingInstallation(): bool
    {
        if ($this->input->isInteractive() && $this->confirm('Logger is already installed. Continue without overwriting configuration or views?', false)) {
            return true;
        }
        $this->warn('Logger is already installed. Use logger:update or pass --force.');

        return false;
    }

    private function writeSettings(EnvironmentSettings $settings, array $choices): bool
    {
        try {
            $settings->write($this->laravel->environmentFilePath(), [
                'LARAVEL_LOGGER_CSS_FRAMEWORK' => $choices['css'],
                'LARAVEL_LOGGER_VIEWS' => $choices['views'],
                'LARAVEL_LOGGER_THEME' => $choices['theme'],
            ]);

            return true;
        } catch (RuntimeException $exception) {
            $this->error($exception->getMessage());

            return false;
        }
    }

    private function publishFiles(): void
    {
        if ($this->getName() === 'logger:install') {
            $this->call('vendor:publish', ['--tag' => 'LaravelLogger-config']);
        }
        $this->call('vendor:publish', ['--tag' => 'LaravelLogger-assets', '--force' => true]);
        if ($this->option('publish-views')) {
            $this->call('vendor:publish', ['--tag' => 'LaravelLogger-views']);
        }
        $this->call('view:clear');
    }

    private function promptForChoices(array $choices): array
    {
        foreach (['css' => ['bootstrap3', 'bootstrap4', 'bootstrap5', 'tailwind'], 'views' => ['legacy', 'modern'], 'theme' => ['system', 'light', 'dark']] as $key => $values) {
            if ($this->option($key) === null) {
                $choices[$key] = $this->choice(ucfirst($key), $values, $choices[$key]);
            }
        }

        return $choices;
    }

    private function validSelection(array $choices): bool
    {
        foreach (['css' => ['bootstrap3', 'bootstrap4', 'bootstrap5', 'tailwind'], 'views' => ['legacy', 'modern'], 'theme' => ['system', 'light', 'dark'], 'frontend' => ['blade']] as $key => $values) {
            if (! in_array($choices[$key], $values, true)) {
                $this->error('Unsupported '.$key.'. Choose '.implode(', ', $values).'.');

                return false;
            }
        }
        if ($choices['views'] === 'legacy' && ! in_array($choices['css'], ['bootstrap3', 'bootstrap4'], true)) {
            $this->error('Legacy views require Bootstrap 3 or Bootstrap 4.');

            return false;
        }

        return true;
    }
}
