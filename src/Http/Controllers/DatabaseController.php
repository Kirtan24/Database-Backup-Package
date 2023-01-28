<?php

namespace Kirtan\Backup\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class DatabaseController extends Controller
{
    public function dbDownload()
    {
        $path="C:\\xampp\\htdocs\\AssetManagement\\database_backup";
        $mysqlDumpPath='C:\xampp\mysql\bin\mysqldump.exe';        

        $file=env('DB_DATABASE')."_".strtotime(now()).".sql";

        if(!File::isDirectory($path)){
            File::makeDirectory($path, 0777, true, true);
        }   
    
        $command= $mysqlDumpPath." --user=".env('DB_USERNAME')." --password=".env('DB_PASSWORD')." --host=".env('DB_HOST')." ".env('DB_DATABASE')." > ".$path."/".$file;
        Storage::disk('ftp')->put($file,'r+');

        $returnVar = NULL;
        $output = NULL;
        exec($command,$output,$returnVar);

        // Laravel Section
        $files=glob($path.'/*.sql');
            
        usort($files,function($a,$b){
            return filemtime($a) < filemtime($b);
        });

        $keep=array_slice($files,0,5);
        $delete=array_slice($files,5);

        foreach($delete as $d){
            unlink($d);
        }            

        // Fizezilla Section
        $files=Storage::disk('ftp')->put($file,'r+');

        $ftp=Storage::disk('ftp')->allFiles();

        usort($ftp,function($a,$b){
            return Storage::disk('ftp')->lastModified($a) <=> Storage::disk('ftp')->lastModified($b);
        });

        $keep = array_slice($ftp, -5);
        $delete = array_diff($ftp, $keep);

        foreach($delete as $d){
            if(Storage::disk('ftp')->exists($d)){
                Storage::disk('ftp')->delete($d);
            }
        }

        return response()->json(["success"=>"Database Backup created successfully"]);
    }
}