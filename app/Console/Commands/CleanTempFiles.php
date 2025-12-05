<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CleanTempFiles extends Command
{
    protected $signature = 'clean:temp-files';
    protected $description = 'Nettoyer les fichiers temporaires';

    public function handle()
    {
        $tempDir = storage_path('app/temp/');
        
        if (File::exists($tempDir)) {
            $files = File::files($tempDir);
            $deletedCount = 0;
            
            foreach ($files as $file) {
                // Supprimer les fichiers de plus de 1 heure
                if (time() - File::lastModified($file) > 3600) {
                    File::delete($file);
                    $deletedCount++;
                }
            }
            
            $this->info("{$deletedCount} fichiers temporaires supprimés.");
        } else {
            $this->info("Le répertoire temporaire n'existe pas.");
        }
        
        return 0;
    }
}