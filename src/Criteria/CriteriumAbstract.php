<?php namespace RepositoryPackage\Criteria;

use RepositoryPackage\Contracts\IRepository;

abstract class CriteriumAbstract {
	public abstract function apply($model, IRepository $repository); 
}
