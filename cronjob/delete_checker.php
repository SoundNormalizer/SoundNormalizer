<?php
set_time_limit(0);
require dirname(__FILE__) . "/../settings.php";

$outputDir = realpath(dirname(__FILE__) . "/../converted/");
$db = new PDO("mysql:host=" . $dbHost . ";dbname=" . $dbName . ";charset=utf8", $dbUser, $dbPass);

$queueQuery = $db->prepare("SELECT `ID`,`LocalName`FROM `conversions` WHERE (`DuplicateOf` = '0' AND `Deleted` = FALSE AND `TimeCompleted` IS NOT NULL AND (`TimeCompleted` + 28800) < UNIX_TIMESTAMP())");
$queueQuery->execute();
$toDeleteArr = $queueQuery->fetchAll();

foreach ($toDeleteArr as $toDelete) {
	$reqID = $toDelete["ID"];
	$reqLocalName = $toDelete["LocalName"];
	
	unlink($outputDir . "/" . preg_replace('((^\.)|\/|(\.$))', '', $reqLocalName) . ".mp3");
	
	$deleteQuery = $db->prepare("UPDATE `conversions` SET `Deleted`=TRUE WHERE `ID`=:id");
	$deleteQuery->execute(array(":id" => $reqID));
}
?>