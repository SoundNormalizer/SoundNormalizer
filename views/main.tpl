		<h1 class="instructions">
			<i class="glyphicon glyphicon-cloud-download download-icon"></i>
			<span class="instruction-text" id="ytHead"><strong>Paste YouTube URL</strong> into the text field and submit to <strong>normalize video(s)</strong></span>
			<span class="instruction-text" id="uploadHead"><strong>Browse for a file</strong> to upload and submit to <strong>normalize audio track(s)</strong></span>
		</h1>
		<div id="ytForm">
			<form method="POST" action="convert">
				<div class="input-group full-width">
					<input name="normalize" type="checkbox" id="normToggle" data-width="100%" data-toggle="toggle" data-on="Normalization On" data-off="Normalization Off" data-onstyle="success" data-offstyle="danger" checked="checked">
				</div>
				<div class="input-group">
					<input name="url" type="text" placeholder="Example: https://www.youtube.com/watch?v=dQw4w9WgXcQ" class="form-control input-lg youtube-url">
					<span class="input-group-btn">
						<input type="submit" class="btn btn-primary btn-lg convert-button" id="ytSubmit" value="Convert & Normalize">
					</span>
				</div>
			</form>
		</div>
		<div id="uploadForm">
			<form method="POST" action="normalize">
				<div class="input-group">
					<input id="file" type="file" name="file">
					<span class="input-group-btn">
						<button type="button" id="selectFile" class="btn btn-info btn-lg convert-button">
							<span class="glyphicon glyphicon-folder-open"></span> &nbsp;
							Browse
						</button>
					</span>
					<input id="fileName" type="text" class="form-control input-lg youtube-url" readonly="readonly">
					<span class="input-group-btn">
						<input type="submit" class="btn btn-primary btn-lg convert-button" value="Normalize">
					</span>
				</div>
			</form>
		</div>
		<div id="switchForm">
			<a href="#" id="switchToUpload">Or, upload an mp3 file to normalize...</a>
			<a href="#" id="switchToYt">Or, convert and normalize a YouTube video...</a>
		</div>
		