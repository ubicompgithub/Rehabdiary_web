<?php
	
	$uid = $_POST['uid'];
	$timestamp = $_POST['data'][0];
	$week = $_POST['data'][1];
	$page = $_POST['data'][2];
	$isCorrect = $_POST['data'][3];
	$selection = $_POST['data'][4];
	$agreement = $_POST['data'][5];
	$score = $_POST['data'][6];

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
	
	// Insert information into the database in table 'EmotionDIY'
	$sql = "INSERT INTO StorytellingTest (UserId,Date,Time,Timestamp,Week,TimeSlot,QuestionPage,Correct,Selection,Agreement,Score) VALUES ('$uid','$date','$time',$timestamp,$week,$time_slot,$page,$isCorrect,'$selection',$agreement,$score)";
	$result = mysql_query($sql);
	if (!$result){
		echo $sql;
		die("invalid mysql query");
	}
	mysql_close($dbhandle);
	echo "upload success";
?>

