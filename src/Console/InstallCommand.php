<?php

namespace jeremykenedy\LaravelLogger\Console;

class InstallCommand extends ConfigureCommand
{
    protected $signature = 'logger:install {--css=} {--frontend=blade} {--views=} {--theme=} {--publish-views} {--force}';

    protected $description = 'Install Laravel Logger without replacing existing configuration or views.';
}
