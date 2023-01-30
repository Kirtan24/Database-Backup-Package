<!-- # Database Backup Package

## This will create your database backup when you run the command

## Please EnterThis line to add publish config file on your project

## php artisan vendor:publish --force --provider=Kirtan\Backup\ContectServiceProvider -->
Laravel-Database Backup / Backup in FTP
===========

A simple Laravel 5/6/7/8/9 ftp service provider.

[![Latest Stable Version](https://poser.pugx.org/anchu/ftp/v/stable)](https://packagist.org/packages/kirtan/backup)
[![Total Downloads](https://poser.pugx.org/anchu/ftp/downloads)](https://packagist.org/packages/kirtan/backup)
[![License](https://poser.pugx.org/anchu/ftp/license)](https://packagist.org/packages/kirtan/backup)

Installation
------------

> If you're using Laravel 5.5+ skip the next step, as Laravel auto discover packages.
Add the service provider in `config/app.php`:

    'Kirtan\Backup\BackupServiceProvider',

Configuration
------------
Run `php artisan vendor:publish --force --provider=Kirtan\Backup\BackupServiceProvider` and modify the config file(`config/ftp.php`) with your ftp connections.

You can add dynamic FTP connections with following syntax

```php
  'ftp' => [
        'host'   => '',
        'port'  => 21,
        'username' => '',
        'password'   => '',
        'root' => '',
    ],
```