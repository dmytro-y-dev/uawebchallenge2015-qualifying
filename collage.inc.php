<?php namespace TwitterCollage;

/*
 * Get id, URI of avatar and count of published tweets of all (or, if $maxUsersCount
 * is not 0, at most $maxUsersCount) users who are followed by user with name = $userName.
 *
 * @param twitterAPI configured instance of TwitterAPIExchange class.
 * @param userName name of friends list owner.
 * @param maxUsersCount maximum count of users to return (if 0 or negative value is specified, then all).
 *
 * @return array of users (each user is represented as array with keys {id, avatarURI, tweetsCount}).
 */
function GetFriendsList($twitterAPI, $userName, $maxUsersCount = 0)
{
	$plainArrayFriends = array(); // Final result is stored in this variable
	
	$usersPerPage = 200; // 200 is maximum count of data items per request on Twitter API
	$maxPagesCount = (int)ceil($maxUsersCount / $usersPerPage);
	
	$currentPage = 0;
	$currentPageCursor = -1;
	
	while ($maxUsersCount == 0 || $currentPage !== $maxPagesCount) {
		// Get list of friends from Twitter API
		
		$friendsPageURI = "https://api.twitter.com/1.1/friends/list.json";
	
		$friendsJSON = $twitterAPI
			->setGetfield("?screen_name={$userName}&skip_status=1&cursor={$currentPageCursor}&count={$usersPerPage}")
			->buildOauth($friendsPageURI, 'GET')
			->performRequest();
			
		$friends = json_decode($friendsJSON);
		
		if (!empty($friends->errors)) {
			throw new \Exception("Twitter API Error! Code {$friends->errors[0]->code}: {$friends->errors[0]->message}.");
		}
		
		if (empty($friends->users)) {
			break;
		}
		
		// Convert list of users into plain array
		
		foreach ($friends->users as $friend) {
			$plainArrayFriends[] = array(
				'name' => $friend->screen_name,
				'avatarURI' => $friend->profile_image_url,
				'tweetsCount' => $friend->statuses_count
			);
		}
		
		++$currentPage;
		$currentPageCursor = $friends->next_cursor_str;
	}
	
	// Slice array if it exceeds maximum users count
			
	if ($maxUsersCount !== 0 && count($plainArrayFriends) > $maxUsersCount) {
		$plainArrayFriends = array_slice($plainArrayFriends, 0, $maxUsersCount);
  }
	
	return $plainArrayFriends;
}

/*
 * Calculate proportions of avatars.
 *
 * @param users array of users (format of data must be the same as in GetFriendsList function).
 *
 * @return associative array in the format "user id => relative avatar size in the range 0.0..1.0".
 */
function GetAvatarsProportions($users)
{
	$overallTweetsCount = 0;
	
	foreach ($users as $user) {
		$overallTweetsCount += $user['tweetsCount'];
	}
	
	$avatarsSizes = array();
	
	foreach ($users as $user) {
		$avatarsSizes[$user['name']] = $user['tweetsCount'] / $overallTweetsCount;
	}
	
	return $avatarsSizes;
}

/*
 * Create .PNG file with collage (each avatar size is the same).
 *
 * @param users array of users in GetFriendsList function result format.
 * @param width desired collage width in px.
 * @param height desired collage height in px.
 *
 * @return GD PNG image resource if successful, or FALSE if function failed.
 */
function CreateSimpleCollagePNG($users, $width, $height)
{
  if (empty($users)) {
  	return FALSE;
  }
  
  $twitterAvatarWidth = 48; // 48px is default width for avatar on Twitter
  $twitterAvatarHeight = 48; // 48px is default height for avatar on Twitter
  $avatarsPerRow = (int)floor($width / $twitterAvatarWidth);
  $maximumRows = (int)floor($height / $twitterAvatarHeight);
  $placedAvatarsCount = 0;
  
  $collage = imagecreatetruecolor($width, $height);
  
  foreach ($users as $user) {
  	$currentCol = (int)($placedAvatarsCount % $avatarsPerRow);
  	$currentRow = (int)($placedAvatarsCount / $avatarsPerRow);
  	
  	if ($currentRow == $maximumRows) {
  		break;
  	}
  	
  	$avatarRawContent = file_get_contents($user['avatarURI']);
  	$avatar = @imagecreatefromstring($avatarRawContent);
  	
  	if ($avatar === FALSE) {
  		continue;
  	}
  	
  	imagecopy($collage,
  		$avatar,
  		$currentCol * $twitterAvatarWidth,
  		$currentRow * $twitterAvatarHeight,
  		0, 0,
  		$twitterAvatarWidth, $twitterAvatarHeight
  	);
  	
  	imagedestroy($avatar);
  	
  	++$placedAvatarsCount;
  }
  
	return $collage;
}

