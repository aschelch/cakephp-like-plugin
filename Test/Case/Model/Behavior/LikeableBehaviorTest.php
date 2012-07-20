<?php

require_once dirname(__FILE__) . DS . 'models.php';
App::uses('LikeableBehavior', 'Like.Model/Behavior');
App::load('LikeableBehavior');

class LikeableBehaviorTest extends CakeTestCase{
	
	public $fixtures = array(
		'plugin.like.like',
		'plugin.like.post',
		'plugin.like.user'
	);
	
	/**
	 * Method executed before each test
	 *
	 */
	public function setUp() {
		parent::setUp();
		$this->User = ClassRegistry::init('User');
		$this->Post = ClassRegistry::init('Post');
		
		$this->User->bindModel(array(
			'hasMany' => array('Post')
		), false);
		
		$this->Post->bindModel(array(
			'belongsTo' => array('User')
		), false);
		
		$this->Post->Behaviors->attach('Likeable');
	}
	
	/**
	 * Method executed after each test
	 *
	 */
	public function tearDown() {
		unset($this->Post);
		unset($this->User);
		parent::tearDown();
	}
	
	public function testIsLikedByWithNotExistingPost(){
		$this->expectException('NotFoundException');
		$user_id = 1;
		$post_id = 99;
		$this->Post->isLikedBy($post_id, $user_id);
	}
	
	public function testIsLikedBy(){
		$post_id = 1;
		$this->assertEquals($this->Post->isLikedBy($post_id, 1), true);		
		$this->assertEquals($this->Post->isLikedBy($post_id, 2), false);
	}
	
	public function testLikeWithNotExistingPost(){
		$this->expectException('NotFoundException');
		$user_id = 1;
		$post_id = 99;
		$this->Post->like($post_id, $user_id);
	}
	
	public function testLikeWithAlreadyLikedPost(){
		$this->expectException('CakeException');
		$user_id = 1;
		$post_id = 1;
		$this->Post->like($post_id, $user_id);
	}
	
	public function testLike(){
		$user_id = 2;
		$post_id = 1;
		$this->assertEquals($this->Post->Like->find('count'), 1);
		$this->Post->like($post_id, $user_id);
		$this->assertEquals($this->Post->Like->find('count'), 2);
	}
	
	public function testDislikeWithNotExistingPost(){
		$this->expectException('NotFoundException');
		$user_id = 1;
		$post_id = 99;
		$this->Post->dislike($post_id, $user_id);
	}
	
	public function testDislikeWithNotLikedPost(){
		$this->expectException('CakeException');
		$user_id = 1;
		$post_id = 2;
		$this->Post->dislike($post_id, $user_id);
	}
	
	public function testDislike(){
		$user_id = 1;
		$post_id = 1;
		$this->assertEquals($this->Post->Like->find('count'), 1);
		$this->Post->dislike($post_id, $user_id);
		$this->assertEquals($this->Post->Like->find('count'), 0);
	}
	
	public function testFindLikedPost(){
		//TODO Test find('liked)
	}
	
	public function testFindMostLikedPost(){
		//TODO Test find('most_liked)
	}
	
}