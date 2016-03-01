<?php
	
	$uid = $_POST['uid'];
	$timestamp = $_POST['data'][0];
	$week = $_POST['data'][1];
	$pageWeek = $_POST['data'][2];
	$pageLevel = $_POST['data'][3];
	$text = $_POST['data'][4];
	$addedScore = $_POST['data'][5];
	$uploadSuccess = $_POST['data'][6];
	$privacy = $_POST['data']['7'];
	$score = $_POST['data'][8];

	$timestamp_in_sec = $timestamp/1000;
	$date = date('Y-m-d', $timestamp_in_sec);
	$time = date('H:i:s', $timestamp_in_sec);

	include_once('../connect_db.php');
	$dbhandle = connect_to_db();

	$text = mysql_real_escape_string($text);	
	$sql = "INSERT INTO Facebook (UserId,Date,Time,Timestamp,Week,PageWeek,PageLevel,Text,AddedScore,UploadSuccess,Privacy,Score) VALUES ('$uid','$date','$time',$timestamp,$week,$pageWeek,$pageLevel,'{$text}',$addedScore,$uploadSuccess,$privacy,$score)";
	$result = mysql_query($sql);
	if (!$result){
		echo $sql;
		die("invalid mysql query");
	}
	mysql_close($dbhandle);
	echo "upload success";
?>

