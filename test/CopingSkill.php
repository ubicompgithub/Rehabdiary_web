<?php
	
	$uid = $_POST['uid'];
	$timestamp = $_POST['data'][0];
	#$week = $_POST['data'][1];
	$skill_type = $_POST['data'][1];
	$skill_select = $_POST['data'][2];
	$recreation = $_POST['data'][3];
	$score = $_POST['data'][4];
    $week  = $_POST['data'][5];

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

    $recreation = mysql_real_escape_string($recreation);
	
	// Insert information into the database in table 'CopingSkill'
	$sql = "INSERT INTO CopingSkill (UserId,Date,Time,Timestamp,Timeslot,SkillType,
    SkillSelect,Recreation,Score,Week) VALUES ('$uid','$date','$time',$timestamp,$time_slot,$skill_type,$skill_select,'$recreation',$score, $week)";
	$result = mysql_query($sql);
	if (!$result){
		echo $sql;
		die("invalid mysql query");
	}
	mysql_close($dbhandle);
	echo "upload success";
?>

