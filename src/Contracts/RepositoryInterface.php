<?php
namespace Ethanzway\Repository\Contracts;

/**
 * Interface RepositoryInterface
 * @package Ethanzway\Repository\Contracts
 */
interface RepositoryInterface
{

    /**
     * Find data by id
     *
     * @param       $id
     * @param array $columns
     *
     * @return mixed
     */
    public function find($id, $columns = ['*']);

    /**
     * Save a entity in repository
     *
     * @param $entity
     *
     * @return mixed
     */
    public function save($entity);

    /**
     * remove a entity in repository
     *
     * @param $entity
     *
     * @return mixed
     */
    public function remove($entity);

    /**
     * Query Scope
     *
     * @param \Closure $scope
     *
     * @return $this
     */
    public function scopeQuery(\Closure $scope);

    /**
     * Reset Query Scope
     *
     * @return $this
     */
    public function resetScope();

    /**
     * Retrieve first data of repository
     *
     * @param array $columns
     *
     * @return mixed
     */
    public function first($columns = ['*']);

    /**
     * Retrieve all data of repository
     *
     * @param array $columns
     *
     * @return mixed
     */
    public function all($columns = ['*']);

    /**
     * Retrieve all data of repository, paginated
     *
     * @param null  $limit
     * @param array $columns
     *
     * @return mixed
     */
    public function paginate($limit = null, $columns = ['*']);
}
