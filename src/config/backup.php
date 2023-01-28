<?php

return[

    /**
     * ---------------------------------------------------------------------
     * Database Backup Path
     * ---------------------------------------------------------------------
     * 
     * The 'db_backup_path' is where you want to backup your database
     * 
     * if it is empty it will backup automatically at '../database_backup' in your current project
     */

    'db_backup_path' => 'database_backup',

    /**
     * ---------------------------------------------------------------------
     * MySQLDump Path
     * ---------------------------------------------------------------------
     * 
     * the 'mysqlidump_ptah' is the path by which the backup being proccessed
     * 
     * you must have to specify it
     */

    'mysqldump_path' => 'C:\xampp\mysql\bin\mysqldump.exe',
    
    /**
     *--------------------------------------------------------------------------
     * FTP Connections
     *--------------------------------------------------------------------------
     *
     * Here are each of the FTP connections setup for your application.
     *
     */

    'ftp' => [
        'host'   => '',
        'port'  => 21,
        'username' => '',
        'password'   => '',
        'root' => '',
    ],
];