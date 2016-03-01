<?php
	
	$uid = $_POST['uid'];
	$timestamp = $_POST['data'][0];
	$counter = $_POST['data'][1];

	$timestamp_in_sec = $timestamp/1000;
	$date = date('Y-m-d', $timestamp_in_sec);
	$time = date('H:i:s', $timestamp_in_sec);

	include_once('../connect_db.php');
	$dbhandle = connect_to_db();

	$sql = "INSERT INTO ExchangeHistory (UserId,Date,Time,Timestamp,NumOfCounter) VALUES ('$uid','$date','$time',$timestamp,$counter)";
	$result = mysql_query($sql);
	if (!$result){
		echo $sql;
		die("invalid mysql query");
	}
	mysql_close($dbhandle);
	echo "upload success";
?>

