var selectedForm = "#youtube-form";

$(document).ready(function() {
	// register handlers to switch forms
	$("#upload-switch").click(toggleForm);
	$("#youtube-switch").click(toggleForm);
	
	// pass browse button click to upload input
	$("#browse-button").click(function() {
		$("#file").click();
	});
	
	// populate file name field with selected file's name
	$("#file").change(function() {
		$("#file-name").val($(this).val().match(/[^\/\\]+$/));
	});
	
	// register handler to toggle checkbox when setting text is clicked
	$("#settings-dropdown a").on("click", function(event) {
		if($(event.target).is("input")) return;
		
		var checkBoxes = $("[name=normalize-checkbox]");
		checkBoxes.prop("checked", !checkBoxes.prop("checked")).triggerHandler("change");
		
		return false;
	});
	
	// register handler to change button text when normalize setting is changed
	$("[name=normalize-checkbox]").change(function() {
		if ($(this).is(":checked")) {
			$("#youtube-submit").text("Convert & Normalize");
		} else {
			$("#youtube-submit").text("Convert");
		}
	});
});

function toggleForm() {
	// switch forms
	$("#youtube-form").toggle();
	$("#upload-form").toggle();
	$("#youtube-switch").toggle();
	$("#upload-switch").toggle();
	$("#youtube-instructions").toggle();
	$("#upload-instructions").toggle();
	
	if ($("#captcha-upload").html().trim() == "") {
		$("#captcha-modal").appendTo("#captcha-upload");
		selectedForm = "#upload-form";
	}
	else {
		$("#captcha-modal").appendTo("#captcha-youtube");
		selectedForm = "#youtube-form";
	}
}

function captchaSuccess() {
	// 1 second delay so the green checkmark animation completes and user sees it
	setTimeout(function() {
		$(selectedForm + " form").submit();
	}, 1000);
}