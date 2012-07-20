<?php

class LikeableBehavior extends ModelBehavior{
	
	/**
	 * Default settings array
	 *
	 * @var array
	 */
	private $defaultSettings = array(
		'counterCache' => true
	);
	
	public $mapMethods = array(
		'/^_findLiked$/' => '_findLiked',
		'/^_findMostLiked$/' => '_findMostLiked'
	);
	
	public function setup(Model $Model, $settings) {
		if (!isset($this->settings[$Model->alias])) {
			$this->settings[$Model->alias] = $this->defaultSettings;
		}
		
		$this->settings[$Model->alias] = array_merge(
			$this->settings[$Model->alias], 
			(array)$settings
		);
		
		$Model->findMethods['liked'] = true;
		//$Model->findMethods['most_liked'] = true;
		
		// bind the Like model to the current model
		$Model->bindModel(array(
			'hasMany' => array(
				'Like' => array(
					'className' => 'Like.Like',
					'foreignKey' => 'foreign_id',
					'conditions' => array('Like.model' => $Model->alias)
				)
			)
		), false);
	
		// bind the current model with Like model
		$Model->Like->bindModel(array(
			'belongsTo' => array(
				$Model->alias => array(
					'className' => $Model->alias,
					'foreignKey' => 'foreign_id',
					'counterCache' => $this->settings[$Model->alias]['counterCache'],
					'conditions' => array('Like.model' => $Model->alias),
					'counterScope' => array('Like.model' => $Model->alias)
				)
			)
		), false);
	}
	
	/**
	 * Like an item
	 * 
	 * @example $this->Post->like(1, $this->Auth->user('id));
	 * @param int $foreign_id ID of the item
	 * @param int $user_id ID the user
	 */
	public function like(Model $Model, $foreign_id, $user_id){
		// If the item does not exist
		if(!$Model->exists($foreign_id)){
			throw new NotFoundException();
		}
	
		// If the user already like this item
		if($Model->isLikedBy($foreign_id, $user_id)){
			throw new CakeException('Already liked');
		}
		
		$Model->Like->create();
		$Model->Like->save(array(
			'Like' => array(
				'model' => $Model->alias,
				'foreign_id' => $foreign_id,
				'user_id' => $user_id
			)	
		));
	}
	
	/**
	 * Dislike an item
	 *
	 * @example $this->Post->dislike(1, $this->Auth->user('id))
	 * @param int $foreign_id ID of the item
	 * @param int $user_id ID the user
	 */
	public function dislike(Model $Model, $foreign_id, $user_id){
		if(!$Model->exists($foreign_id)){
			throw new NotFoundException();
		}
		
		if(!$Model->isLikedBy($foreign_id, $user_id)){
			throw new CakeException('Not liked');
		}
		
		$like_id = $Model->Like->field('id', array(
			'Like.model' => $Model->alias,
			'Like.foreign_id' => $foreign_id,
			'Like.user_id' => $user_id
		));
		
		$Model->Like->delete($like_id);
	}
	
	/**
	 * Check if an item is liked by a user
	 *
	 * @param int $foreign_id ID of an item
	 * @param int $user_id ID the user
	 * @return boolean True if the user like the item, false otherwise
	 */
	public function isLikedBy(Model $Model, $foreign_id, $user_id){
		// If the item does not exist
		if(!$Model->exists($foreign_id)){
			throw new NotFoundException();
		}

		$count = $Model->Like->find('count', array(
			'conditions' => array(
				'Like.model' => $Model->alias,
				'Like.foreign_id' => $foreign_id,
				'Like.user_id' => $user_id
			)
		));
		return $count == 1;
	}
	
	/**
	 * Custom find query to get the liked item
	 *
	 * @example $this->Post->find('liked', array('limit'=>5));
	 */
	public function _findLiked(Model $model, $method, $state, $query, $results = array()){
		if ($state == 'before') {
			debug($query);
			
			return $query;
		}
		return $results;
	}
	
	/**
	 * Custom find query to get the most liked Model item
	 *
	 * @example $this->Post->find('most_liked', array('limit'=>5));
	 */
	public function _findMostLiked(Model $model, $method, $state, $query, $results = array()){
		if ($state == 'before') {
			
			return $query;
		}
		return $results;
	}
}