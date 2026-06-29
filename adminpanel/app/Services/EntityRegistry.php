<?php

namespace App\Services;

use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class EntityRegistry
{
    /** @var array<string, EntityCrudService> */
    protected array $services = [];

    public function __construct()
    {
        foreach (config('kweek_entities', []) as $collection => $meta) {
            $modelClass = $meta['model'] ?? null;
            if (! $modelClass || ! class_exists($modelClass)) {
                continue;
            }

            /** @var Model $model */
            $model = new $modelClass();
            $repository = new class($model) extends BaseRepository {
                protected function filterableColumns(): array
                {
                    return [
                        'status', 'role', 'section_id', 'sectionId', 'authorID', 'vendorID',
                        'driverID', 'driverId', 'user_id', 'userId', 'isActive', 'active',
                        'type', 'publish', 'isEnabled', 'paymentStatus',
                    ];
                }

                protected function searchableColumns(): array
                {
                    return ['title', 'name', 'email', 'firstName', 'lastName', 'phoneNumber', 'code'];
                }
            };

            $this->services[$this->slug($collection)] = new EntityCrudService(
                $repository,
                $model->getFillable() !== [] ? $model->getFillable() : ['id', 'payload']
            );
        }
    }

    public function get(string $slug): EntityCrudService
    {
        $slug = $this->slug($slug);

        if (! isset($this->services[$slug])) {
            throw new InvalidArgumentException("Unknown entity [{$slug}].");
        }

        return $this->services[$slug];
    }

    public function slugs(): array
    {
        return array_keys($this->services);
    }

    protected function slug(string $name): string
    {
        return str_replace('_', '-', strtolower($name));
    }
}
