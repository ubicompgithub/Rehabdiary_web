<?php
	
	$uid = $_POST['uid'];
	$timestamp = $_POST['data'][0];
	#$week = $_POST['data'][1];
	$type = $_POST['data'][1];
	$isCorrect = $_POST['data'][2];
	$selection = $_POST['data'][3];
	$choose = $_POST['data'][4];
	$score = $_POST['data'][5];
    $week  = $_POST['data'][6];

	$timestamp_in_sec = $timestamp/1000;
	$date = date('Y-m-d', $timestamp_in_sec);
	$time = date('H:i:s', $timestamp_in_sec);

	$hr = intval(date('H',$timestamp_in_sec));
	$time_slot = 0;
	if (0 <= $hr && $hr < 12) {	// Time slot 1 - morning
		$time_slot = 0;
	} else if (12 <= $hr && $hr < 20) {	// Time slot 2 - noon
		$time_slot = 1;
	} else if (20 <= $hr && $hr < 24) {	// Time slot 3 - evening
		$time_slot = 2;
	}

	include_once('../connect_db.php');
	$dbhandle = connect_to_db();

    $selection = mysql_real_escape_string($selection);	
	// Insert information into the database in table 'QuestionTest'
	$sql = "INSERT INTO QuestionTest (UserId,Date,Time,Timestamp,Timeslot,Type,
    isCorrect,Selection,Choose,Score,Week) VALUES ('$uid','$date','$time',$timestamp,$time_slot,$type,$isCorrect,'$selection',$choose,$score,$week)";
	$result = mysql_query($sql);
	if (!$result){
		echo $sql;
		die("invalid mysql query");
	}
	mysql_close($dbhandle);
	echo "upload success";
?>

