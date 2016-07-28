<?php namespace RepositoryPackage\Contracts;

interface IRepository {
	public function all();
	public function paginate($numOfPages = 10);
	public function find($id);
	public function findBy($field, $value);
	public function create(array $data);
	public function update($id, array $data);
	public function delete($id);
}