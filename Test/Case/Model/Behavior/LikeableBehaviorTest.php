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
		$this->expectException('AlreadyLikedException');
		$user_id = 1;
		$post_id = 1;
		$this->Post->like($post_id, $user_id);
	}
	
	public function testLike(){
		$user_id = 2;
		$post_id = 1;
		$this->assertEquals($this->Post->Like->find('count'), 2);
		$this->Post->like($post_id, $user_id);
		$this->assertEquals($this->Post->Like->find('count'), 3);
	}
	
	public function testDislikeWithNotExistingPost(){
		$this->expectException('NotFoundException');
		$user_id = 1;
		$post_id = 99;
		$this->Post->dislike($post_id, $user_id);
	}
	
	public function testDislikeWithNotLikedPost(){
		$this->expectException('NotLikedException');
		$user_id = 1;
		$post_id = 2;
		$this->Post->dislike($post_id, $user_id);
	}
	
	public function testDislike(){
		$user_id = 1;
		$post_id = 1;
		$this->assertEquals($this->Post->Like->find('count'), 2);
		$this->Post->dislike($post_id, $user_id);
		$this->assertEquals($this->Post->Like->find('count'), 1);
	}
	
	public function testCounterCache(){
		$post_id = 1;
		$user_id = 2;
		$this->assertEquals(intval($this->Post->field('like_count', array('Post.id'=>$post_id))), 1);
		$this->Post->like($post_id, $user_id);
		$this->assertEquals(intval($this->Post->field('like_count', array('Post.id'=>$post_id))), 2);
		$this->Post->dislike($post_id, $user_id);
		$this->assertEquals(intval($this->Post->field('like_count', array('Post.id'=>$post_id))), 1);
	}
	
	public function testFindLikedPost(){
		$this->assertEquals(count($this->Post->find('liked')), 1);
		$this->Post->like(2, 2);
		$this->assertEquals(count($this->Post->find('liked')), 2);
		$this->Post->dislike(2, 2);
		$this->Post->dislike(1, 1);
		$this->assertEquals(count($this->Post->find('liked')), 0);
	}
	
	public function testFindMostLikedPost(){
		$this->Post->like(2, 2);
		$this->Post->like(1, 2);
		
		$result = $this->Post->find('mostLiked');
		
		$this->assertEquals(count($result), 2);
		$this->assertEquals($result[0]['Post']['id'], 1);
		$this->assertEquals($result[1]['Post']['id'], 2);
	}
	
	public function testFindLikedPostByUser(){
		$user_id = 1;
		$result = $this->Post->findLikedBy($user_id);
		$this->assertEquals(count($result), 1);
		$this->assertEquals($result[0]['Post']['id'], 1);
	}
	
	public function testFindLikedPostByUserWithoutLikedPost(){
		$user_id = 2;
		$result = $this->Post->findLikedBy($user_id);
		$this->assertEquals(count($result), 0);
	}
	
	public function testFindWithIsLikedByCurrentUserField(){
		$post_id = 1;
		$this->Post->recursive = 0;
		$result = $this->Post->findById($post_id);
		//$this->assertEqual(key_exists('is_liked_by_current_user', $result['Post']), true);
	}

	public function testDeleteLikesByDeletingPost(){
		$this->assertEquals($this->Post->find('count'), 2);
		$this->assertEquals($this->Post->Like->find('count'), 2);

		$this->Post->delete(1);

		$this->assertEquals($this->Post->Like->find('count'), 1);
		$this->assertEquals($this->Post->find('count'), 1);
	}
	
}