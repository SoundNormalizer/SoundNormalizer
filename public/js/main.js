$(document).ready(function() {
	$("#normToggle").change(function() {
		if ($(this).is(":checked")) {
			$("#ytSubmit").val("Convert & Normalize");
		}
		else {
			$("#ytSubmit").val("Convert");
		}
	});
	
	$("#switchToUpload").click(toggleForm);
	$("#switchToYt").click(toggleForm);
});

function toggleForm() {
	$("#ytForm").toggle();
	$("#switchToUpload").toggle();
	$("#uploadForm").toggle();
	$("#switchToYt").toggle();
}