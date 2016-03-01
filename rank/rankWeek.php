<?php

header('Content-type:application/json');

include_once('../connect_db.php');
$conn = connect_to_db();

$sql="SELECT UserId, JoinDate FROM Patient WHERE IsDropOut = 0 AND UserId LIKE 'rehab_%'";
//$sql="SELECT UserId, JoinDate FROM Alcoholic WHERE DropOut = 0";

$resultAll = mysql_query($sql);

if(!$resultAll){
	echo 'fail';
	mysql_close($conn);
	header($header);
	die();
}

$userStateArray = array();

$today = new DateTime("now");
$today_str = $today->format('Y-m-d');
$today_date_time = new DateTime($today_str);

$month_before = new DateTime($today_str);
$month_before->sub(new DateInterval('P7D'));
$month_before_str = $month_before->format('Y-m-d');
$month_before_date_time = new DateTime($month_before_str);


while ($row = mysql_fetch_array($resultAll)){
	$uid = $row['UserId'];
	$joinDate = $row['JoinDate'];
	$join_date_date_time = new DateTime($joinDate);
	$interval = $join_date_date_time->diff($today_date_time);
	$day_diff = $interval->format('%d');
	
	if( !is_numeric(substr($uid, 6,3 )) ){
                //echo $uid."\n";
                continue;
        }
	
	//TestResult
	$dScore = 0;
	$sql = "SELECT Score FROM TestResult WHERE UserId='$uid' ORDER BY Timestamp DESC LIMIT 1";
	$result = mysql_query($sql);
	if ($result){
		if($row = mysql_fetch_array($result))
			$dScore = $row['Score'];
	}
	
	$dScore_m = 0;
	$sql = "SELECT Score FROM TestResult WHERE UserId='$uid' AND Date <= '$month_before_str' ORDER BY Timestamp DESC LIMIT 1";
	$result = mysql_query($sql);
	if ($result){
		if($row = mysql_fetch_array($result))
			$dScore_m = $row['Score'];
	}

	$testResultScore = $dScore-$dScore_m;

	//NoteAdd
	$diyScore = 0;
	$sql = "SELECT Score FROM NoteAdd WHERE UserId='$uid' ORDER BY Timestamp DESC LIMIT 1";
	$result = mysql_query($sql);
	if ($result){
		if($row = mysql_fetch_array($result))
			$diyScore = $row['Score'];
	}
	
	$diyScore_m = 0;
	$sql = "SELECT Score FROM NoteAdd WHERE UserId='$uid' AND Date <= '$month_before_str' ORDER BY Timestamp DESC LIMIT 1";
	$result = mysql_query($sql);
	if ($result){
		if($row = mysql_fetch_array($result))
			$diyScore_m = $row['Score'];
	}


	$noteAddScore = $diyScore-$diyScore_m;


    
	//QuestionTest
	$emScore = 0;
	$sql = "SELECT Score FROM QuestionTest WHERE UserId='$uid' ORDER BY Timestamp DESC LIMIT 1";
	$result = mysql_query($sql);
	if ($result){
		if($row = mysql_fetch_array($result))
			$emScore = $row['Score'];
	}
	
	$emScore_m = 0;
	$sql = "SELECT Score FROM QuestionTest WHERE UserId='$uid' AND Date <= '$month_before_str' ORDER BY Timestamp DESC LIMIT 1";
	$result = mysql_query($sql);
	if ($result){
		if($row = mysql_fetch_array($result))
			$emScore_m = $row['Score'];
	}

	$questionScore = $emScore-$emScore_m;

	//CopingSkill
	$rScore = 0;
	$sql = "SELECT Score FROM CopingSkill WHERE UserId='$uid' ORDER BY Timestamp DESC LIMIT 1";
	$result = mysql_query($sql);
	if ($result){
		if($row = mysql_fetch_array($result))
			$rScore = $row['Score'];
	}
	
	$rScore_m = 0;
	$sql = "SELECT Score FROM CopingSkill WHERE UserId='$uid' AND Date <= '$month_before_str' ORDER BY Timestamp DESC LIMIT 1";
	$result = mysql_query($sql);
	if ($result){
		if($row = mysql_fetch_array($result))
			$rScore_m = $row['Score'];
	}


	$copingScore = $rScore-$rScore_m;

	$totalScore = $testResultScore+$noteAddScore+$questionScore+$copingScore;

	$interval_begin_to_today =  $join_date_date_time->diff($today_date_time);
	$interval_month_to_today =  $month_before_date_time->diff($today_date_time);
	$mt = $interval_month_to_today->format('%R%a');
	$bj = $interval_begin_to_today->format('%R%a')+1;
	$total_day = min($mt,$bj);

	if ($total_day > 0){
		$totalScore = floor($totalScore * 100 / $total_day);
		$data = array($uid,$totalScore);
		array_push($userStateArray,$data);
	}
}

mysql_close($conn);
//return json object.
echo json_encode($userStateArray);

?>
