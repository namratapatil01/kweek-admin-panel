<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ScaffoldAdminModuleViews extends Command
{
    protected $signature = 'kweek:scaffold-admin-views {--force : Overwrite existing views}';

    protected $description = 'Generate MySQL CRUD Blade views for all admin modules (no Firebase)';

    public function handle(): int
    {
        $modules = config('admin_modules', []);
        $created = 0;
        $skipped = 0;

        foreach ($modules as $slug => $config) {
            $viewFolder = $config['view'] ?? str_replace('-', '_', $slug);
            $path = resource_path("views/{$viewFolder}");

            if (! File::isDirectory($path)) {
                File::makeDirectory($path, 0755, true);
            }

            $files = [
                'index.blade.php' => "@include('admin.partials.crud-index')\n",
                'create.blade.php' => "@include('admin.partials.crud-form')\n",
                'edit.blade.php' => "@include('admin.partials.crud-form')\n",
                'show.blade.php' => "@include('admin.partials.crud-show')\n",
            ];

            foreach ($files as $filename => $content) {
                $filePath = "{$path}/{$filename}";

                if (File::exists($filePath) && ! $this->option('force')) {
                    $skipped++;
                    continue;
                }

                File::put($filePath, $content);
                $created++;
            }

            $this->line("  ✓ {$viewFolder}");
        }

        $this->info("Scaffolded {$created} view files ({$skipped} skipped).");

        return self::SUCCESS;
    }
}
