<?php

namespace Kirtan\Backup\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class dbBackup extends Command
{    
    /**
     * The name and signature of the console command.
     *
     * @var string
     */    
    protected $signature = "db:backup {--ftp : Use this option to take backup on your FTP server}";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create mysql database backup';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {                  
        if(config('backup.mysqldump_path') != '')
        {
            $this->info('-> Database backup started...');
            $path=config('backup.backup_location');
            if($path != '')
                $this->info('-> Your database backup is stroed at -> '.$path);
            else{
                $path=public_path('\database_backup');
                $this->info('-> Your database backup is stroed at -> '.$path);
            }
            //If Directory doesn't exist it wil create a new directory
            if(!File::isDirectory($path)){
                File::makeDirectory($path, 0777, true, true);
            }
            
            $mysqlDumpPath=config('backup.mysqldump_path');    
            $file=env('DB_DATABASE')."_".strtotime(now()).".sql";
            $command= $mysqlDumpPath." --user=".env('DB_USERNAME')." --password=".env('DB_PASSWORD')." --host=".env('DB_HOST')." ".env('DB_DATABASE')." > ".$path."/".$file;
            $returnVar = NULL;
            $output = NULL;
            //Running Command
            exec($command,$output,$returnVar);

            //Sorting For Last 5 Database Backup
            //------------------------------------------------//
            
            // laravel shorting section
            $files=glob($path.'/*.sql');
                
            usort($files,function($a,$b){
                return filemtime($a) < filemtime($b);
            });
            $keep=array_slice($files,0,5);
            $delete=array_slice($files,5);
            foreach($delete as $d){
                unlink($d);
            }
            //------------------------------------------------//

            //--------------------FTP section------------------------//
            // $ftp=$this->argument('ftp');
            // $ftp_status=($ftp==null)?true:false;
            
            if($this->option('ftp')){
                $config=config('backup.ftp');                
                if($config['host'] == null || $config['username'] == null || $config['password'] == null || $config['root'] == null)
                    $this->error("-> * Please fill required field at `config/backup.php`");
                else{
                    //Checking connection and taking backup
                    $server_root=$config['root'];                
                    $ftp_connect=ftp_connect($config['host'],21) or die("-> * Couldn't Connect to -> ".$config['host']);
                    ftp_login($ftp_connect,$config['username'],$config['password']) or die("-> * Connection Failed");
                    ftp_pasv($ftp_connect,true);
                    //------------------------------------------------------------
                    //If Directory you given is not exist it will create
                    if(ftp_nlist($ftp_connect,$server_root) == false && $config['force'] == true){
                        ftp_mkdir($ftp_connect,$server_root);
                    }
                    elseif(ftp_nlist($ftp_connect,$server_root) == false && $config['force'] == false){
                        $this->error('-> * The directory you given is not exist in your FTP...');
                        $this->info('-> * If you want to create the directory automaticaly iif not exist then give force option true in config file `config/backup.php` file');                        
                    }
                    else{
                    }
                    //------------------------------------------------------------


                    //------------------------------------------------------------

                    //Putting the backup file in FTP server                    
                    if(ftp_put($ftp_connect,"$server_root/$file","$path/$file",FTP_ASCII)){
                        $this->info('-> Backup Success -> '.$file);
                    }
                    else{
                        $this->info('-> * Failed To Backup..!');
                    }
                    function clean_nlist($ftp_connect , $server_dir){
                        $files_on_ftp=ftp_nlist($ftp_connect,$server_dir);
                        return array_values(array_diff($files_on_ftp,array($server_dir.'/.',$server_dir.'/..')));
                    }
                    //------------------------------------------------------------


                    //------------------------------------------------------------

                    //Sorting For Last 5 Database Backup
                    $backup_files=clean_nlist($ftp_connect,$server_root);
                    usort($backup_files,function($a,$b) use ($ftp_connect){
                        return ftp_mdtm($ftp_connect, $a) > ftp_mdtm($ftp_connect, $b);
                    });
                    $keep = array_slice($backup_files, -5);
                    $delete = array_diff($backup_files, $keep);
                    foreach($delete as $d){
                        ftp_delete($ftp_connect,$d);
                    }
                    //------------------------------------------------------------
                    ftp_close($ftp_connect);
                }                            
            }
            else{
            }
            $this->info('-> Database Backup Created Successfully...');
        }
        else{
            $this->error('-> * Somthing went wrong when finding mysqldump path..!!');
        }        
    }
}