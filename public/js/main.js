var selectedForm = "youtube";

$(document).ready(function() {
	// jquery toggle sets "inline" for upload-instructions, causing it to not center in @media, so we use this instead
	$.fn.toggleBlock = function() {
		if ($(this).css("display") == "none") {
			$(this).css("display", "block");
		}
		else {
			$(this).hide();
		}
	}

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

	// show recaptcha modal if user hits enter to submit form
	$("#youtube-url").keypress(formKeypress);
	$("#file-name").keypress(formKeypress);
});

function formKeypress(e) {
	if (e.which == 13) {
		$("#captcha-modal").modal("show");

		return false;
	}
}

function toggleForm() {
	// switch forms
	$("#youtube-form").toggle();
	$("#upload-form").toggle();
	$("#youtube-switch").toggle();
	$("#upload-switch").toggle();
	$("#youtube-instructions").toggleBlock();
	$("#upload-instructions").toggleBlock();
	
	if (selectedForm == "youtube") {
		selectedForm = "upload";

		$("form").attr("action", "upload");
		$("form").attr("enctype", "multipart/form-data");
	}
	else {
		selectedForm = "youtube";

		$("form").attr("action", "youtube");
		$("form").removeAttr("enctype");
	}
}

function captchaSuccess() {
	// 1 second delay so the green checkmark animation completes and user sees it
	setTimeout(function() {
		$("form").submit();
	}, 1000);
}