<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media as SpatieMedia;
use TomatoPHP\FilamentMediaManager\Models\Folder;

class SyncMediaManagerFolders extends Command
{
    protected $signature = 'filament-media-manager:sync-folders {--dry-run : Show what would be created without writing to the database}';

    protected $description = 'Backfill media manager folders from existing Spatie media records';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $created = 0;

        SpatieMedia::query()
            ->select(['model_type', 'model_id', 'collection_name'])
            ->whereNotNull('model_type')
            ->whereNotNull('model_id')
            ->whereNotNull('collection_name')
            ->distinct()
            ->orderBy('model_type')
            ->orderBy('model_id')
            ->orderBy('collection_name')
            ->chunk(500, function ($items) use (&$created, $dryRun) {
                foreach ($items as $item) {
                    $modelClass = $item->model_type;
                    $modelName = class_exists($modelClass)
                        ? class_basename($modelClass)
                        : Str::headline(class_basename((string) $modelClass));
                    $collectionName = (string) $item->collection_name;

                    $folders = [
                        [
                            'model_type' => $modelClass,
                            'model_id' => null,
                            'collection' => null,
                            'name' => $modelName,
                        ],
                        [
                            'model_type' => $modelClass,
                            'model_id' => null,
                            'collection' => $collectionName,
                            'name' => Str::headline($collectionName),
                        ],
                        [
                            'model_type' => $modelClass,
                            'model_id' => $item->model_id,
                            'collection' => $collectionName,
                            'name' => sprintf('%s [%s]', $modelName, $item->model_id),
                        ],
                    ];

                    foreach ($folders as $folderData) {
                        $query = Folder::query()
                            ->where('model_type', $folderData['model_type'])
                            ->where('collection', $folderData['collection']);

                        if (is_null($folderData['model_id'])) {
                            $query->whereNull('model_id');
                        } else {
                            $query->where('model_id', (string) $folderData['model_id']);
                        }

                        if (! $query->exists()) {
                            $created++;

                            if (! $dryRun) {
                                Folder::create([
                                    'model_type' => $folderData['model_type'],
                                    'model_id' => $folderData['model_id'],
                                    'collection' => $folderData['collection'],
                                    'name' => $folderData['name'],
                                    'is_public' => true,
                                ]);
                            }
                        }
                    }
                }
            });

        $this->info($dryRun
            ? "Would create {$created} folders."
            : "Created {$created} folders.");

        return self::SUCCESS;
    }
}
