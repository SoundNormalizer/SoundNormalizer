<!DOCTYPE html>
<html>
	<head>
		<title>{{ @pageName }} | {{ @siteName }}</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link rel="shortcut icon" href="favicon.ico">
		<link href="css/bootstrap.min.css" rel="stylesheet" media="screen">
		<link href="css/style.css" rel="stylesheet">
		
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
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
				<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<a class="navbar-brand" href="./">{{ @siteName }}</a>
			</div>
		</div>
	</div>
	<div class="container">
		<div class="col-md-8 col-md-offset-2 well">
			<div class="center">
				<include href="{{ '../views/' . @pageType . '.tpl' }}" />
			</div>
		</div>

		<check if="{{ @adCode != null }}">
			<true>
				<div class="col-md-8 col-md-offset-2">
					{{ @adCode }}	
				</div>
			</true>
		</check>
	</div>
	<footer>
		<div class="container">
			<p class="text-muted">&copy; 2015 {{ @siteName }}</p>
		</div>
	</footer>
	
	</body>
</html>