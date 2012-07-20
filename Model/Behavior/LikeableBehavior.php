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
	
	public function setup(Model $Model, $settings) {
		if (!isset($this->settings[$Model->alias])) {
			$this->settings[$Model->alias] = $this->defaultSettings;
		}
		
		$this->settings[$Model->alias] = array_merge(
			$this->settings[$Model->alias], 
			(array)$settings
		);
		
		$Model->bindModel(array(
			'hasMany' => array(
				'Like' => array(
					'className' => 'Like.Like',
					'foreignKey' => 'foreign_id',
					'conditions' => array('Like.model' => $Model->alias)
				)
			)
		), false);
	
		
		$Model->Like->bindModel(array(
			'belongsTo' => array(
				$Model->alias => array(
					'className' => $Model->alias,
					'foreignKey' => $Model->primaryKey,
					'counterCache' => $this->settings[$Model->alias]['counterCache'],
					'conditions' => array('Like.model' => $Model->alias)
				)
			)
		), false);
	}
	
	/**
	 * Like a Model item
	 *
	 * @param int $model_id ID of the model item to like
	 * @param int $user_id ID the user
	 */
	public function like(Model $Model, $foreign_id, $user_id){
		if(!$Model->exists($foreign_id)){
			throw new NotFoundException();
		}
	
		if($Model->isLikedBy($foreign_id, $user_id)){
			throw new CakeException('Already liked');
		}
		
		$Model->Like->create();
		$Model->Like->save(array(
			'Like' => array(
				'Like.model' => $Model->alias,
				'Like.foreign_id' => $foreign_id,
				'Like.user_id' => $user_id
			)	
		));
	}
	
	/**
	 * Dislike a Model item
	 *
	 * @param int $model_id ID of the model item to dislike
	 * @param int $user_id ID the user
	 */
	public function dislike(Model $Model, $foreign_id, $user_id){
		if(!$Model->exists($foreign_id)){
			throw new NotFoundException();
		}
		
		if(!$Model->isLikedBy($foreign_id, $user_id)){
			throw new CakeException('Not liked');
		}
		
		$Model->Like->deleteAll(array(
			'Like.model' => $Model->alias,
			'Like.foreign_id' => $foreign_id,
			'Like.user_id' => $user_id
		));
	}
	
	/**
	 * check if a Model item is liked by a user
	 *
	 * @param int $model_id ID of the model item
	 * @param int $user_id ID the user
	 * @return boolean True if the user like the item, false otherwise
	 */
	public function isLikedBy(Model $Model, $foreign_id, $user_id){
		if(!$Model->exists($foreign_id)){
			throw new NotFoundException();
		}
		
		$count = $Model->Like->find('count', array(
			'conditions' => array(
				'Like.foreign_id' => $foreign_id,
				'Like.user_id' => $user_id
			)
		));

		return $count == 1;
	}
	
	/**
	 * Custom find query to get the liked Model item
	 *
	 * @example $this->Model->find('liked', array('limit'=>5));
	 */
	protected function _findLiked($state, $query, $results = array()){
		if ($state == 'before') {
	
			return $query;
		}
		return $results;
	}
	
	/**
	 * Custom find query to get the most liked Model item
	 *
	 * @example $this->Model->find('most_liked', array('limit'=>5));
	 */
	protected function _findMostLiked($state, $query, $results = array()){
		if ($state == 'before') {
			//TODO Order by number of likes
			return $query;
		}
		return $results;
	}
}