/*
 * Create .PNG file with collage (each avatar's size is proportionate to others: proportion is specified in $avatarsProportions).
 *
 * @param users array of users in GetFriendsList function result format.
 * @param avatarsProportions array of avatars proportions (in a format of GetAvatarsProportions function result).
 * @param width desired collage width in px.
 * @param height desired collage height in px.
 *
 * @return GD PNG image resource if successful, or FALSE if function failed.
 */
function CreateProportionateCollagePNG($users, $avatarsProportions, $width, $height)
{
  if (empty($users) || empty($avatarsProportions)) {
  	return FALSE;
  }
  
  $avatars = array();
  $collageDesiredSize = min($width, $height);
  $collageRealSize = 0; // The real size of picture (after avatars scaling)
  
  // Calculate avatars sizes for scaling function and sort them from larger to smaller
  // NB! Avatars are always square! So size is just length of edge.
  
  foreach ($users as $user) {
  	$currentAvatar = array(
  		'user' => $user,
			'size' => (int)ceil(sqrt($avatarsProportions[$user['name']]) * $collageDesiredSize)
  	);
  
		$isInserted = FALSE;
		
		for ($i = 0, $iend = count($avatars); $i != $iend; ++$i) {
			if ($avatars[$i]['size'] < $currentAvatar['size']) {
			  array_splice($avatars, $i, 0, array($currentAvatar));
			  $isInserted = TRUE;
			  break;
		  }
		}
		
		if (!$isInserted) {
			$avatars[] = $currentAvatar;
		}
  	
  	$collageRealSize += $currentAvatar['size'] * $currentAvatar['size'] * 1.5;
  }

  $collageRealSize = (int)ceil(sqrt($collageRealSize));
  
  $rowsUsedPixelsCount = array(); // Array which indicates how many pixels in i-th row are already used
  for ($i = 0, $iend = $collageRealSize; $i < $iend; ++$i) {
  	$rowsUsedPixelsCount[$i] = 0;
  }
  
  // Draw collage
  
  $collage = imagecreatetruecolor($collageRealSize, $collageRealSize);

  foreach ($avatars as $avatar) {
  	$avatarRawContent = file_get_contents($avatar['user']['avatarURI']);
  	$avatarImageNotScaled = @imagecreatefromstring($avatarRawContent);
  	
  	if ($avatarImageNotScaled === FALSE) {
  		continue;
  	}
  	
  	$avatarImage = imagecreatetruecolor($avatar['size'], $avatar['size']);
  	imagecopyresampled($avatarImage,
	  	$avatarImageNotScaled,
	  	0, 0,
	  	0, 0,
	  	$avatar['size'], $avatar['size'],
	  	imagesx($avatarImageNotScaled), imagesy($avatarImageNotScaled)
  	);
  	
  	// Find row where to insert next avatar
  
    $rowToInsertAvatar = 0;
    
  	for ($i = 1, $iend = $collageRealSize - $avatar['size']; $i != $iend; ++$i) {
  	  if ($rowsUsedPixelsCount[$rowToInsertAvatar] > $rowsUsedPixelsCount[$i]) {
  	  	$changeNextAvatarRow = true;
  	  	
  	  	for ($j = $i + 1, $jend = $i + $avatar['size']; $j != $jend; ++$j) {
  	  		if ($rowsUsedPixelsCount[$i] < $rowsUsedPixelsCount[$j]) {
  	  			$changeNextAvatarRow = false;
  	  			break;
  	  		}
  	  	}
  	  	
  	  	if ($changeNextAvatarRow) {
	  	  	$rowToInsertAvatar = $i;
	  	  }
  	  }
  	}

  	if ($rowToInsertAvatar == $collageRealSize) {
  	  // Unable to place next avatar. Just skip.
  	  
  		break;
  	}
  	
  	imagecopy($collage,
  		$avatarImage,
  		$rowsUsedPixelsCount[$rowToInsertAvatar],
  		$rowToInsertAvatar,
  		0, 0,
  		$avatar['size'], $avatar['size']
  	);
  	
  	imagedestroy($avatarImage);
  	
  	// Mark pixels as used
  	
  	for ($i = $rowToInsertAvatar + $avatar['size'], $iend = $rowToInsertAvatar; $i != $iend; --$i) {
  		$rowsUsedPixelsCount[$i - 1] = $rowsUsedPixelsCount[$rowToInsertAvatar] + $avatar['size'];
  	}
  }

  // Scale collage to fit specified by user size
  
  $collageOfDesiredSize = imagecreatetruecolor($width, $height);
  imagecopyresampled($collageOfDesiredSize,
  	$collage,
  	0, 0,
  	0, 0,
  	$collageDesiredSize, $collageDesiredSize,
  	$collageRealSize, $collageRealSize
  );
  
	return $collageOfDesiredSize;
}
