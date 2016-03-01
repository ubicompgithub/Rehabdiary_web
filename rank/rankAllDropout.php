<?php

header('Content-type:application/json');

include_once('../connect_db.php');
$conn = connect_to_db();

$sql="SELECT UserId, JoinDate FROM Alcoholic WHERE DropOut = 1 AND UserId LIKE 'sober_0%'";
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
$month_before->sub(new DateInterval('P28D'));
$month_before_str = $month_before->format('Y-m-d');
$month_before_date_time = new DateTime($month_before_str);

while ($row = mysql_fetch_array($resultAll)){
	$uid = $row['UserId'];
	$joinDate = $row['JoinDate'];
	$join_date_date_time = new DateTime($joinDate);
	$interval = $join_date_date_time->diff($today_date_time);
	$day_diff = $interval->format('%d');
	
	//Detection
	$dScore = 0;
	$sql = "SELECT Score FROM Detection WHERE UserId='$uid' ORDER BY Timestamp DESC LIMIT 1";
	$result = mysql_query($sql);
	if ($result){
		if($row = mysql_fetch_array($result))
			$dScore = $row['Score'];
	}
	
	$dScore_m = 0;
	$sql = "SELECT Score FROM Detection WHERE UserId='$uid' AND Date <= '$month_before_str' ORDER BY Timestamp DESC LIMIT 1";
	$result = mysql_query($sql);
	if ($result){
		if($row = mysql_fetch_array($result))
			$dScore_m = $row['Score'];
	}

	$detectionScore = $dScore-$dScore_m;
	//Advice
	$diyScore = 0;
	$sql = "SELECT Score FROM EmotionDIY WHERE UserId='$uid' ORDER BY Timestamp DESC LIMIT 1";
	$result = mysql_query($sql);
	if ($result){
		if($row = mysql_fetch_array($result))
			$diyScore = $row['Score'];
	}
	
	$diyScore_m = 0;
	$sql = "SELECT Score FROM EmotionDIY WHERE UserId='$uid' AND Date <= '$month_before_str' ORDER BY Timestamp DESC LIMIT 1";
	$result = mysql_query($sql);
	if ($result){
		if($row = mysql_fetch_array($result))
			$diyScore_m = $row['Score'];
	}

	$emotionDIYScore = $diyScore - $diyScore_m;

	$quesScore = 0;
	$sql = "SELECT Score FROM Questionnaire WHERE UserId='$uid' ORDER BY Timestamp DESC LIMIT 1";
	$result = mysql_query($sql);
	if ($result){
		if($row = mysql_fetch_array($result))
			$quesScore = $row['Score'];
	}
	
	$quesScore_m = 0;
	$sql = "SELECT Score FROM Questionnaire WHERE UserId='$uid' AND Date <= '$month_before_str' ORDER BY Timestamp DESC LIMIT 1";
	$result = mysql_query($sql);
	if ($result){
		if($row = mysql_fetch_array($result))
			$quesScore_m = $row['Score'];
	}

	$questionnaireScore = $quesScore - $quesScore_m;

	$adviceScore = $emotionDIYScore + $questionnaireScore;


	//Manage
	$emScore = 0;
	$sql = "SELECT Score FROM EmotionManagement WHERE UserId='$uid' ORDER BY Timestamp DESC LIMIT 1";
	$result = mysql_query($sql);
	if ($result){
		if($row = mysql_fetch_array($result))
			$emScore = $row['Score'];
	}
	
	$emScore_m = 0;
	$sql = "SELECT Score FROM EmotionManagement WHERE UserId='$uid' AND Date <= '$month_before_str' ORDER BY Timestamp DESC LIMIT 1";
	$result = mysql_query($sql);
	if ($result){
		if($row = mysql_fetch_array($result))
			$emScore_m = $row['Score'];
	}

	$emotionManagementScore = $emScore - $emScore_m;

	$vScore = 0;
	$sql = "SELECT Score FROM StorytellingRecord WHERE UserId='$uid' ORDER BY Timestamp DESC LIMIT 1";
	$result = mysql_query($sql);
	if ($result){
		if($row = mysql_fetch_array($result))
			$vScore = $row['Score'];
	}
	
	$vScore_m = 0;
	$sql = "SELECT Score FROM StorytellingRecord WHERE UserId='$uid' AND Date <= '$month_before_str' ORDER BY Timestamp DESC LIMIT 1";
	$result = mysql_query($sql);
	if ($result){
		if($row = mysql_fetch_array($result))
			$vScore_m = $row['Score'];
	}

	$voiceRecordScore = $vScore - $vScore_m;

	$aScore = 0;
	$sql = "SELECT Score FROM AdditionalQuestionnaire WHERE UserId='$uid' ORDER BY Timestamp DESC LIMIT 1";
	$result = mysql_query($sql);
	if ($result){
		if($row = mysql_fetch_array($result))
			$aScore = $row['Score'];
	}
	
	$aScore_m = 0;
	$sql = "SELECT Score FROM AdditionalQuestionnaire  WHERE UserId='$uid' AND Date <= '$month_before_str' ORDER BY Timestamp DESC LIMIT 1";
	$result = mysql_query($sql);
	if ($result){
		if($row = mysql_fetch_array($result))
			$aScore_m = $row['Score'];
	}

	$additionalQuestionScore = $aScore - $aScore_m;
	$selfManagementScore = $emotionManagementScore + $voiceRecordScore;
	$manageScore = $selfManagementScore + $additionalQuestionScore;

	//Story
	$rScore = 0;
	$sql = "SELECT Score FROM StorytellingReading WHERE UserId='$uid' ORDER BY Timestamp DESC LIMIT 1";
	$result = mysql_query($sql);
	if ($result){
		if($row = mysql_fetch_array($result))
			$rScore = $row['Score'];
	}
	
	$rScore_m = 0;
	$sql = "SELECT Score FROM StorytellingReading  WHERE UserId='$uid' AND Date <= '$month_before_str' ORDER BY Timestamp DESC LIMIT 1";
	$result = mysql_query($sql);
	if ($result){
		if($row = mysql_fetch_array($result))
			$rScore_m = $row['Score'];
	}

	$readingScore = $rScore - $rScore_m;

	$tScore = 0;
	$sql = "SELECT Score FROM StorytellingTest WHERE UserId='$uid' ORDER BY Timestamp DESC LIMIT 1";
	$result = mysql_query($sql);
	if ($result){
		if($row = mysql_fetch_array($result))
			$tScore = $row['Score'];
	}
	
	$tScore_m = 0;
	$sql = "SELECT Score FROM StorytellingTest  WHERE UserId='$uid' AND Date <= '$month_before_str' ORDER BY Timestamp DESC LIMIT 1";
	$result = mysql_query($sql);
	if ($result){
		if($row = mysql_fetch_array($result))
			$tScore_m = $row['Score'];
	}

	$testScore = $tScore - $tScore_m;

	$fScore = 0;
	$sql = "SELECT Score FROM Facebook WHERE UserId='$uid' ORDER BY Timestamp DESC LIMIT 1";
	$result = mysql_query($sql);
	if ($result){
		if($row = mysql_fetch_array($result))
			$fScore = $row['Score'];
	}
	
	$fScore_m = 0;
	$sql = "SELECT Score FROM Facebook WHERE UserId='$uid' AND Date <= '$month_before_str' ORDER BY Timestamp DESC LIMIT 1";
	$result = mysql_query($sql);
	if ($result){
		if($row = mysql_fetch_array($result))
			$fScore_m = $row['Score'];
	}

	$fbScore = $fScore - $fScore_m;

	$storyScore = $readingScore + $testScore + $fbScore;

	$totalScore = $detectionScore+$adviceScore+$manageScore+$storyScore;

	$interval_begin_to_today =  $join_date_date_time->diff($today_date_time);
	$interval_month_to_today =  $month_before_date_time->diff($today_date_time);
	$mt = $interval_month_to_today->format('%R%a');
	$bj = $interval_begin_to_today->format('%R%a')+1;
	$total_day = min($mt,$bj);

	if ($total_day > 0){
		$totalScore = floor($totalScore * 100 / $total_day);
		$detectionScore = floor($detectionScore * 100 / $total_day);
		$adviceScore = floor($adviceScore * 100 / $total_day);
		$manageScore = floor($manageScore * 100 / $total_day);
		$storyScore = floor($storyScore * 100 / $total_day);

		$questionnaireScore = floor($questionnaireScore*100/$total_day);
		$emotionDIYScore = floor($emotionDIYScore*100/$total_day);
	
		$voiceRecordScore = floor($voiceRecordScore*100/$total_day);	
		$emotionManagementScore = floor($emotionManagementScore*100/$total_day);
		$additionalQuestionScore = floor($additionalQuestionScore*100/$total_day);

		$readingScore = floor($readingScore*100/$total_day);
		$testScore = floor($testScore*100/$total_day);
		$fbScore = floor($fbScore*100/$total_day);

		$data = array($uid,$totalScore,$detectionScore,$adviceScore,$manageScore,$storyScore, $questionnaireScore, $emotionDIYScore, $voiceRecordScore, $emotionManagementScore, $additionalQuestionScore, $readingScore, $testScore, $fbScore);
		array_push($userStateArray,$data);
	}
}

mysql_close($conn);
//return json object.
echo json_encode($userStateArray);

?>
