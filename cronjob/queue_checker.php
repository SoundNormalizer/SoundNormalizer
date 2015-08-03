<?php
// Start: Settings
$ytdlBin = "C:\\xampp\htdocs\youtube2mp3\cronjob\bin\youtube-dl.exe";
$ffmpegDir = "C:\\xampp\htdocs\youtube2mp3\cronjob\bin\\";
// End: Settings

set_time_limit(0);
require dirname(__FILE__) . "/../settings.php";

$outputDir = realpath(dirname(__FILE__) . "/../converted/");
$db = new PDO("mysql:host=" . $dbHost . ";dbname=" . $dbName . ";charset=utf8", $dbUser, $dbPass);

$queueQuery = $db->prepare("SELECT `ID`,`VideoID` FROM `conversions` WHERE `Started`=FALSE");
$queueQuery->execute();
$unqueuedArr = $queueQuery->fetchAll();

foreach ($unqueuedArr as $unqueued) {
	$reqID = $unqueued["ID"];
	$reqVideoID = $unqueued["VideoID"];
	
	$updateQueue = $db->prepare("UPDATE `conversions` SET `Started`=TRUE, `TimeStarted`=UNIX_TIMESTAMP() WHERE `ID`=:id");
	$updateQueue->execute(array(":id" => $reqID));
	
	$cmd = shell_exec($ytdlBin . " --extract-audio --prefer-ffmpeg --ffmpeg-location " . $ffmpegDir . " --audio-quality 128K --audio-format mp3 -o \"" . $outputDir . "/%(id)s.%(ext)s\" --sleep-interval 1 -- " . escapeshellarg($reqVideoID));
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
	
	$updateStatus = $db->prepare("UPDATE `conversions` SET `StatusCode`=:code, `Completed`=TRUE, `TimeCompleted`=UNIX_TIMESTAMP() WHERE `ID`=:id");
	$updateStatus->execute(array(	
		":code" => $statusCode,
		":id" => $reqID
	));
}
?>