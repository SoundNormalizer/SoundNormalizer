<!DOCTYPE html>
<html>
	<head>
		<check if="{{ @pageType == 'main' }}">
			<true>
				<title>{{ @siteName }} - normalize MP3s online</title>
				<meta name="description" content="The easiest way to normalize audio online, whether it is from YouTube or from your hard drive.">
			</true>
			<false>
				<title>{{ @pageName }} - {{ @siteName }}</title>
			</false>
		</check>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link rel="shortcut icon" href="favicon.ico">
		<link href="css/bootstrap.min.css" rel="stylesheet" media="screen">
		<link href="css/style.css" rel="stylesheet">
		
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
		<check if="{{ @pageType == 'main' }}">
			<true>
				<script src="js/bootstrap.min.js"></script>
				<script src="js/main.js"></script>
			</true>
		</check>
		<check if="{{ @pageType == 'status' }}">
			<true>
				<script src="js/status_checker.js"></script>
			</true>
		</check>		
		<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
		<!--[if lt IE 9]>
		  <script src="js/html5shiv.js"></script>
		  <script src="js/respond.min.js"></script>
		<![endif]-->
	</head>
	<body>
	<div class="navbar navbar-default navbar-fixed-top">
		<div class="container">
			<div class="navbar-header">
				<a class="navbar-brand" href="./"><span class="glyphicon glyphicon-music brand-glyph"></span> &nbsp; {{ @siteName }}</a>
			</div>
		</div>
	</div>
	<div class="container">
		<div class="col-md-8 col-md-offset-2 well">
			<div class="center">
				<include href="{{ '../views/' . @pageType . '.tpl' }}" />
			</div>
		</div>

		<include href="{{ '../views/ads.tpl' }}" />
	</div>
	<footer>
		<div class="container">
			<p class="text-muted">&copy; 2015 {{ @siteName }}</p>
		</div>
	</footer>
	
	</body>
</html>