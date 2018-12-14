<?php
namespace Ethanzway\Repository\Doctrine;

use Closure;
use Exception;
use Illuminate\Container\Container as Application;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Ethanzway\Repository\Contracts\RepositoryInterface;
use Ethanzway\Repository\Exceptions\RepositoryException;

abstract class BaseRepository implements RepositoryInterface
{

    protected $app;

    protected $manager;

    protected $builder = null;

    protected $scopeQuery = null;

    public function __construct(Application $app, EntityManager $manager)
    {
        $this->app = $app;
        $this->manager = $manager;
        $this->boot();
    }

    public function boot()
    {
        
    }

    abstract public function model();

    public function find($id, $columns = ['*'])
    {
        $result = $this->manager->find($this->model(), $id);

        return $this->parserResult($result);
    }

    public function save($model)
    {
        $this->manager->persist($model);
        $this->manager->flush();
    }

    public function remove($model)
    {
        $this->manager->remove($model);
        $this->manager->flush();
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
            $this->builder = $callback($this->builder);
        }

        if (count($this->builder->getDQLPart('select')) === 0) {
            $this->builder = $this->builder->select('o')->from($this->model(), 'o');
        }

        return $this;
    }

    public function first($columns = ['*'])
    {
        $this->builder = $this->manager->createQueryBuilder();

        $this->applyScope();

        $this->builder = $this->builder->setMaxResults(1);

        $result = $this->builder->getQuery()->getOneOrNullResult();

        return $this->parserResult($result);
    }

    public function all($columns = ['*'])
    {
        $this->builder = $this->manager->createQueryBuilder();

        $this->applyScope();

        $results = $this->builder->getQuery()->getResult();

        return $this->parserResult($results);
    }

    public function paginate($limit = null, $columns = ['*'], $method = "paginate", $characteristic = "page")
    {

        $page = Paginator::resolveCurrentPage($characteristic);

        $this->builder = $this->manager->createQueryBuilder();

        $this->applyScope();

        $query = $this->builder->getQuery();

        $query->setFirstResult(($page - 1) * $limit)
              ->setMaxResults($limit);

        $doctrinePaginator = new DoctrinePaginator($query, true);

        $results = iterator_to_array($doctrinePaginator);
        $path = Paginator::resolveCurrentPath();

        return new LengthAwarePaginator(
            $results,
            $doctrinePaginator->count(),
            $limit,
            $page,
            compact('path')
        );
    }

    public function parserResult($result)
    {
        return $result;
    }
}
