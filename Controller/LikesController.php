<?php

class LikesController extends AppController{
	
	public function like($model, $foreing_id){
		
		$Model = ClassRegistry::init($model);
		$referer = $this->referer('/');
		$user_id = $this->Auth->user('id'); 
		
		if(!$user_id){
			$this->Session->setFlash(__('You are not connected.'));
			return $this->redirect($referer);
		}
		
		if(!$Model->Behaviors->attached('Likeable')){
			$this->Session->setFlash(__('You cannot like this element.'));
			return $this->redirect($referer);
		}
		
		if(!$Model->exists($foreing_id)){
			$this->Session->setFlash(__('This element does not exist.'));
			return $this->redirect($referer);
		}
		
		$Model->like($foreing_id, $user_id);
		return $this->redirect($referer);
	}
	
}