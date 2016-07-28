<?php namespace RepositoryPackage\Repository;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Container\Container as App;
use Illuminate\Support\Collection;
use RepositoryPackage\Contracts\IRepository;
use RepositoryPackage\Contracts\ICriterium;
use RepositoryPackage\Exceptions\RepositoryException;

abstract class RepositoryAbstract implements IRepository, ICriterium {
	/** @var App */
	protected $app;
	/** @var mixed */
	protected $entity;
	/** @var Collection */
	protected $criteria;
	/** @var bool */
	protected $skipCriteria = false;
	
	public function __construct(App $app, Collection $criteria) {
		$this->app = $app;
		$this->criteria = $criteria;
		$this->resetScope();
		$this->makeEntity();
	}
	
	// Returns the name of the entity
	public abstract function entity();
	
	// Makes a(n) (Eloquent) model for our generic repository (RepositoryAbstract) to use
	private function makeEntity() {
		// make entity through app container
		$entity = $this->app->make ( $this->entity () );
		
		// if entity is not a(n) (Eloquent) model, fire an exception!
		if (! $entity instanceof Model)
			throw new RepositoryException ( "Class {$this->entity()} must be an instance of " . Model::class . '!' );
		
		$this->entity = $entity->newQuery();
		return $this->entity;
	}
	
	// Resets all search criteria for the repository
	private function resetScope() {
		$this->skipCriteria(false);
		return $this;
	}
	
	
	
	/**
	 * Retrieves all records in the database.
	 * 
	 * {@inheritDoc}
	 * @see \EasyCMS\Repositories\Contracts\IRepository::all()
	 */
	public function all(){
		$this->applyCriteria();
		return $this->entity->get();
	}
	
	/**
	 * Paginates records from the database.
	 * 
	 * {@inheritDoc}
	 * @see \EasyCMS\Repositories\Contracts\IRepository::paginate()
	 */
	public function paginate($numOfPages = 10){
		$this->applyCriteria();
		return $this->entity->paginate($numOfPages);
	}
	
	/**
	 * Finds records in database by their identifier [default = 'id'].
	 * 
	 * {@inheritDoc}
	 * @see \EasyCMS\Repositories\Contracts\IRepository::find()
	 */
	public function find($id){
		$this->applyCriteria();
		return $this->entity->find($id);
	}
	
	/**
	 * Finds records in database by any other attribute.
	 *  
	 * {@inheritDoc}
	 * @see \EasyCMS\Repositories\Contracts\IRepository::findBy()
	 */
	public function findBy($field, $value){
		$this->applyCriteria();
		return $this->entity->where($field, $value)->first();
	}
	
	/**
	 * Inserts data into the database to create a new record.
	 * 
	 * {@inheritDoc}
	 * @see \EasyCMS\Repositories\Contracts\IRepository::create()
	 */
	public function create(array $data){
		$entity = $this->app->make($this->entity());
		return $entity->create($data);
	}
	
	/**
	 * Updates existing record in database by referencing to their id.
	 * 
	 * {@inheritDoc}
	 * @see \EasyCMS\Repositories\Contracts\IRepository::update()
	 */
	public function update($id, array $data){ 
		return $this->entity->find($id)->update($data);
	}
	
	/**
	 * Deletes record from database.
	 * 
	 * {@inheritDoc}
	 * @see \EasyCMS\Repositories\Contracts\IRepository::delete()
	 */
	public function delete($id){
		return $this->entity->find($id)->delete();
	}
	
	
	/**
	 * 
	 * @param string $status
	 * @return \EasyCMS\Repositories\RepositoryAbstract
	 */
	public function skipCriteria($status = false) {
		$this->skipCriteria = $status;
		return $this;
	}
	
	/**
	 * 
	 * @return Collection
	 */
	public function getCriteria() {
		return $this->criteria;
	}
	
	/**
	 * 
	 * @param ICriterium $criterium
	 * @return \EasyCMS\Repositories\RepositoryAbstract
	 */
	public function getByCriteria(CriteriumAbstract $criterium) {
		$this->entity = $criterium->apply($this->entity, $this);
		return $this;
	}
	
	
	/**
	 * 
	 * @param ICriterium $criterium
	 * @return \EasyCMS\Repositories\RepositoryAbstract
	 */
	public function pushCriteria(CriteriumAbstract $criterium) {
		$this->criteria->push($criterium);
		return $this;
	}
	
	/**
	 * 
	 * @return \EasyCMS\Repositories\RepositoryAbstract
	 */
	public function applyCriteria() {
		// skip criteria? yes
		if ($this->skipCriteria){
			return $this;
		}
		
		// otherwise, apply them
		foreach ( $this->getCriteria() as $criterium ) {
			if ($criterium instanceof CriteriumAbstract){
				$this->entity = $criterium->apply($this->entity, $this);
			}
		}
		return $this;
		
	}

}