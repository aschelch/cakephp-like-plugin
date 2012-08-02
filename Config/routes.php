<?php

Router::connect('/like/*', array('plugin' => 'like', 'controller' => 'likes', 'action' => 'like'));
Router::connect('/dislike/*', array('plugin' => 'like', 'controller' => 'likes', 'action' => 'dislike'));