<?php
// Create a default profile image
$width = 200;
$height = 200;
$image = imagecreatetruecolor($width, $height);

// Colors
$bgColor = imagecolorallocate($image, 240, 240, 240);  // Light gray
$textColor = imagecolorallocate($image, 255, 191, 0);  // #FFBF00 yellow

// Fill the background
imagefilledrectangle($image, 0, 0, $width, $height, $bgColor);

// Draw a circle in the center
$centerX = $width / 2;
$centerY = $height / 2;
$radius = $width * 0.4;
imagefilledellipse($image, $centerX, $centerY, $radius * 2, $radius * 2, $textColor);

// Draw a person silhouette
$headRadius = $radius * 0.6;
$bodyWidth = $radius * 1.2;
$bodyHeight = $radius * 1.5;
imagefilledellipse($image, $centerX, $centerY - $radius * 0.3, $headRadius, $headRadius, $bgColor);
imagefilledrectangle($image, $centerX - $bodyWidth/2, $centerY, $centerX + $bodyWidth/2, $centerY + $bodyHeight, $bgColor);

// Save the image
$outputFile = 'images/default.jpg';
imagejpeg($image, $outputFile, 90);
imagedestroy($image);

echo "Default profile image created at $outputFile";
?> 