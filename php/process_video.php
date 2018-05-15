<?php

function processVideo($post_id, $uploadfile) {
	$ffmpeg = "/usr/bin/ffmpeg";

	$attr = getVideoAttributes($uploadfile, $ffmpeg);

	$imagefile = $_SERVER["DOCUMENT_ROOT"] . "/uploads/thumbnail_$post_id.jpg";
	$size = "178x100";
	$getFromSecond = floor($attr['duration'] / 2);
	$cmd = "$ffmpeg -i $uploadfile -an -ss $getFromSecond -s $size $imagefile";
	if (shell_exec($cmd)) {
		return true;
	} else {
		return false;
	}
}

function getVideoAttributes($video, $ffmpeg) {
	$cmd = "$ffmpeg -i $video -vstats 2>&1";
	$output = shell_exec($cmd);
	$regex_sizes = "/Video: ([^\r\n]*), ([^,]*), ([0-9]{1,4})x([0-9]{1,4})/"; 
	if (preg_match($regex_sizes, $output, $regs)) {
		$codec = $regs [1] ? $regs [1] : null;
		$width = $regs [3] ? $regs [3] : null;
		$height = $regs [4] ? $regs [4] : null;
	}

	$regex_duration = "/Duration: ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2}).([0-9]{1,2})/";
    if (preg_match($regex_duration, $output, $regs)) {
        $hours = $regs [1] ? $regs [1] : null;
        $mins = $regs [2] ? $regs [2] : null;
        $secs = $regs [3] ? $regs [3] : null;
        // Get total duration, in seconds
        $duration = $hours * 3600 + $mins * 60 + $secs;
    }

    return array(
    	'codec' => $codec,
        'width' => $width,
        'height' => $height,
        'duration' => $duration
    );
}
