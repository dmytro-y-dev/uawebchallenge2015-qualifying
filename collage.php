<?php

// HOW TO USE collage.php service
//
// URL GET parameters (* marks required parameters):
//  * login - twitter username from whom get friends' avatars.
//  * width - collage's width.
//  * height - collage's height.
//    proportionate - if t, true or 1, then make collage with avatars which are proportionate to its owner's tweets count.
//    avatars_count - maximum avatars count on collage. if it is 0, then no limit.
//
// Limitations:
// 1. Width and height of collage must be no more than 1000 (due to extensive usage of memory by PHP GD when image has large size).
// 2. No more than 15 request per 15 minutes window due to Twitter API limitations.
//
// Usage example:
// collage.php?login=<your-login>&avatars_count=10&width=400&height=400&proportionate=true
//

require_once 'config.inc.php';
require_once 'collage.inc.php';
require_once 'cache.inc.php';

require_once 'vendor/twitter-api-php-master/TwitterAPIExchange.php';

// Get parameters from URL address and apply constraints.
// If parameters are satisfied, then create collage.

if (!isset($_GET['login'])) {
	exit("Error: Tweeter's user login is required. Include `login` parameter in URL.");
}

$config['userName'] = htmlspecialchars($_GET['login']);

if (!isset($_GET['width'])) {
	exit("Error: Collage's width is required. Include `width` parameter in URL.");
}

if ($_GET['width'] > 1000) {
	exit("Error: Collage's width must be lesser than 1000.");
}

if ($_GET['width'] <= 0) {
	exit("Error: Collage's width must be greater than 0.");
}

if (!isset($_GET['height'])) {
	exit("Error: Collage's height is required. Include `height` parameter in URL.");
}

if ($_GET['height'] > 1000) {
	exit("Error: Collage's height must be lesser than 1000.");
}

if ($_GET['height'] <= 0) {
	exit("Error: Collage's height must be greater than 0.");
}

$config['collageSize'] = array(
		'width'  => (int)$_GET['width'],
		'height' => (int)$_GET['height'],
);

if (isset($_GET['proportionate']) && in_array($_GET['proportionate'], array('t', 'true', '1'))) {
	$config['avatarsSizeReflectsTweetsCount'] = true;
} else {
	$config['avatarsSizeReflectsTweetsCount'] = false;
}

if (isset($_GET['avatars_count']) && $_GET['avatars_count'] > 0) {
	$config['avatarsMaximumCount'] = (int)$_GET['avatars_count'];
} else {
	$config['avatarsMaximumCount'] = 0;
}

// Do real job on creation of collage

$collageURI = GetImageFromCache($config['userName'], $config['collageSize']['width'], $config['collageSize']['height'], $config['avatarsSizeReflectsTweetsCount'], $config['avatarsMaximumCount']);

if ($collageURI === FALSE) {
	$twitterAPI = new TwitterAPIExchange($config['twitterAPISettings']);
	
	try {
		$friends = \TwitterCollage\GetFriendsList($twitterAPI, $config['userName'], $config['avatarsMaximumCount']);
	} catch (\Exception $e) {
		exit($e->getMessage());
	}
	
	if ($config['avatarsSizeReflectsTweetsCount']) {
		$avatarsProportions = \TwitterCollage\GetAvatarsProportions($friends);
		
		$collage = \TwitterCollage\CreateProportionateCollagePNG($friends, $avatarsProportions, $config['collageSize']['width'], $config['collageSize']['height']);
	} else {
		$collage = \TwitterCollage\CreateSimpleCollagePNG($friends, $config['collageSize']['width'], $config['collageSize']['height']);
	}
	
	if ($collage) {
		$collageURI = AddImageToCache($collage, $config['userName'], $config['avatarsSizeReflectsTweetsCount'], $config['avatarsSizeReflectsTweetsCount'], $config['avatarsMaximumCount']);
	}
} else {
	$collage = @imagecreatefrompng($collageURI);
}

// Return collage to browser

if ($collage) {
	header('Content-Type: image/png');
	imagepng($collage);
	
	imagedestroy($collage);
} else {
	exit("Fatal error: Unable to build collage.");
}
