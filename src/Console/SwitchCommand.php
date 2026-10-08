<?php

namespace jeremykenedy\LaravelLogger\Console;

class SwitchCommand extends ConfigureCommand
{
    protected $signature = 'logger:switch {--css=} {--frontend=blade} {--views=} {--theme=} {--publish-views} {--force}';

    protected $description = 'Switch Laravel Logger frameworks using command options.';
}
