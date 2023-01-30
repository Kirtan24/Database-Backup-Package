Laravel-Database Backup / Backup in FTP
===========

A simple Laravel 8/9 database service provider.

[![Latest Stable Version](https://poser.pugx.org/kirtan/backup/v/stable)](https://packagist.org/packages/kirtan/backup)
[![Total Downloads](https://poser.pugx.org/kirtan/backup/v/stable)](https://packagist.org/packages/kirtan/backup)
[![License](https://poser.pugx.org/kirtan/backup/license)](https://packagist.org/packages/kirtan/backup)

Installation
------------

> If you're using Laravel 5.5+ skip the next step, as Laravel auto discover packages.
Add the service provider in `config/app.php`:

    //Backup ServiceProvider
    Kirtan\Backup\BackupServiceProvider::class,

Configuration
------------
> Run `php artisan vendor:publish --force --provider=Kirtan\Backup\BackupServiceProvider` and modify the config file(`config/backup.php`) with your ftp connections.

> You can add dynamic FTP connections with following syntax

```php
  'ftp' => [
        'host'   => '',
        'username' => '',
        'password'   => '',
        'root' => '',
        'port'  => 21,
    ],    
```

Useage
------------

> To take backup please run the following command

```php
    php artisan db:backupmysql
```
> It will create a database backup at the path you have given in the `app/config.php` file or it will be create a backup at default path which is `/database_backup` in your current project.

--------------

