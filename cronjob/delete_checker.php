<?php
set_time_limit(0);
require dirname(__FILE__) . "/../settings.php";

$outputDir = realpath(dirname(__FILE__) . "/../converted/");
$db = new PDO("mysql:host=" . $dbHost . ";dbname=" . $dbName . ";charset=utf8", $dbUser, $dbPass);

$queueQuery = $db->prepare("SELECT `ID`,`VideoID` FROM `conversions` WHERE `Deleted`=FALSE AND `TimeCompleted` IS NOT NULL AND (`TimeCompleted` + 28800) < UNIX_TIMESTAMP()");
$queueQuery->execute();
$toDeleteArr = $queueQuery->fetchAll();

foreach ($toDeleteArr as $toDelete) {
	$reqID = $toDelete["ID"];
	$reqVideoID = $toDelete["VideoID"];
	
	unlink($outputDir . "/" . preg_replace('((^\.)|\/|(\.$))', '', $reqVideoID) . ".mp3");
	
	$deleteQuery = $db->prepare("UPDATE `conversions` SET `Deleted`=TRUE WHERE `ID`=:id");
	$deleteQuery->execute(array(":id" => $reqID));
}
?>