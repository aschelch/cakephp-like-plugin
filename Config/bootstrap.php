<?php

$default = array(
	'user'	 => array(
		'model' => 'User'
	)
);
Configure::write('Plugin.Like', (Configure::read('Plugin.Like') ? Configure::read('Plugin.Like') : array()) + $default);