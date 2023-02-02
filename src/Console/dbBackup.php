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
    protected $signature = "db:backupmysql {ftp?}";

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
            $this->info('Database backup started...');

            $path=config('backup.backup_location');
            if($path != '')
                $this->info('Your database is stroed at -> '.$path);
            else{
                $path=public_path('\database_backup');
                $this->info('Your database is stroed at -> '.$path);
            }
            
            $mysqlDumpPath=config('backup.mysqldump_path');

            if(!File::isDirectory($path)){
                File::makeDirectory($path, 0777, true, true);
            }

            $file=env('DB_DATABASE')."_".strtotime(now()).".sql";

            $command= $mysqlDumpPath." --user=".env('DB_USERNAME')." --password=".env('DB_PASSWORD')." --host=".env('DB_HOST')." ".env('DB_DATABASE')." > ".$path."/".$file;

            $returnVar = NULL;
            $output = NULL;
            exec($command,$output,$returnVar);

            // laravel section
            $files=glob($path.'/*.sql');
                
            usort($files,function($a,$b){
                return filemtime($a) < filemtime($b);
            });

            $keep=array_slice($files,0,5);
            $delete=array_slice($files,5);

            foreach($delete as $d){
                unlink($d);
            }            

            // ftp section        

            $ftp=$this->argument('ftp');
            $ftp_status=($ftp==null)?true:false;
            if($ftp_status==0){
                // $this->info(config("backup.ftp"));
                $drive=config('backup.ftp');
                $config=Storage::disk($drive);
                $files=$config->put($file,'r+');

                $ftp=$config->allFiles();

                usort($ftp,function($a,$b){
                    return Storage::disk($drive)->lastModified($a) <=> Storage::disk($drive)->lastModified($b);
                });

                $keep = array_slice($ftp, -5);
                $delete = array_diff($ftp, $keep);

                foreach($delete as $d){
                    if($config->exists($d)){
                        $config->delete($d);
                    }
                }
            }

            else{
            }

            $this->newLine();
            $this->info('Database Backup Created Successfully...');
        }
        else{
            $this->error('somthing went wrong when finding mysqldump path..!!');           
        }
    }
}