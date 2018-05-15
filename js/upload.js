var upload = document.getElementById("file-upload");
var filePreviewText = document.getElementById("selected-file");

function selectFiles() {
	upload.click();
}

var previewImage = document.getElementById("preview-image");

function uploadFile() {
	var f = upload.files.item(0);
	filePreviewText.innerHTML = "Selected file: " + f.name;
	if (previewImage != undefined) {
		previewImage.src = URL.createObjectURL(f);
	}
}

upload.onchange = uploadFile;