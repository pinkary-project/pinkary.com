<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Imagick;

final class DeleteImagesMetadataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:images-metadata';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete the metadata from uploaded images';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $disk = Storage::disk('public');
        $directory = 'images';

        $files = $disk->allFiles($directory);

        $progressBar = $this->output->createProgressBar(count($files));
        $progressBar->start();

        foreach ($files as $file) {
            $extension = pathinfo((string) $file, PATHINFO_EXTENSION);
            if (in_array(mb_strtolower($extension), ['jpg', 'jpeg', 'png', 'gif'])) {
                $filePath = $disk->path($file);

                $imagick = new Imagick($filePath);
                $imagick->stripImage();
                $imagick->writeImage($filePath);
                $imagick->clear();
                $imagick->destroy();
            }

            $progressBar->advance();
        }

        $this->info("\nThe image metadata has been removed.");
    }
}
