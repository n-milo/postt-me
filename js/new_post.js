var upload = document.getElementById("file-upload");
var filePreviewText = document.getElementById("selected-file");
var previewImage = document.getElementById("preview-image");
var sel = document.getElementById("type-selector");
var body = document.getElementById("body-input");
var hide = document.getElementById("hide-on-text");
var title = document.getElementById("title-input");
var titleCount = document.getElementById("title-count");
var bodyCount = document.getElementById("body-count");
var bodyDescriptor = document.getElementById("body-descriptor");

function selectFiles() {
	upload.click();
}

function uploadFile() {
	var f = upload.files.item(0);
	filePreviewText.innerHTML = "Selected file: " + f.name;
	if (sel.value == "image") {
		previewImage.src = URL.createObjectURL(f);
		previewImage.style = "";
	} else {
		previewImage.style = "display:none;";
	}
}

upload.onchange = uploadFile;

function changeType() {
	if (sel.value == "text") {
		bodyDescriptor.innerHTML = "Body text";
		hide.style = "display:none;";
	} else {
		bodyDescriptor.innerHTML = "Description (optional)";
		hide.style = "";
	}
	
	if (sel.value == "image") {
		previewImage.style = "";
	} else {
		previewImage.style = "display:none;";
	}
	
	if (sel.value == "video") {
		showProgressBar();
	}
}

title.oninput = function() {
	titleCount.innerHTML = title.value.length;
}

body.oninput = function() {
	bodyCount.innerHTML = body.value.length;
}

var progressAmt = 0;
var progress = document.getElementById("progress-container");
var bar = document.getElementById("progress-bar");

function showProgressBar() {
	progress.style.display = 'block';
}

function setProgress(amt) {
	progressAmt = amt;
	bar.style.width = progressAmt + '%';
	if (amt >= 100) {
		bar.innerHTML = 'Done!';
		amt = 100;
	} else {
		bar.innerHTML = progressAmt + '%';
	}
}

function createRequest() {
	var http;
	if (navigator.appName == "Microsoft Internet Explorer") {
		http = new ActiveXObject("Microsoft.XMLHTTP");
	} else {
		http = new XMLHttpRequest();
	}
}

function sendRequest() {
	var http = createRequest();
	http.open("GET", "progress.php");
	http.onreadystatechange = function() { handleResponse(http); };
	http.send(null);
}

function handleResponse(http) {
	var response;
	if (http.readyState == 4) {
		response = http.responseText;
		setProgress(response);
		if (response < 100) {
			setTimeout("sendRequest()", 1000);
		} else {
			alert('Done!');
		}
	}
}

function startUpload() {
	setTimeout("sendRequest()", 1000);
}