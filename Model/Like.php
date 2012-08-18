<?php
/**
 * Like Model
 *
 */
class Like extends AppModel {
	
	public function __construct( $id = false, $table = NULL, $ds = NULL ){
		$this->belongsTo['User'] = array(
			'className' => Configure::read('Plugin.Like.user.model'),
			'foreignKey'=> 'user_id',
		);
		parent::__construct($id,$table,$ds);
	}
	
}
