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
		
		if (isset($_COOKIE["token"])) {
			$cookie_query = $f3->get("DB")->prepare("SELECT * FROM `conversions` WHERE `Cookie` = :Cookie AND `Completed` = '0'");
			$cookie_query->bindValue(":Cookie", $_COOKIE["token"]);
			$cookie_query->execute();
			
			if ($cookie_query->rowCount() > 0) {
				$f3->error("Please wait for your current video to finish converting.");
			}
		}
		
		$url_parts = parse_url($_POST["url"]);
		parse_str($url_parts["query"], $query_parts);
		
		
		if (strpos($url_parts["host"], "youtube.com") !== false) {
			if (isset($query_parts["v"])) {
				$token = bin2hex(mcrypt_create_iv(20, MCRYPT_DEV_URANDOM));
				
				try {
					$insert_query = $f3->get("DB")->prepare("INSERT INTO `conversions` (`VideoID`, `Cookie`, `IP`, `TimeAdded`) VALUES (:VideoID, :Cookie, :IP, :TimeAdded)");
					
					$insert_query->bindValue(":VideoID", $query_parts["v"]);
					$insert_query->bindValue(":Cookie", $token);
					$insert_query->bindValue(":IP", (empty($_SERVER['HTTP_CLIENT_IP'])?(empty($_SERVER['HTTP_X_FORWARDED_FOR'])?$_SERVER['REMOTE_ADDR']:$_SERVER['HTTP_X_FORWARDED_FOR']):$_SERVER['HTTP_CLIENT_IP']));
					$insert_query->bindValue(":TimeAdded", time());
					
					$insert_query->execute();
					
					setcookie("token", $token);
					
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