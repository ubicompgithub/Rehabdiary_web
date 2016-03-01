<?php
	
	$uid = $_POST['uid'];
	$timestamp = $_POST['data'][0];
	$week = $_POST['data'][1];
	$type = $_POST['data'][2];
	$seq = $_POST['data'][3];
	$score = $_POST['data'][4];

	$ans = preg_split("/[\s,]+/",$seq);
	$ans_count = sizeof($ans);


	$columnStr = "";
	$valueStr = "";
	for ($i=0;$i<$ans_count;++$i){
		$idx =$i+1;
		$columnStr.=",A$idx";
		$valueStr .= ",$ans[$i]";
	}

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
	
	$sql = "INSERT INTO Questionnaire (UserId,Date,Time,Timestamp,Week,TimeSlot,QuestionnaireType,Seq,Score$columnStr) VALUES ('$uid','$date','$time',$timestamp,$week,$time_slot,$type,'$seq',$score $valueStr)";
	$result = mysql_query($sql);
	if (!$result){
		echo $sql;
		die("invalid mysql query");
	}
	mysql_close($dbhandle);
	echo "upload success";
?>

