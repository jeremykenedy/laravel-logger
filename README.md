# Laravel Logger

[![Latest Stable Version](https://poser.pugx.org/jeremykenedy/laravel-logger/v/stable)](https://packagist.org/packages/jeremykenedy/laravel-logger)
[![Total Downloads](https://poser.pugx.org/jeremykenedy/laravel-logger/downloads)](https://packagist.org/packages/jeremykenedy/laravel-logger)
[![Latest Unstable Version](https://poser.pugx.org/jeremykenedy/laravel-logger/v/unstable)](https://packagist.org/packages/jeremykenedy/laravel-logger)
[![License](https://poser.pugx.org/jeremykenedy/laravel-logger/license)](https://packagist.org/packages/jeremykenedy/laravel-logger)

#### READY FOR USE!
- [About](#about)
- [Features](#features)
- [Requirements](#requirements)
- [Installation Instructions](#installation-instructions)

### About
Laravel logger is an activity event logger for your laravel application. It comes out the box with ready to use with dashboard to view your activity. Laravel logger can be added as a middleware or called through a trait. This package is easily configurable and customizable.

### Features

| Laravel Logger Features  |
| :------------ |
||

## Requirements
* [Laravel 5.1, 5.2, 5.3, 5.4, or 5.5+](https://laravel.com/docs/installation)


### Installation Instructions

1. From your projects root folder in terminal run:

```bash
    composer require jeremykenedy/laravel-logger
```

2. Register the package

* Laravel 5.5 and up
Uses package auto discovery feature, no need to edit the `config/app.php` file.

* Laravel 5.4 and below
Register the package with laravel in `config/app.php` under `providers` with the following:

```php
    'providers' => [
        jeremykenedy\LaravelLogger\LaravelLoggerServiceProvider::class,
    ];
```

3. Publish the packages views, config file, assets, and language files by running the following from your projects root folder:

```bash
    php artisan vendor:publish --tag=laravellogger
```



















Laravel logger can work out the box with or without the following roles packages:
* [jeremykenedy/laravel-roles](https://github.com/jeremykenedy/laravel-roles)
* [spatie/laravel-permission](https://github.com/spatie/laravel-permission)
* [Zizaco/entrust](https://github.com/Zizaco/entrust)
* [romanbican/roles](https://github.com/romanbican/roles)
* [ultraware/roles](https://github.com/ultraware/roles)

