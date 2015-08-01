<?php
require_once("../vendor/autoload.php");

F3::set("DB", new \DB\SQL("mysql:host=" . apache_getenv("DB_HOST") . ";dbname=" . apache_getenv("DB_NAME") . ";charset=utf8", apache_getenv("DB_USER"), apache_getenv("DB_PASS")));

// Initialize framework
$f3 = \Base::instance();
$f3->set("siteName", "youtube2mp3");

// Route home page
$f3->route("GET /",
	function ($f3) {
		$f3->set("pageName", "Home");
		$f3->set("pageType", "main");
		
		$template = new Template;
		echo $template->render("../views/base.tpl");
	}
);

// Route convert page
$f3->route("POST /convert",
	function ($f3) {
		$f3->set("pageName", "Convert");
		$f3->set("pageType", "convert");
		
		$template = new Template;
		echo $template->render("../views/base.tpl");
	}
);

// Route errors
$f3->set("ONERROR",
	function($f3) {
		$f3->set("pageName", "Error");
		$f3->set("pageType", "error");
		
		$template = new Template;
		echo $template->render("../views/base.tpl");
	}
);

$f3->run();
?>