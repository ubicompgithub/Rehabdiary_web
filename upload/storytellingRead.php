<?php
	
	$uid = $_POST['uid'];
	$timestamp = $_POST['data'][0];
	$week = $_POST['data'][1];
	$addedScore = $_POST['data'][2];
	$page = $_POST['data'][3];
	$score = $_POST['data'][4];

	$timestamp_in_sec = $timestamp/1000;
	$date = date('Y-m-d', $timestamp_in_sec);
	$time = date('H:i:s', $timestamp_in_sec);

	include_once('../connect_db.php');
	$dbhandle = connect_to_db();

	
	$sql = "INSERT INTO StorytellingReading (UserId,Date,Time,Timestamp,Week,AddedScore,Page,Score) VALUES ('$uid','$date','$time',$timestamp,$week,$addedScore,'$page',$score)";
	$result = mysql_query($sql);
	if (!$result){
		echo $sql;
		die("invalid mysql query");
	}
	mysql_close($dbhandle);
	echo "upload success";
?>

