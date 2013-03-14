<?php

App::uses('AuthComponent', 'Controller/Component');
App::uses('NotLikedException', 'Like.Exception');
App::uses('AlreadyLikedException', 'Like.Exception');

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
	
	public function setup(Model $Model, $settings = array()) {
		if (!isset($this->settings[$Model->alias])) {
			$this->settings[$Model->alias] = $this->defaultSettings;
		}
		
		$this->settings[$Model->alias] = array_merge(
			$this->settings[$Model->alias], 
			(array)$settings
		);
		
		$Model->findMethods['liked'] = true;
		$Model->findMethods['mostLiked'] = true;
		
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
	
	
	public function afterFind(Model $Model, $results, $primary){
		foreach($results as $k => $result){
			if(!empty($result['Like']) && AuthComponent::user('id')){
				$results[$k][$Model->alias]['is_liked_by_current_user'] = in_array(AuthComponent::user('id'),Set::classicExtract($result['Like'], '{n}.user_id'));
			}	
		}
		return $results;
	}

	/**
	 * afterDelete callback
	 */
	public function afterDelete(Model $Model){
		$likesToDelete = $Model->Like->find('list', array(
			'conditions' => array(
				'model' => $Model->alias,
				'foreign_id' => $Model->id
			)
		));

		if($likesToDelete != false){
			$likesId = array_keys($likesToDelete);
			$Model->Like->deleteAll(array('Like.id'=>$likesId));
			$Model->getEventManager()->dispatch(new CakeEvent('Plugin.Like.delete', $Model->Like, $likesId));
		}
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
			throw new AlreadyLikedException();
		}
		
		$Model->Like->create();
		$Model->Like->save(array(
			'Like' => array(
				'model' => $Model->alias,
				'foreign_id' => $foreign_id,
				'user_id' => $user_id
			)	
		));
		
		$Model->getEventManager()->dispatch(new CakeEvent('Plugin.Like.like', $Model->Like));
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
			throw new NotLikedException();
		}
		
		$like = $Model->Like->find('first', array('conditions' => array(
			'Like.model' => $Model->alias,
			'Like.foreign_id' => $foreign_id,
			'Like.user_id' => $user_id
		)));
		
		$Model->Like->delete($like['Like']['id']);
		
		$Model->getEventManager()->dispatch(new CakeEvent('Plugin.Like.dislike', $like));
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
	public function _findLiked(Model $Model, $method, $state, $query, $results = array()){
		if ($state == 'before') {
			$query['conditions'][$Model->alias.'.like_count >'] = 0;
			return $query;
		}
		return $results;
	}
	
	/**
	 * Custom find query to get the most liked Model item
	 *
	 * @example $this->Post->find('most_liked', array('limit'=>5));
	 */
	public function _findMostLiked(Model $Model, $method, $state, $query, $results = array()){
		if ($state == 'before') {
			$query['order'][$Model->alias.'.like_count '] = 'DESC';
			return $query;
		}
		return $results;
	}
	
	/**
	 * Find item liked by a specific user
	 * 
	 * @example $this->Post->findLikedBy($this->Auth->user('id'));
	 * @param Model $Model
	 * @param int $user_id ID of the user
	 */
	public function findLikedBy(Model $Model, $user_id){
		$Model->Like->recursive = -1;
		$likedItem = $Model->Like->find('all', array(
			'fields' => array('Like.foreign_id'),
			'conditions' => array(
				'Like.model' => $Model->alias,
				'Like.user_id' => $user_id
			)
		));
		
		if(empty($likedItem)){
			return array();
		}
			
		$likedItemIds = Set::classicExtract($likedItem, '{n}.Like.foreign_id');
		return $Model->findAllById($likedItemIds);
	}
}