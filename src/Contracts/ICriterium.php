<?php namespace RepositoryPackage\Contracts;

use RepositoryPackage\Criteria\CriteriumAbstract;

interface ICriterium {
	public function skipCriteria($status = false);
	public function getCriteria();
	public function getByCriteria(CriteriumAbstract $criterium);
	public function pushCriteria(CriteriumAbstract $criterium);
	public function applyCriteria();
}