<?php

$uid = $_POST['uid'];
$isAfterTest = $_POST['data'][0];
$timestamp = $_POST['data'][1];
$recordTimestamp = $_POST['data'][2];
$record_timeslot = $_POST['data'][3];
$category = $_POST['data'][4];
$type = $_POST['data'][5];
$items = $_POST['data'][6];
$impact = $_POST['data'][7];
$action = $_POST['data'][8];
$feeling = $_POST['data'][9];
$thinking = $_POST['data'][10];
$finished = $_POST['data'][11];
$score = $_POST['data'][12];

//$week  = 0;
$week  = $_POST['data'][13];
$key = $_POST['data'][14];

$timestamp_in_sec = $timestamp/1000;
$date = date('Y-m-d', $timestamp_in_sec);
$time = date('H:i:s', $timestamp_in_sec);

$rTimestamp_in_sec = $recordTimestamp/1000;
$record_date = date('Y-m-d', $rTimestamp_in_sec);


include_once('../connect_db.php');
$dbhandle = connect_to_db();

$description = mysql_real_escape_string($description); //prevent SQL query error


$sql = "INSERT INTO NoteAdd (UserId,IsAfterTest,Date,Time,Timestamp,
	RecordDate,RecordTimeslot,Category,Type,Items,Impact,Action,Feeling,Thinking,isFinishRecord,Score,Week,RelationKey) 
	VALUES ('$uid',$isAfterTest,'$date','$time',$timestamp,'$record_date',$record_timeslot,$category,$type,$items,$impact,'{$action}','{$feeling}','{$thinking}',$finished,$score,$week,$key)";
	$result = mysql_query($sql);
	if (!$result){
		echo $sql;
		die("invalid mysql query");
	}
mysql_close($dbhandle);
echo "upload success";
?>

