<?php

Router::connect('/like/*', array('plugin' => 'like', 'controller' => 'likes', 'action' => 'like'));