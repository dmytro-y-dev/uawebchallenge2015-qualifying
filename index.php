<?php

require_once 'config.inc.php';
require_once 'collage.inc.php';

$users = GetUsersFromTwitterWall($config['userId'], $config['avatarsMaximumCount']);

include 'views/collage-page.phtml';
