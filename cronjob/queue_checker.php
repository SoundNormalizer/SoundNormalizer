<?php
// Start: Settings
$ytdlBin = "C:\\xampp\htdocs\youtube2mp3\cronjob\bin\youtube-dl.exe";
$ffmpegDir = "C:\\xampp\htdocs\youtube2mp3\cronjob\bin\\";

$outputDir = dirname(__FILE__) . "\..\converted\\"; // include the last slash
// End: Settings

require dirname(__FILE__) . "/../settings.php";
set_time_limit(0);

$db = new PDO("mysql:host=" . $dbHost . ";dbname=" . $dbName . ";charset=utf8", $dbUser, $dbPass);

$queueQuery = $db->prepare("SELECT `ID`,`VideoID` FROM `conversions` WHERE `Queued`=FALSE");
$queueQuery->execute();
$unqueuedArr = $queueQuery->fetchAll();

foreach ($unqueuedArr as $unqueued) {
	$reqID = $unqueued["ID"];
	$reqVideoID = $unqueued["VideoID"];
	
	$updateQueue = $db->prepare("UPDATE `conversions` SET `Queued`=TRUE WHERE `ID`=:id");
	$updateQueue->execute(array(":id" => $reqID));
	
	$cmd = shell_exec($ytdlBin . " --extract-audio --prefer-ffmpeg --ffmpeg-location " . $ffmpegDir . " --audio-quality 128K --audio-format mp3 -o \"" . $outputDir . "%(id)s.%(ext)s\" -- " . escapeshellarg($reqVideoID));
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
	
	$updateStatus = $db->prepare("UPDATE `conversions` SET `StatusCode`=:code, `Completed`=TRUE");
	$updateStatus->execute(array(":code" => $statusCode));
}
?>