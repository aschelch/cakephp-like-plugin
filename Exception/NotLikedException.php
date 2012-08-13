<?php

class NotLikedException extends CakeException {

	public function __construct(){
		parent::__construct(__d('like', 'Already liked'));
	}
    
}