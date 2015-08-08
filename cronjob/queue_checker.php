<?php
// Start: Settings
$ytdlBin = "/usr/local/bin/youtube-dl";
$mp3gainBin = "/usr/bin/mp3gain";
$ffmpegDir = "/usr/bin";
// End: Settings

set_time_limit(0);
require dirname(__FILE__) . "/../settings.php";

$outputDir = realpath(dirname(__FILE__) . "/../converted/");
$db = new PDO("mysql:host=" . $dbHost . ";dbname=" . $dbName . ";charset=utf8", $dbUser, $dbPass);

$queueQuery = $db->prepare("SELECT `ID`,`Type`,`LocalName`,`YouTubeID`,`Normalized` FROM `conversions` WHERE (`DuplicateOf` = '0' AND `Started` = FALSE)");
$queueQuery->execute();
$unqueuedArr = $queueQuery->fetchAll();

foreach ($unqueuedArr as $unqueued) {
	$reqID = $unqueued["ID"];
	$reqType = $unqueued["Type"];
	$reqLocalName = $unqueued["LocalName"];
	$reqVideoID = $unqueued["YouTubeID"];
	$reqNormalize = $unqueued["Normalized"];
	
	$updateQueue = $db->prepare("UPDATE `conversions` SET `Started`=TRUE, `TimeStarted`=UNIX_TIMESTAMP() WHERE `ID`=:id");
	$updateQueue->execute(array(":id" => $reqID));
	
	$statusCode = 0;	
	$cmd = shell_exec($ytdlBin . " --extract-audio --prefer-ffmpeg --ffmpeg-location " . $ffmpegDir . " --audio-quality 128K --audio-format mp3 -o \"" . $outputDir . "/" . $reqLocalName . ".%(ext)s\" --add-metadata --sleep-interval 1 -- " . escapeshellarg($reqVideoID));
	if (strpos($cmd, "YouTube said: This video does not exist") !== false) {
		// Video is non-existent
		$statusCode = 1;
	}
	elseif (strpos($cmd, "Unable to download webpage") !== false) {
		// Couldn't fetch web page (bad video URL)
		$statusCode = 2;
	}
	elseif (strpos($cmd, "Deleting original file") !== false) {
		// Success
		$statusCode = 3;
	}
	else {
		// Unknown Error
		$statusCode = 4;
	}
	
	// Check if we should normalize it
	if ((($reqNormalize == 1) && ($statusCode == 3)) || ($reqType == "upload")) {
		$outputFile = $outputDir . "/" . preg_replace('((^\.)|\/|(\.$))', '', $reqLocalName) . ".mp3";

		$normalizeValueCmd = shell_exec($mp3gainBin . " -o " . escapeshellarg($outputFile));
		$normalizeValueResult = explode(PHP_EOL, trim($normalizeValueCmd));
		$normalizeValue = explode("\t", $normalizeValueResult[1])[1];

		$normalizeCmd = shell_exec($mp3gainBin . " -g " . $normalizeValue . " " . escapeshellarg($outputFile));
		
		if (strpos($normalizeCmd, "Can't open") !== false) {
			// File doesn't exist
			$statusCode = 5;
		}
		elseif (strpos($normalizeCmd, "Can't find any valid MP3 frames") !== false) {
			// Not a valid mp3 file
			$statusCode = 6;
		}
		else {
			// Success
			$statusCode = 3;
		}
	}
	
	$updateStatus = $db->prepare("UPDATE `conversions` SET `StatusCode`=:code, `Completed`=TRUE, `TimeCompleted`=UNIX_TIMESTAMP() WHERE `ID`=:id");
	$updateStatus->execute(array(	
		":code" => $statusCode,
		":id" => $reqID
	));
}
?>