Laravel-Database Backup / Backup in FTP
===========

A simple Laravel database service provider.

[![Latest Stable Version](https://poser.pugx.org/kirtan/backup/v/stable)](https://packagist.org/packages/kirtan/backup)
[![Total Downloads](https://poser.pugx.org/kirtan/backup/downloads)](https://packagist.org/packages/kirtan/backup)
[![License](https://poser.pugx.org/kirtan/backup/license)](https://packagist.org/packages/kirtan/backup)

Installation
------------

> If you're using Laravel 5.5+ skip the next step, as Laravel auto discover packages.
Add the service provider in `config/app.php`:

    //Backup ServiceProvider
    Kirtan\Backup\BackupServiceProvider::class,

Configuration
------------
> Run the following coomand and modify the config file(`config/backup.php`) with your ftp connections.

```php
php artisan vendor:publish --force --provider=Kirtan\Backup\BackupServiceProvider
```

> You have to provide a path of mysqldump to us

> If you are using `xampp` in your computer you will find the mysqldump at `<xampp\mysql\bin>` directory

> If you are using `linux` in your computer you will find the mysqldump at `<root/bin>` directory

> You have set that path at :
```php
'mysqldump_path' => ''
```

> You can add dynamic FTP connections with following syntax

```php
  'ftp' => [
        'host'   => '',
        'username' => '',
        'password'   => '',
        'root' => '',
        'port'  => 21,
        'force' => false,
    ],
```

> If force key is true then your root directory wil created automatcally if not exist.

Useage
------------
> To know all option run :

```php
php artisan db:backup --help
```

`OR`

```php
php artisan db:backup -h
```            

> To take backup locally run following command : 

```php
php artisan db:backup
```

> It will create a database backup at the path you have given in the `app/config.php` file `OR` it will be create a backup at default public path if you don't give location which is `public/database_backup` in your current project.

> To take backup on locally and on FTP serevr run following command

> Use the `--ftp` option for the FTP

```php
php artisan db:backup --ftp
```

> It will create a database backup at the path you have given in the `app/config.php` file in `root`.

> If the directory givenn is not avalilable it will create if you give `true` to the `force` option at `config\backup.php`

--------------

> To take backup on locally and on Google Drive run following command

> Use the `--drive` option for the Drive Backup

```php
php artisan db:backup --drive
```

> It will create a database backup at the folder you have given in the `app/config.php` file in `folder_id`.

--------------
