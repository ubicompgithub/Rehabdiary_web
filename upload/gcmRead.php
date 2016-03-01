<?php
	
	$uid = $_POST['uid'];
	$timestamp = $_POST['data'][0];
	$readTimestamp = $_POST['data'][1];
	$message = $_POST['data'][2];

	$timestamp_in_sec = $timestamp/1000;
	$date = date('Y-m-d', $timestamp_in_sec);
	$time = date('H:i:s', $timestamp_in_sec);

	$readTimestamp_in_sec = $readTimestamp/1000;
	$readDate = date('Y-m-d', $readTimestamp_in_sec);
	$readTime = date('H:i:s', $readTimestamp_in_sec);

	include_once('../connect_db.php');
	$dbhandle = connect_to_db();

	$message = mysql_real_escape_string($message);	
	$sql = "INSERT INTO GCMRead (UserId,Date,Time,Timestamp,ReadDate,ReadTime,ReadTimestamp,Message) VALUES ('$uid','$date','$time',$timestamp,'$readDate','$readTime',$readTimestamp,'{$message}')";
	$result = mysql_query($sql);
	if (!$result){
		echo $sql;
		die("invalid mysql query");
	}
	mysql_close($dbhandle);
	echo "upload success";
?>

