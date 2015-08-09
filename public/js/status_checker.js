var checkInterval;

$(document).ready(function() {
	$(document).on("click", "#dlBtn", function() {
		window.location.href = "download";
	});
	
	checkInterval = setInterval(function() {
		$.ajax({
			url: "api/status",
			dataType: "json"
		}).done(function(data) {
			var statusType = "Normalization";
			var buttonText = "Normalized";
			if (data.response_type == "success") {
				if (data.conversion_type == "youtube") {
					if (parseInt(data.normalization) == 0) {
						statusType = "Conversion";
						buttonText = "Converted";
					}
				}
			}
			
			switch (data.response_message) {
				case "conversion_queued":
					$("#status").text("In Queue");
					break;
					
				case "conversion_started":
					$("#status").text(statusType + " started");
					break;
					
				case "conversion_completed":
					switch (parseInt(data.status_code)) {
						case 3:
							$("#status").text(statusType + " complete");
							
							var btnHtml = '<button type="submit" class="btn btn-primary btn-block" id="dlBtn"><span class="glyphicon glyphicon-download-alt"></span> &nbsp; Download ' + buttonText + ' MP3</button>';
							$(".progress").before(btnHtml);
							$(".progress").remove();
							break;
							
						case 1:
							displayError(statusType + " failed: The video doesn't exist");
							break;
							
						case 2:
							displayError(statusType + " failed: Couldn't load the video");
							break;
							
						case 4:
							displayError(statusType + " failed: Unknown error");
							break;
							
						case 5:
							displayError(statusType + " failed: File not found");
							break;
							
						case 6:
							displayError(statusType + " failed: Corrupted MP3 file");
							break;
						
						default:
							displayError(statusType + " failed: Unknown status");
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