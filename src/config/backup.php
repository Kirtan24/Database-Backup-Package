<?php

return[

    /**
     * ---------------------------------------------------------------------
     * Database Backup Location
     * ---------------------------------------------------------------------
     * 
     * The 'db_backup_path' is where you want to backup your database
     * 
     * if it will empty it will backup automatically at 'public/database_backup' in your current laravel project
     */

    'backup_location' => '',

    /**
     * ---------------------------------------------------------------------
     * MySQLDump Path
     * ---------------------------------------------------------------------
     * 
     * the 'mysqldump_ptah' is the path by which the backup being proccessed
     * 
     * you must have to specify it
     */

    'mysqldump_path' => 'C:\xampp\mysql\bin\mysqldump',
    
    /**
     *--------------------------------------------------------------------------
     * FTP Connections
     *--------------------------------------------------------------------------
     *
     * Here are each of the FTP connections setup for your application.
     * 
     * If you will not specifie it we will give you an Exception
     * 
     * You Must define all the ftp configuration
     */
    'disks' => [
        'ftp' => [
            'host'   => '',
            'username' => '',
            'password'   => '',
            'root' => '',
            'port'  => 21,
        ],
    ]
];