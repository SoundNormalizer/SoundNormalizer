<?php
require_once("../vendor/autoload.php");
require "../settings.php";

// Initialize framework
$f3 = \Base::instance();
$f3->set("siteName", "youtube2mp3");
$f3->set("Core", new SoundNormalizer\Core($f3));
$f3->set("DB", new \DB\SQL("mysql:host=" . $dbHost . ";dbname=" . $dbName . ";charset=utf8", $dbUser, $dbPass));
$f3->set("DEBUG", 0);

// Recaptcha info
$f3->set("recaptchaSiteKey", $recaptchaSiteKey);
$f3->set("recaptchaSecret", $recaptchaSecret);
$f3->set("recaptchaLang", $recaptchaLang);

// Route home page
$f3->route("GET /",
	function ($f3) {
		$f3->set("pageName", "Home");
		$f3->set("pageType", "main");
		
		echo Template::instance()->render("../views/base.tpl");
	}
);

// Route YouTube page
$f3->route("POST /youtube",
	function ($f3) {
		$f3->set("pageName", "YouTube");
		$f3->set("pageType", "youtube");

		$recaptcha = new \ReCaptcha\ReCaptcha($f3->get("recaptchaSecret"));
		$recapResp = $recaptcha->verify($_POST["g-recaptcha-response"], $_SERVER["REMOTE_ADDR"]);
		
		if (!$recapResp->isSuccess()) {
			$f3->error("Invalid captcha!");
		}

		if ($f3->get("Core")->isAlreadyConverting()) {
			$f3->error("Please wait for your current file to finish converting.");
		} else {
			$video_id = SoundNormalizer\Utilities::getYouTubeID($_POST["url"]);

			if ($video_id === false) {
				$f3->error("Invalid URL entered!");
			} else {
				$normalize = (isset($_POST["normalize-checkbox"]) ? 1 : 0);
				$duplicate_check_results = $f3->get("Core")->fetchRecentCompletedMatches("youtube", $video_id, $normalize);

				if (count($duplicate_check_results) > 0) {
					$f3->get("Core")->insertDuplicateConversion("youtube", $duplicate_check_results[0]["ID"]);
					$f3->reroute("@download");
				} else {
					$f3->get("Core")->insertConversion("youtube", $video_id, $normalize);
					$f3->reroute("@status");
				}
			}
		}
	}
);

// Route upload page
$f3->route("POST /upload",
	function($f3) {
		$f3->set("pageName", "Upload");
		$f3->set("pageType", "upload");
		
		$recaptcha = new \ReCaptcha\ReCaptcha($f3->get("recaptchaSecret"));
		$recapResp = $recaptcha->verify($_POST["g-recaptcha-response"], $_SERVER["REMOTE_ADDR"]);
		
		if (!$recapResp->isSuccess()) {
			$f3->error("Invalid captcha!");
		}
		
		if (isset($_FILES["file"]) && !empty($_FILES["file"])) {
			$tmpName = $_FILES["file"]["tmp_name"];
			$fileError = $_FILES["file"]["error"];
			
			if ($fileError === UPLOAD_ERR_OK) {
				$finfo = finfo_open(FILEINFO_MIME_TYPE);
				$mimeType = finfo_file($finfo, $tmpName);
			
				if ($mimeType != "audio/mpeg") {
					$f3->error("Only MP3 files can be normalized.");
				}
				else {
					echo "i plan to do stuff here.";
				}				
			}
			elseif ($fileError === UPLOAD_ERR_NO_FILE) {
				$f3->error("No file was uploaded!");
			}
			else {
				$f3->error("Your file was not properly uploaded.");
			}
		}
		else {
			// ini config value to bytes adapted from http://php.net/manual/en/function.ini-get.php
			$maxFileSize = trim(ini_get("post_max_size"));
			$sizeUnit = strtolower(substr($maxFileSize, -1));
			switch($sizeUnit) {
				case "g":
					$maxFileSize *= 1024;
				case "m":
					$maxFileSize *= 1024;
				case "k":
					$maxFileSize *= 1024;
			}
			
			if ($_SERVER["CONTENT_LENGTH"] > $maxFileSize) {
				$f3->error("Your file was too big! (Max: " . strtoupper(trim(ini_get("post_max_size"))) . "B)");
			}
			else {
				$f3->error("No file was uploaded!");
			}			
		}
	}
);

// Route status page
$f3->route("GET @status: /status",
	function ($f3) {
		$f3->set("pageName", "Status");
		$f3->set("pageType", "status");

		if ($f3->get("Core")->isAlreadyConverting()) {
			echo Template::instance()->render("../views/base.tpl");
		} else {
			$f3->error("You have no files converting right now.");
		}
	}
);

// Route download path
$f3->route("GET @download: /download",
	function ($f3) {
		$download = $f3->get("Core")->getDownload();
		if ($download === false) {
			$f3->error("No download found.");
		} else {
			$output_dir = realpath(dirname(__FILE__) . "/../converted/");
			$output_file = $output_dir . "/" . preg_replace('((^\.)|\/|(\.$))', '', $download["LocalName"]) . ".mp3";
			
			if (file_exists($output_file)) {
				$filename = "[" . $f3->get("siteName") . "]_";
				if ($download["Type"] == "youtube") {
					$filename .= $download["YouTubeID"] . ".mp3";	
				} else {
					$filename .= $download["FileName"];
				}

				header("X-Sendfile: $output_file");
				header("Content-type: audio/mpeg");
				header('Content-Disposition: attachment; filename="' . $filename . '"');
				
				die();
			} else {
				$f3->error("No download found.");
			}
		}
	}
);

// Route status API
$f3->route("GET /api/status",
	function ($f3) {
		$conversion_query = $f3->get("DB")->prepare("SELECT * FROM `conversions` WHERE (`IP` = :IP AND `Deleted` = '0') ORDER BY `ID` DESC LIMIT 1");
		$conversion_query->bindValue(":IP", $_SERVER["REMOTE_ADDR"]);
		$conversion_query->execute();
		
		$status_response = array();
		
		if ($conversion_query->rowCount() > 0) {
			$conversion = $conversion_query->fetch(\PDO::FETCH_ASSOC);
			
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