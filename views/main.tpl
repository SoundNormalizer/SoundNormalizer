		<h1 id="instructions">
			<i class="glyphicon glyphicon-cloud-download download-icon"></i>
			<span class="instruction-text" id="youtube-instructions"><strong>Paste YouTube URL</strong> into the text field and submit to <strong>download normalized MP3(s)</strong></span>
			<span class="instruction-text" id="upload-instructions"><strong>Browse for a file</strong> to upload and submit to <strong>normalize audio track(s)</strong></span>
		</h1>
		<div id="youtube-form">
			<form method="POST" action="convert">
				<div class="input-group">
					<input id="youtube-url" name="url" type="text" class="form-control input-lg input-text" placeholder="Example: https://www.youtube.com/watch?v=dQw4w9WgXcQ">
					<span class="input-group-btn">
						<button type="button" class="btn btn-info btn-lg input-btn dropdown-toggle" id="settings-button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							<span class="glyphicon glyphicon-cog"></span>
							<span class="sr-only">Toggle Dropdown</span>
						</button>
						<ul id="settings-dropdown" class="dropdown-menu">
							<li id="normalize-option"><a href="#" class="small" tabIndex="-1"><input id="normalize-checkbox" type="checkbox" name="normalize-checkbox" checked><span>Normalize</span></a></li>
						</ul>
						<button type="button" class="btn btn-primary btn-lg input-btn" id="youtube-submit" data-toggle="modal" data-target="#captcha-modal">Download</button>
						
						<div id="captcha-youtube">
							<div id="captcha-modal" class="modal fade">
								<div class="modal-dialog">
									<div class="modal-content">
										<div class="modal-header">
											<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
											<h4 class="modal-title">Just one more step...</h4>
										</div>
										<div class="modal-body">
											<div class="g-recaptcha" data-callback="captchaSuccess" data-sitekey="{{ @recaptchaSiteKey }}"></div>
												<script type="text/javascript" src="https://www.google.com/recaptcha/api.js?hl={{ @recaptchaLang }}">
											</script>
										</div>
									</div>
								</div>
							</div>
						</div>
					</span>
				</div>
			</form>
		</div>
		<div id="upload-form">
			<form method="POST" action="normalize" enctype="multipart/form-data">
				<div class="input-group">
					<input id="file" type="file" name="file" accept=".mp3">
					<span class="input-group-btn">
						<button type="button" class="btn btn-info btn-lg input-btn" id="browse-button">
							<span class="glyphicon glyphicon-folder-open"></span>
							<span id="browse-text"> &nbsp; Browse</span>
						</button>
					</span>
					<input id="file-name" type="text" class="form-control input-lg input-text" readonly="readonly">
					<span id="upload-button" class="input-group-btn">
						<button type="button" class="btn btn-primary btn-lg input-btn" data-toggle="modal" data-target="#captcha-modal">Normalize</button>
					</span>
					
					<div id="captcha-upload"></div>
				</div>
			</form>
		</div>
		<div id="switchForm">
			<a href="#" id="upload-switch">Or, upload an MP3 file to normalize...</a>
			<a href="#" id="youtube-switch">Or, convert and normalize a YouTube video...</a>
		</div>