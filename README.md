# Laravel-FluentLogger
fluent logger for laravel
(with Monolog handler for Fluentd )

[fluentd](http://www.fluentd.org/)

[![Build Status](http://img.shields.io/travis/ytake/Laravel-FluentLogger/master.svg?style=flat-square)](https://travis-ci.org/ytake/Laravel-FluentLogger)
[![Coverage Status](http://img.shields.io/coveralls/ytake/Laravel-FluentLogger/master.svg?style=flat-square)](https://coveralls.io/r/ytake/Laravel-FluentLogger?branch=master)
[![Dependency Status](https://www.versioneye.com/user/projects/563e07fa4d415e001b0000ac/badge.svg?style=flat)](https://www.versioneye.com/user/projects/563e07fa4d415e001b0000ac)
[![Scrutinizer Code Quality](http://img.shields.io/scrutinizer/g/ytake/Laravel-FluentLogger.svg?style=flat)](https://scrutinizer-ci.com/g/ytake/Laravel-FluentLogger/?branch=master)

[![License](http://img.shields.io/packagist/l/ytake/laravel-fluent-logger.svg?style=flat-square)](https://packagist.org/packages/ytake/laravel-fluent-logger)
[![Latest Version](http://img.shields.io/packagist/v/ytake/laravel-fluent-logger.svg?style=flat-square)](https://packagist.org/packages/ytake/laravel-fluent-logger)
[![Total Downloads](http://img.shields.io/packagist/dt/ytake/laravel-fluent-logger.svg?style=flat-square)](https://packagist.org/packages/ytake/laravel-fluent-logger)
[![StyleCI](https://styleci.io/repos/45625024/shield)](https://styleci.io/repos/45625024)

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/2ac5d569-39c0-4a80-900d-03760287acba/mini.png)](https://insight.sensiolabs.com/projects/2ac5d569-39c0-4a80-900d-03760287acba)

## usage

### Installation For Laravel
Require this package with Composer

```bash
$ composer require ytake/laravel-fluent-logger
```

or composer.json

```json
"require": {
  "ytake/laravel-fluent-logger": "~0.0"
},
```

add Laravel.Smarty Service Providers

your config/app.php
```php
'providers' => [
    // add smarty extension
    \Ytake\Ytake\LaravelFluent\LogServiceProvider::class,
]
```

### publish configure

* basic

```bash
$ php artisan vendor:publish
```

* use tag option

```bash
$ php artisan vendor:publish --tag=log
```

* use provider

```bash
$ php artisan vendor:publish --provider="Ytake\LaravelFluent\LogServiceProvider"
```

### Basic Push Handler

your Application service provider
```php

public function bbot()
{
    $this->app['fluent.handler']->pushHandler();
}
```

### All logs to fluentd

in Application Http\Kernel class

override bootstrappers property

```php
    /**
     * The bootstrap classes for the application.
     *
     * @var array
     */
    protected $bootstrappers = [
        'Illuminate\Foundation\Bootstrap\DetectEnvironment',
        'Illuminate\Foundation\Bootstrap\LoadConfiguration',
        \Ytake\LaravelFluent\ConfigureLogging::class,
        'Illuminate\Foundation\Bootstrap\HandleExceptions',
        'Illuminate\Foundation\Bootstrap\RegisterFacades',
        'Illuminate\Foundation\Bootstrap\RegisterProviders',
        'Illuminate\Foundation\Bootstrap\BootProviders',
    ];
```

