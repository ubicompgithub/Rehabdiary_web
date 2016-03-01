<?php
	
	$uid = $_POST['uid'];
	
	$timestamp = $_POST['data'][0];
	$week = $_POST['data'][1];
	$rYear = $_POST['data'][2];
	$rMonth = $_POST['data'][3];
	$rDay = $_POST['data'][4];
	$score = $_POST['data'][5];
	$uploadFile = $_POST['data'][6];

	$uploadDest = '../patients/' . $uid . '/audio_data';
	if (!file_exists($uploadDest)) {
		if (!mkdir($uploadDest, 0777, true)) {
			die("Failed to create directory: " . $uploadDest);
		}
	}
	$len = count($_FILES['file']['name']);
	if ($len > 0) {
		for ($i=0; $i < $len; $i++) {
			$tmpName = $_FILES['file']['tmp_name'][$i];
			if (is_uploaded_file($tmpName)) {
				$fname = basename($_FILES['file']['name'][$i]);
				if (!move_uploaded_file($tmpName, $uploadDest . "/" . $fname)) {
					die("Fail to move the files");
				}
			} else {
				die("No upload file exists");
			}
		}
	}else{
		echo "len=$len\n";
	}
	
	include_once('../connect_db.php');
	$dbhandle = connect_to_db();

	$timestamp_in_sec = $timestamp/1000;	
	$date = date('Y-m-d', $timestamp_in_sec);
	$time = date('H:i:s', $timestamp_in_sec);
	$hr = intval(date('H', $timestamp_in_sec));

	// Determine time slot

	$time_slot = 0;
	if (0 <= $hr && $hr < 12) {
		$time_slot = 0;
	} else if (12 <= $hr && $hr < 20) {
		$time_slot = 1;
	} else if (20 <= $hr && $hr < 24) {
		$time_slot = 2;
	}

	$dataDate = "{$rYear}-{$rMonth}-{$rDay}";
	$datafile = "$uploadDest/{$rYear}_{$rMonth}_{$rDay}.3gp";

	if (file_exists($datafile) || $uploadFile==0){
	}else{
		if ($uploadFile == 1)
			die ("Data file not found");
	}

	if ($uploadFile==0)
		$datafile = "NONE";
	
	$sql = "INSERT INTO StorytellingRecord (UserId,Date,Time,Timestamp,Week,TimeSlot,RecordDate,RecordPath,Score) VALUES('$uid','$date','$time',$timestamp,$week,$time_slot,'$dataDate','$datafile',$score)";
	$result = mysql_query($sql);
	if (!$result)
		die ('invalid query');
	
	mysql_close($dbhandle);

	echo 'upload success';
?>
