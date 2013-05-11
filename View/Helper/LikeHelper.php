<?php

class LikeHelper extends AppHelper{
	
	public $helpers = array(
		'Html', 'Text'
	);
	
	public function like($model, $foreign_id, $option = array()){
		return $this->Html->link(__d('like', 'Like'), array(
			'plugin' => 'like',
			'controller' => 'likes',
			'action' => 'like',
			$model,
			$foreign_id
		), $option);
	}
	
	
	public function dislike($model, $foreign_id, $option = array()){
		return $this->Html->link(__d('like', 'Dislike'), array(
			'plugin' => 'like',
			'controller' => 'likes',
			'action' => 'dislike',
			$model,
			$foreign_id
		), $option);
	}

	public function display($likes, $userKey = 'username'){

	    $n = count($likes);

	    if($n > 0){
	        if($n == 1){
	            $like = $likes[0];
	            $text = $this->Html->link(
	                $like['User'][$userKey], 
	                array('controller'=>'users', 'action'=>'view', $like['User']['id'])
	            );
	        }else if($n > 1){
	            $users = array();
	            foreach ($likes as $like) {
	                $users[] = $like['User'][$userKey];
	            }

	            $text = $this->Html->link(
	                __n('%s people', '%s people', $n, $n), 
	                '#', 
	                array('title'=>$this->Text->toList($users, __('and')))
	            );

	        }
	        echo __dn(
	        	'like',
	            '%s liked this.',
	            '%s liked this.',
	            $n,
	            $text
	        );
	    }
	}
	
}