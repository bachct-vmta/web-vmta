<?php

namespace Packages\Core\Src\Console\Commands;

use Illuminate\Console\Command;
use Packages\Core\Src\Chunks\Storage\ChunkStorage;

/**
 * ClearChunksCommand
 *
 * Artisan command to clean up old chunk files.
 */
class ClearChunksCommand extends Command
{
    protected $signature = 'media:clear-chunks 
                            {--older-than=3 : Delete chunks older than X hours}
                            {--all : Delete all chunks regardless of age}';

    protected $description = 'Clear old chunk files from storage';

    public function handle(): int
    {
        $storage = new ChunkStorage;

        if ($this->option('all')) {
            $files = $storage->getChunkFiles();
            $count = count($files);

            foreach ($files as $chunk) {
                $chunk->delete();
            }

            $this->info("Deleted {$count} chunk files.");

            return Command::SUCCESS;
        }

        $hours = (int) $this->option('older-than');
        $deleted = $storage->deleteChunksOlderThan($hours);

        $this->info("Deleted {$deleted} chunk files older than {$hours} hours.");

        return Command::SUCCESS;
    }
}
