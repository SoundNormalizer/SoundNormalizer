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

$queueQuery = $db->prepare("SELECT `ID`,`VideoID`,`Normalized` FROM `conversions` WHERE `Started`=FALSE");
$queueQuery->execute();
$unqueuedArr = $queueQuery->fetchAll();

foreach ($unqueuedArr as $unqueued) {
	$reqID = $unqueued["ID"];
	$reqVideoID = $unqueued["VideoID"];
	$reqNormalize = $unqueued["Normalized"];
	
	$updateQueue = $db->prepare("UPDATE `conversions` SET `Started`=TRUE, `TimeStarted`=UNIX_TIMESTAMP() WHERE `ID`=:id");
	$updateQueue->execute(array(":id" => $reqID));
	
	$cmd = shell_exec($ytdlBin . " --extract-audio --prefer-ffmpeg --ffmpeg-location " . $ffmpegDir . " --audio-quality 128K --audio-format mp3 -o \"" . $outputDir . "/%(id)s.%(ext)s\" --add-metadata --sleep-interval 1 -- " . escapeshellarg($reqVideoID));
	if (strpos($cmd, "YouTube said: This video does not exist") !== false) {
		// Video is non-existent
		$statusCode = 1;
	}
	elseif (strpos($cmd, "Unable to download webpage") !== false) {
		// Couldn't fetch web page (bad video URL)
		$statusCode = 2;
	}
	elseif (strpos($cmd, "Deleting original file") !== false) {
		// Success, now check if we should normalize it
		$statusCode = 3;
		
		if ($reqNormalize) {
			$outputFile = $outputDir . "/" . preg_replace('((^\.)|\/|(\.$))', '', $reqVideoID);
			$normalizeCmd = shell_exec($mp3gainBin . " " . escapeshellarg($outputFile));
		}
	}
	else {
		// Unknown Error
		$statusCode = 4;
	}
	
	$updateStatus = $db->prepare("UPDATE `conversions` SET `StatusCode`=:code, `Completed`=TRUE, `TimeCompleted`=UNIX_TIMESTAMP() WHERE `ID`=:id");
	$updateStatus->execute(array(	
		":code" => $statusCode,
		":id" => $reqID
	));
}
?>