<?php

class LikeHelper extends AppHelper{
	
	public $helpers = array(
		'Html'
	);
	
	public function like($model, $foreign_id){
		return $this->Html->link(__d('like', 'Like'), array(
			'plugin' => 'like',
			'controller' => 'likes',
			'action' => 'like',
			$model,
			$foreign_id
		));
	}
	
	
	public function dislike($model, $foreign_id){
		return $this->Html->link(__d('like', 'Dislike'), array(
			'plugin' => 'like',
			'controller' => 'likes',
			'action' => 'dislike',
			$model,
			$foreign_id
		));
	}
	
}