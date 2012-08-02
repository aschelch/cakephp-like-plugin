<?php

class LikesController extends AppController{
	
	public function like($model_name, $foreing_id){
		$Model = ClassRegistry::init($model_name);
		$referer = $this->referer('/');
		$user_id = $this->Auth->user('id'); 
		
		if(!$user_id){
			$this->Session->setFlash(__d('like', 'You are not connected.'));
			return $this->redirect($referer);
		}
		
		if(!$Model->Behaviors->attached('Likeable')){
			$this->Session->setFlash(__d('like', 'You cannot like this element.'));
			return $this->redirect($referer);
		}
		
		if(!$Model->exists($foreing_id)){
			$this->Session->setFlash(__d('like', 'This element does not exist.'));
			return $this->redirect($referer);
		}
		
		$Model->like($foreing_id, $user_id);
		return $this->redirect($referer);
	}
	
	public function dislike($model_name, $foreing_id){
		$Model = ClassRegistry::init($model_name);
		$referer = $this->referer('/');
		$user_id = $this->Auth->user('id');
	
		if(!$user_id){
			$this->Session->setFlash(__d('like', 'You are not connected.'));
			return $this->redirect($referer);
		}
	
		if(!$Model->Behaviors->attached('Likeable')){
			$this->Session->setFlash(__d('like', 'You cannot like this element.'));
			return $this->redirect($referer);
		}
	
		if(!$Model->exists($foreing_id)){
			$this->Session->setFlash(__d('like', 'This element does not exist.'));
			return $this->redirect($referer);
		}
	
		$Model->dislike($foreing_id, $user_id);
		return $this->redirect($referer);
	}
	
}