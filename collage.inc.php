<?php

/*
 * Get id, URI of avatar and count of tweets on the wall of all (or, if $maxUsersCount
 * is not 0, at most $maxUsersCount) users whose tweets are preset on the wall of user with id = $userId.
 *
 * @param userId twitter id of wall owner (where to search other users).
 * @param maxUsersCount maximum count of users to return (if 0 or negative value is specified, then all).
 *
 * @return array of users (each user is represented as array with keys {id, avatarURI, tweetsOnWall}).
 */
function GetUsersFromTwitterWall($userId, $maxUsersCount = 0)
{
	return array(
		array(
			'id' => 'test1',
			'avatarURI' => 'https://pbs.twimg.com/profile_images/378800000379381538/89d4049297491dd4c063cf071da47404_normal.png',
			'tweetsOnWall' => 10
		),
		array(
			'id' => 'test2',
			'avatarURI' => 'https://pbs.twimg.com/profile_images/489888254247067649/Zr3AOS1Y_normal.jpeg',
			'tweetsOnWall' => 4
		),
	);
}

/*
 * Calculate sizes of avatars.
 *
 * @param users array of users (format of data must be the same as in GetUsersFromTwitterWall function).
 *
 * @return associative array in the format "user id => relative avatar size in percents".
 */
function GetUsersAvatarSizes($users)
{
	$overallTweetsCount = 0;
	
	foreach ($users as $user) {
		$overallTweetsCount += $user['tweetsOnWall'];
	}
	
	$avatarSizes = array();
	
	foreach ($users as $user) {
		$avatarSizes[$user['id']] = $user['tweetsOnWall'] * 100.0 / $overallTweetsCount;
	}
	
	return $avatarSizes;
}
