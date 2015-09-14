<?php

require_once '../collage.inc.php';

$config = array(
	'collageSize' => array(
		'width'  => 480, //px
		'height' => 480, //px
	),
);

$friends = unserialize(file_get_contents('data/avatars-cache.txt'));
$collage = \TwitterCollage\CreateSimpleCollagePNG($friends, $config['collageSize']['width'], $config['collageSize']['height']);

header('Content-Type: image/png');
imagepng($collage);

imagedestroy($collage);
