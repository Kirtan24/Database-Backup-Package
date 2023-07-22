<?php

namespace Kirtan\Backup\Console;

use Exception;
use Google\Client;
use Google\Service\Drive;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class dbBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "db:backup {--ftp : Use this option to take backup on your FTP server} {--drive : Use this option to take backup on google drive}";

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
        if (config('backup.mysqldump_path') != '') {
            $path = config('backup.backup_location');
            if ($this->option('ftp')) {
                $progressBar1 = $this->output->createProgressBar(4);
            } elseif ($this->option('drive')) {
                $progressBar1 = $this->output->createProgressBar(1);
            } else {
                $progressBar1 = $this->output->createProgressBar(3);
            }

            $progressBar1->start();
            $this->info('   -> Database backup started...');
            sleep(2);
            if ($path != '') {
                $progressBar1->advance(1);
                $this->info('   -> Your database backup is stroed at -> ' . $path);
            } else {
                $path = public_path('database_backup');
                $progressBar1->advance(1);
                $this->info('   -> Your database backup is stroed at -> ' . $path);
            }
            //If Directory doesn't exist it wil create a new directory
            if (!File::isDirectory($path)) {
                File::makeDirectory($path, 0777, true, true);
            }

            $mysqlDumpPath = config('backup.mysqldump_path');
            $file = env('DB_DATABASE') . "_" . strtotime(now()) . ".sql";
            $command = $mysqlDumpPath . " --user=" . env('DB_USERNAME') . " --password=" . env('DB_PASSWORD') . " --host=" . env('DB_HOST') . " " . env('DB_DATABASE') . " > " . $path . "/" . $file;

            $returnVar = null;
            $output = null;
            //Running Command
            $status = exec($command, $output, $returnVar);
            if ($returnVar == 0) {
                //Sorting For Last 5 Database Backup
                //------------------------------------------------//

                // laravel shorting section
                $files = glob($path . '/*.sql');

                usort($files, function ($a, $b) {
                    return filemtime($a) < filemtime($b);
                });
                $keep = array_slice($files, 0, 5);
                $delete = array_slice($files, 5);
                foreach ($delete as $d) {
                    unlink($d);
                }

                $progressBar1->advance(1);
                $this->info('   -> Last 5 backups stored successfully');

                //------------------------------------------------//

                //--------------------FTP section------------------------//
                if ($this->option('ftp')) {
                    $dir_flg = 1;
                    $config = config('backup.ftp');
                    if ($config['host'] == null || $config['username'] == null || $config['password'] == null || $config['root'] == null) {
                        $progressBar1->advance(-1);
                        $this->error("  -> * Please fill required field at `config/backup.php`");
                    } else {
                        //Checking connection and taking backup
                        $server_root = $config['root'];
                        $ftp_connect = ftp_connect($config['host'], 21) or die("-> * Couldn't Connect to -> " . $config['host']);
                        ftp_login($ftp_connect, $config['username'], $config['password']) or die("-> * Connection Failed");
                        ftp_pasv($ftp_connect, true);

                        // Directly to ftp

                        // // open a temporary file for writing
                        // $handle = fopen("php://temp", "r+");

                        // // create the mysqldump command
                        // $command = $mysqlDumpPath." --user=".env('DB_USERNAME')." --password=".env('DB_PASSWORD')." --host=".env('DB_HOST')." ".env('DB_DATABASE');

                        // // open a pipe to the mysqldump command
                        // $pipe = popen($command, "r");

                        // // read the output of the mysqldump command and write it to the temporary file
                        // while (!feof($pipe)) {
                        //     fwrite($handle, fread($pipe, 8192));
                        // }

                        // // close the pipe to the mysqldump command
                        // pclose($pipe);

                        // // rewind the file pointer to the beginning
                        // rewind($handle);

                        // // write the contents of the temporary file to the FTP server
                        // if (ftp_fput($ftp_connect, $file, $handle, FTP_BINARY)) {
                        //     $this->info("Backup uploaded successfully");
                        // } else {
                        //     $this->error("Backup upload failed");
                        // }

                        // // close the temporary file and the FTP connection
                        // fclose($handle);
                        // ftp_close($ftp_connect);

                        //------------------------------------------------------------
                        //If Directory you given is not exist it will create
                        try {
                            if (ftp_nlist($ftp_connect, $server_root) == false && $config['force'] == true) {
                                ftp_mkdir($ftp_connect, $server_root);
                            } elseif (ftp_nlist($ftp_connect, $server_root) == false && $config['force'] == false) {
                                $dir_flg = 0;
                                $progressBar1->advance(-1);
                                throw new Exception("   -> * The directory you given is not exist in your FTP...\n-> * If you want to create the directory automaticaly if not exist then give force option `true` in config file `config/backup.php` file");
                            } else {
                            }
                        } catch (Exception $e) {
                            $this->error($e->getMessage());
                        }

                        //------------------------------------------------------------

                        //------------------------------------------------------------

                        //Putting the backup file in FTP server
                        if ($dir_flg == 1) {
                            if (ftp_put($ftp_connect, "$server_root/$file", "$path/$file", FTP_ASCII)) {
                                $progressBar1->advance(1);
                                $this->info('   -> Backup Success at FTP -> ' . $file);
                            } else {
                                
                                $this->error('   -> * Failed To Backup at FTP..!');
                            }
                            function clean_nlist($ftp_connect, $server_dir)
                            {
                                $files_on_ftp = ftp_nlist($ftp_connect, $server_dir);
                                return array_values(array_diff($files_on_ftp, array($server_dir . '/.', $server_dir . '/..')));
                            }
                            //Sorting For Last 5 Database Backup
                            $backup_files = clean_nlist($ftp_connect, $server_root);
                            usort($backup_files, function ($a, $b) use ($ftp_connect) {
                                return ftp_mdtm($ftp_connect, $a) > ftp_mdtm($ftp_connect, $b);
                            });
                            $keep = array_slice($backup_files, -5);
                            $delete = array_diff($backup_files, $keep);
                            foreach ($delete as $d) {
                                ftp_delete($ftp_connect, $d);
                            }
                            // ------------------------------------------------------------
                            ftp_close($ftp_connect);
                        } else {

                        }
                    }
                } elseif ($this->option('drive')) {
                    $dir_flg = 1;
                    $config = config('backup.drive');
                    if ($config['CLIENT_ID'] == null || $config['CLIENT_SECRET'] == null || $config['REFRESH_TOKEN'] == null || $config['FOLDER_ID'] == null) {
                        $progressBar1->advance(-1);
                        $this->error("  -> * Please fill required field at in 'drive' key `config/backup.php`");
                    } else {
                        $fileD = File::get($path . "/" . $file);
                        try {
                            $progressBar1->advance(1);
                            $this->info('   -> Initialting drive backup...');

                            $client = new Client();
                            $client->setClientId($config['CLIENT_ID']);
                            $client->setClientSecret($config['CLIENT_SECRET']);
                            $client->refreshToken($config['REFRESH_TOKEN']);

                            $service = new Drive($client);

                            $fileMetadata = new \Google\Service\Drive\DriveFile([
                                'name' => $file,
                                'parents' => [$config['FOLDER_ID']],
                            ]);

                            $content = File::get($path . '/' . $file);

                            $driveFile = $service->files->create($fileMetadata, [
                                'data' => $content,
                                'uploadType' => 'multipart',
                                'fields' => 'id',
                            ]);

                            $fileId = $driveFile->id;

                            $progressBar1->advance(1);
                            $this->info('   -> Backup taken in drive successfully - File Id : ' . $fileId);

                            $folderId = $config['FOLDER_ID'];

                            $files = $service->files->listFiles([
                                'q' => "'$folderId' in parents",
                                'fields' => 'files(id, name, modifiedTime)',
                            ])->getFiles();

                            // Sort the files by modified time in descending order
                            usort($files, function ($a, $b) {
                                return strcmp($b->getModifiedTime(), $a->getModifiedTime());
                            });

                            // Prune the old files, keeping only the last 5 files
                            $keepFiles = array_slice($files, 0, 5);
                            $deleteFiles = array_slice($files, 5);

                            // Delete the old files
                            foreach ($deleteFiles as $file) {
                                $service->files->delete($file->getId());
                            }

                            //------------------------------------------------------------
                        } catch (Exception $e) {
                            $progressBar1->advance(-1);
                            $this->error($e->getMessage());
                        }

                        //------------------------------------------------------------
                    }
                }
                $progressBar1->advance(1);
                $this->info('   -> Database Backup Created Successfully...');
                $progressBar1->finish();
            } else {
                $progressBar1->advance(-1);
                $this->error('  -> * Please give propper path of the `mysqldump`');
            }
        } else {
            $this->error('  -> * Please enter `mysqldump` path..!!');
        }
    }
}