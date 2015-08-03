<?php
require_once("../vendor/autoload.php");
require "../settings.php";

// Initialize framework
$f3 = \Base::instance();
$f3->set("siteName", "youtube2mp3");
$f3->set("DB", new \DB\SQL("mysql:host=" . $dbHost . ";dbname=" . $dbName . ";charset=utf8", $dbUser, $dbPass));

// Route home page
$f3->route("GET /",
	function ($f3) {
		$f3->set("pageName", "Home");
		$f3->set("pageType", "main");
		
		echo Template::instance()->render("../views/base.tpl");
	}
);

// Route convert page
$f3->route("POST /convert",
	function ($f3) {
		$f3->set("pageName", "Convert");
		$f3->set("pageType", "convert");
		
		$ip_query = $f3->get("DB")->prepare("SELECT * FROM `conversions` WHERE `IP` = :IP AND `Completed` = '0'");
		$ip_query->bindValue(":IP", $_SERVER["REMOTE_ADDR"]);
		$ip_query->execute();
		
		if ($ip_query->rowCount() > 0) {
			$f3->error("Please wait for your current video to finish converting.");
		} else {
			$url_parts = parse_url($_POST["url"]);
			parse_str($url_parts["query"], $query_parts);
			
			if (strpos($url_parts["host"], "youtube.com") !== false) {
				if (isset($query_parts["v"])) {
					try {
						$insert_query = $f3->get("DB")->prepare("INSERT INTO `conversions` (`VideoID`, `IP`, `TimeAdded`) VALUES (:VideoID, :IP, :TimeAdded)");
						
						$insert_query->bindValue(":VideoID", $query_parts["v"]);
						$insert_query->bindValue(":IP", $_SERVER["REMOTE_ADDR"]);
						$insert_query->bindValue(":TimeAdded", time());
						
						$insert_query->execute();
						
						echo Template::instance()->render("../views/base.tpl");
					} catch (PDOException $exception) { 
						$f3->error("Database error!");
					}
				} else {
					$f3->error("Invalid URL entered!");
				}
			} else {
				$f3->error("Invalid URL entered!");
			}
		}
	}
);

// Route status path
$f3->route("GET /status",
	function ($f3) {
		$conversion_query = $f3->get("DB")->prepare("SELECT * FROM `conversions` WHERE `IP` = :IP ORDER BY `ID` DESC LIMIT 1");
		$conversion_query->bindValue(":IP", $_SERVER["REMOTE_ADDR"]);
		$conversion_query->execute();
		
		$status_response = array();
		
		if ($conversion_query->rowCount() > 0) {
			$conversion = $conversion_query->fetch(PDO::FETCH_ASSOC);
			
			if ($conversion["Started"] == "0") {
				// conversion hasn't started
				$status_response["response_type"] = "success";
				$status_response["response_message"] = "conversion_queued";
			} else {
				if ($conversion["Completed"] == "0") {
					// conversion has started, but hasn't completed
					$status_response["response_type"] = "success";
					$status_response["response_message"] = "conversion_started";
				} else {
					// conversion is done
					$status_response["response_type"] = "success";
					$status_response["response_message"] = "conversion_completed";
				}
			}
		} else {
			// no conversion queued
			$status_response["response_type"] = "error";
			$status_response["response_message"] = "no_conversion_found";
		}
		
		header("Content-Type: application/json");
		echo json_encode($status_response);
	}
);

// Route errors
$f3->set("ONERROR",
	function($f3) {
		$f3->set("pageName", "Error");
		$f3->set("pageType", "error");
		
		echo Template::instance()->render("../views/base.tpl");
	}
);

$f3->run();
?>