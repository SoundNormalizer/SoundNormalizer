$(document).ready(function() {
	$(document).on("click", "#dlBtn", function() {
		window.location.href = "download";
	});
	
	setInterval(function() {
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
					switch (data.status_code) {
						case 3:
							var btnHtml = '<button type="submit" class="btn btn-primary btn-block" id="dlBtn"><span class="glyphicon glyphicon-download-alt"></span> &nbsp; Download Converted MP3</button>';
							$(".progress").before(btnHtml);
							$(".progress").remove();
							break;
							
						case 1:
							break;
							
						case 2:
							break;
							
						case 4:
							break;
						
						default:
							break;
					}
					break;
					
				case "no_conversion_found":
					break;
					
				default:
					break;
			}
		});
	}, 3000);
});

function displayError(errMsg) {
	
}