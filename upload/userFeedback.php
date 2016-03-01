<?php
	
	$uid = $_POST['uid'];
	
	$timestamp = $_POST['data'][0];
	$dTimestamp = $_POST['data'][1];
	$testSuccess = $_POST['data'][2];
	$hasData = $_POST['data'][3];
	$timestamp_in_sec = $timestamp/1000;
        $date = date('Y-m-d', $timestamp_in_sec);
        $time = date('H:i:s', $timestamp_in_sec);


	if ($hasData == 1){
		$uploadDest = '../patients/' . $uid . '/feedback';
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
			die ("# of upload file = 0");
		}
		$datafile = "$uploadDest/$dTimestamp.3gp";
		if (!file_exists($datafile)){
			die ("Data file not found");
		}
	}
	include_once('../connect_db.php');
	$dbhandle = connect_to_db();

	$sql = "INSERT INTO TryAgainFeedback(UserId,Timestamp,Date,Time,DetectionTimestamp,TestSuccess,HasData) VALUES ('$uid',$timestamp,'$date','$time','$dTimestamp',$testSuccess,$hasData)";
	echo "$sql\n";
	$result = mysql_query($sql);
	if (!$result)
		die('invalid query - insert');

	$sql = "UPDATE Detection SET HasVoiceFeedback = 1 WHERE UserId = '$uid' AND Timestamp = $dTimestamp";
	
	$result = mysql_query($sql);
	if (!$result)
		die ('invalid query - update');

	mysql_close($dbhandle);

	echo 'upload success';
?>
