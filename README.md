cakephp-like-plugin
===================

CakePHP Like Plugin

Installation
==================

Download the plugin

     git submodule add git://github.com/aschelch/cakephp-like-plugin.git app/Plugin/Like
     
Create the like table using

    cake Like.install

Attach the Likeable behavior to the model

    public Post extends AppModel{
    	$actsAs = array('Like.Likeable');
    }
    
In your controller, you can use functions :

    $this->Post->like($post_id, $this->Auth->user('id'));
    
    $this->Post->dislike($post_id, $this->Auth->user('id'));
    
    $this->Post->findLikedBy($this->Auth->user('id'));
    
    if($this->Post->isLikedBy($post_id, $this->Auth->user('id'))){...}
    
    $this->Post->find('most_liked', array('limit'=>5));

Or use the Like controller and Like helper

	public PostController extends AppController{
		public $helpers = array('Like.Like');
	}
	
In your view:

	$this->Like->like('post', $post_id);
	$this->Like->dislike('post', $post_id);