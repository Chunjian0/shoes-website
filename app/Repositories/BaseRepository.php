<?php

declare(strict_types=1);

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

abstract class BaseRepository
{
    protected Model $model;

    public function __construct()
    {
        $this->model = $this->getModelClass();
    }

    abstract protected function getModelClass(): Model;

    public function find(int $id): ?Model
    {
        return $this->model->find($id);
    }

    public function findOrFail(int $id): Model
    {
        return $this->model->findOrFail($id);
    }

    public function all(): Collection
    {
        return $this->model->all();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->paginate($perPage);
    }

    public function create(array $data): Model
    {
        try {
            Log::info('BaseRepository::create - Start creating:', [
                'model' => get_class($this->model),
                'data' => $data
            ]);

            $model = $this->model->create($data);

            Log::info('BaseRepository::create - Created successfully:', [
                'model' => get_class($this->model),
                'id' => $model->id
            ]);

            return $model;
        } catch (\Exception $e) {
            Log::error('BaseRepository::create - Creation failed:', [
                'model' => get_class($this->model),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function update(Model $model, array $data): bool
    {
        return $model->update($data);
    }

    public function delete(Model $model): bool
    {
        return $model->delete();
    }
} 