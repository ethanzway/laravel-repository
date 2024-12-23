<?php
namespace Ethanzway\Repository\Eloquent;

use Closure;
use Exception;
use Illuminate\Container\Container as Application;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Ethanzway\Repository\Contracts\RepositoryInterface;
use Ethanzway\Repository\Exceptions\RepositoryException;

abstract class BaseRepository implements RepositoryInterface
{

    protected $app;

    protected $model;

    protected $scopeQuery = null;

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->makeModel();
        $this->boot();
    }

    public function boot()
    {

    }

    public function resetModel()
    {
        $this->makeModel();
    }

    public function makeModel()
    {
        $model = $this->app->make($this->model());

        if (!$model instanceof Model) {
            throw new RepositoryException("Class {$this->model()} must be an instance of Illuminate\\Database\\Eloquent\\Model");
        }

        return $this->model = $model;
    }

    abstract public function model();

    public function find($id, $columns = ['*'])
    {
        $this->applyScope();
        
        $model = $this->model->findOrFail($id, $columns);

        $this->resetModel();
        $this->resetScope();

        return $this->parserResult($model);
    }
    
    public function save($model)
    {
        $model->save();
    }
    
    public function remove($model)
    {
        $model->delete();
    }

    public function scopeQuery(\Closure $scope)
    {
        $this->scopeQuery = $scope;

        return $this;
    }

    public function resetScope()
    {
        $this->scopeQuery = null;

        return $this;
    }

    protected function applyScope()
    {
        if (isset($this->scopeQuery) && is_callable($this->scopeQuery)) {
            $callback = $this->scopeQuery;
            $this->model = $callback($this->model);
        }

        return $this;
    }

    public function first($columns = ['*'])
    {
        $this->applyScope();

        $results = $this->model->first($columns);

        $this->resetModel();
        $this->resetScope();

        return $this->parserResult($results);
    }

    public function all($columns = ['*'])
    {
        $this->applyScope();

        if ($this->model instanceof Builder) {
            $results = $this->model->get($columns);
        } else {
            $results = $this->model->all($columns);
        }

        $this->resetModel();
        $this->resetScope();

        return $this->parserResult($results);
    }

    public function paginate($limit = null, $columns = ['*'], $method = "paginate", $characteristic = "page")
    {
        $this->applyScope();
        
        $limit = is_null($limit) ? config('repository.pagination.limit', 15) : $limit;
        $results = $this->model->{$method}($limit, $columns, $characteristic);
        $results->appends(app('request')->query());

        $this->resetModel();
        $this->resetScope();

        return $this->parserResult($results);
    }

    public function parserResult($result)
    {
        return $result;
    }
}
