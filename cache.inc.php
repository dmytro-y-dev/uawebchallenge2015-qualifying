<?php

/*
 * Save GD PNG image to website's cache.
 *
 * @param image GD PNG image resource.
 * @param userName name of user who's friends are on collage.
 * @param proportionate use false if collage is simple, and true if avatars in collage have proportionate size.
 *
 * @return URI of image in cache.
 */
function AddImageToCache($image, $userName, $proportionate, $avatarsMaximumCount)
{
	$width = imagesx($image);
	$height = imagesy($image);
	$proportionateSuffix = $proportionate ? '-proportionate' : '';
	
	$imageURI = "cache/{$userName}-{$width}-{$height}-{$avatarsMaximumCount}{$proportionateSuffix}.png";
	
	imagepng($image, $imageURI);
	
	return $imageURI;
}

/*
 * Get URI of collage's image from website's cache.
 *
 * @param userName name of user who's friends are on collage.
 * @param width width of collage.
 * @param height height of collage.
 * @param proportionate use false if collage is simple, and true if avatars in collage have proportionate size.
 *
 * @return URI of image in cache if successful, or FALSE otherwise.
 */
function GetImageFromCache($userName, $width, $height, $proportionate, $avatarsMaximumCount)
{
	$proportionateSuffix = $proportionate ? '-proportionate' : '';
	
	$imageURI = "cache/{$userName}-{$width}-{$height}-{$avatarsMaximumCount}{$proportionateSuffix}.png";
	
	if (!file_exists($imageURI)) {
		return FALSE;
	}
	
	return $imageURI;
}