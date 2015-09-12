<?php

/*
 * Get id, URI of avatar and count of tweets on the wall of all (or, if $maxUsersCount
 * is specified, at most $maxUsersCount) users whose tweets are preset on the wall of
 * user with id = $userId.
 *
 * @param userId twitter id of wall owner (where to search other users)
 * @param maxUsersCount maximum count of users to return (if 0 is specified, then all)
 *
 * @return array of users
 */
function GetUsersFromTwitterWall($userId, $maxUsersCount = 0)
{
	return array(
		array(
			'id' => 'test1',
			'avatarURI' => 'https://pbs.twimg.com/profile_images/378800000379381538/89d4049297491dd4c063cf071da47404_normal.png',
			'tweetsOnPage' => 10
		),
		array(
			'id' => 'test2',
			'avatarURI' => 'https://pbs.twimg.com/profile_images/489888254247067649/Zr3AOS1Y_normal.jpeg',
			'tweetsOnPage' => 4
		),
	);
}
