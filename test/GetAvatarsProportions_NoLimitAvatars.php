<?php

require_once '../config.inc.php';
require_once '../collage.inc.php';
require_once '../vendor/twitter-api-php-master/TwitterAPIExchange.php';

// NB! User information is removed due to requirements of UA Web Challenge.

$config = array_merge($config, array(
	'userName' => 'PavloKlimkin',
	'avatarsMaximumCount' => 0, // if 0, then all from the wall
));

$twitterAPI = new TwitterAPIExchange($config['twitterAPISettings']);
$friends = \TwitterCollage\GetFriendsList($twitterAPI, $config['userName'], $config['avatarsMaximumCount']);

var_dump($friends);
