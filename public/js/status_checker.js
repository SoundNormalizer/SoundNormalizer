var checkInterval;

$(document).ready(function() {
	$(document).on("click", "#dlBtn", function() {
		window.location.href = "download";
	});
	
	checkInterval = setInterval(function() {
		$.ajax({
			url: "status",
			dataType: "json"
		}).done(function(data) {
			switch (data.response_message) {
				case "conversion_queued":
					$("#status").text("In Queue");
					break;
					
				case "conversion_started":
					$("#status").text("Conversion started");
					break;
					
				case "conversion_completed":
					switch (parseInt(data.status_code)) {
						case 3:
							$("#status").text("Conversion complete");
							
							var btnHtml = '<button type="submit" class="btn btn-primary btn-block" id="dlBtn"><span class="glyphicon glyphicon-download-alt"></span> &nbsp; Download Converted MP3</button>';
							$(".progress").before(btnHtml);
							$(".progress").remove();
							break;
							
						case 1:
							displayError("Conversion failed: The video doesn't exist");
							break;
							
						case 2:
							displayError("Conversion failed: Couldn't load the video");
							break;
							
						case 4:
							displayError("Conversion failed: Unknown error");
							break;
						
						default:
							displayError("Conversion failed: Unknown status");
							break;
					}
					
					clearInterval(checkInterval);
					break;
					
				case "no_conversion_found":
					displayError("You have no conversions queued");
					clearInterval(checkInterval);
					break;
					
				default:
					break;
			}
		});
	}, 3000);
});

function displayError(errMsg) {
	$(".center").html('<h1>' + errMsg + '</h1><img id="errorImage" src="./img/error.png">');
}