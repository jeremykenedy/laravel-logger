<?php

namespace jeremykenedy\LaravelLogger\Console;

class UpdateCommand extends ConfigureCommand
{
    protected $signature = 'logger:update {--css=} {--frontend=blade} {--views=} {--theme=} {--publish-views} {--force}';

    protected $description = 'Update Laravel Logger assets and framework settings.';
}
