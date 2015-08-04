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
		
		$ip_query = $f3->get("DB")->prepare("SELECT * FROM `conversions` WHERE (`IP` = :IP AND `Completed` = '0')");
		$ip_query->bindValue(":IP", $_SERVER["REMOTE_ADDR"]);
		$ip_query->execute();
		
		if ($ip_query->rowCount() > 0) {
			$f3->error("Please wait for your current video to finish converting.");
		} else {
			$url_parts = parse_url($_POST["url"]);
			parse_str($url_parts["query"], $query_parts);
			
			if (strpos($url_parts["host"], "youtube.com") !== false) {
				if (isset($query_parts["v"])) {
					$video_id = $query_parts["v"];
					$normalize = (isset($_POST["normalize"]) ? 1 : 0);
					
					// check if the file has been converted recently
					$duplicate_check_query = $f3->get("DB")->prepare("SELECT * FROM `conversions` WHERE (`VideoID` = :VideoID AND `Normalized` = :Normalized AND `Completed` = '1' AND `StatusCode` = '3' AND `Deleted` = '0' AND `TimeCompleted` IS NOT NULL)");
					$duplicate_check_query->bindValue(":VideoID", $video_id);
					$duplicate_check_query->bindValue(":Normalized", $normalize);
					$duplicate_check_query->execute();
					
					if ($duplicate_check_query->rowCount() > 0) {
						$insert_query_str = "INSERT INTO `conversions` (`VideoID`, `Started`, `Completed`, `StatusCode`, `Normalized`, `IP`, `TimeAdded`) VALUES (:VideoID, TRUE, TRUE, '3', :Normalized, :IP, :TimeAdded)";
					} else {						
						$insert_query_str = "INSERT INTO `conversions` (`VideoID`, `Normalized`, `IP`, `TimeAdded`) VALUES (:VideoID, :Normalized, :IP, :TimeAdded)";
					}
					
					try {
						$insert_query = $f3->get("DB")->prepare($insert_query_str);
						
						$insert_query->bindValue(":VideoID", $video_id);
						$insert_query->bindValue(":Normalized", $normalize);
						$insert_query->bindValue(":IP", $_SERVER["REMOTE_ADDR"]);
						$insert_query->bindValue(":TimeAdded", time());
						
						$insert_query->execute();
						
						if ($duplicate_check_query->rowCount() > 0) {
							// redirect to download existing file
							$f3->reroute("@download");
						}
						else {
							// proceed with conversion
							echo Template::instance()->render("../views/base.tpl");
						}
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
		$conversion_query = $f3->get("DB")->prepare("SELECT * FROM `conversions` WHERE (`IP` = :IP AND `Deleted` = '0') ORDER BY `ID` DESC LIMIT 1");
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
					$status_response["status_code"] = $conversion["StatusCode"];
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

// Route download path
$f3->route("GET @download: /download",
	function ($f3) {
		$conversion_query = $f3->get("DB")->prepare("SELECT * FROM `conversions` WHERE (`IP` = :IP AND `Completed` = '1' AND `StatusCode` = '3' AND `Deleted` = '0') ORDER BY `ID` DESC LIMIT 1");
		$conversion_query->bindValue(":IP", $_SERVER["REMOTE_ADDR"]);
		$conversion_query->execute();
		
		if ($conversion_query->rowCount() > 0) {
			$conversion = $conversion_query->fetch(PDO::FETCH_ASSOC);
			
			$output_dir = realpath(dirname(__FILE__) . "/../converted/");
			$output_file = $output_dir . "/" . preg_replace('((^\.)|\/|(\.$))', '', $conversion["VideoID"]) . ".mp3";
			
			$continueWithDL = false;
			
			if (!empty($conversion["TimeCompleted"])) {
				$continueWithDL = true;
			}
			else {
				$duplicate_query = $f3->get("DB")->prepare("SELECT * FROM `conversions` WHERE (`VideoID`=:videoID AND `TimeCompleted` IS NOT NULL AND `Completed` = '1' AND `StatusCode` = '3' AND `Deleted` = '0' AND `Normalized`=:normal) ORDER BY `ID` DESC LIMIT 1");
				$duplicate_query->bindValue(":videoID", $conversion["VideoID"]);
				$duplicate_query->bindValue(":normal", $conversion["Normalized"]);
				$duplicate_query->execute();
				
				$get_duplicates = $duplicate_query->fetchAll();
				if (count($get_duplicates) > 0) {
					$continueWithDL = true;
				}
				else {
					$f3->error("No download found.");
				}
			}
			
			if ($continueWithDL && file_exists($output_file)) {
				header("X-Sendfile: $output_file");
				header("Content-type: audio/mpeg");
				header('Content-Disposition: attachment; filename="' . basename($output_file) . '"');
				
				die();
			} else {
				$f3->error("No download found.");
			}
		} else {			
			$f3->error("No download found.");
		}
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