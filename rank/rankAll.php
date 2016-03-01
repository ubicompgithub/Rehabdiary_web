<?php

header('Content-type:application/json');

include_once('../connect_db.php');
$conn = connect_to_db();
$sql="SELECT UserId, JoinDate FROM Patient WHERE IsDropOut = 0 AND UserId LIKE 'rehab_%'";
//$sql="SELECT UserId, JoinDate FROM Patient WHERE IsDropOut = 0 AND UserId REGEXP 'rehab_[0-9]*'";
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

    //TestScore
    $tScore = 0;
    $sql = "SELECT COUNT(*) FROM (SELECT DISTINCT Score from TestResult WHERE UserId ='$uid') as Tmp ";
    $result = mysql_query($sql);
	if ($result){
		if($row = mysql_fetch_array($result))
			$tScore = $row['COUNT(*)'];
	}      

    $tScore_m = 0;
    $sql = "SELECT COUNT(*) FROM (SELECT DISTINCT Score from TestResult WHERE UserId ='$uid' AND Date <= '$month_before_str') as Tmp ";
    $result = mysql_query($sql);
	if ($result){
		if($row = mysql_fetch_array($result))
			$tScore_m = $row['COUNT(*)'];
	}      
    
    $testScore = $tScore - $tScore_m;
    
    //PassScore
    
     //TestScore
    $pScore = 0;
    $sql = "SELECT COUNT(*) from TestResult WHERE UserId ='$uid' AND IsPrime = 1 AND Result = 0";
    $result = mysql_query($sql);
	if ($result){
		if($row = mysql_fetch_array($result))
			$pScore = $row['COUNT(*)'];
	}      

    $pScore_m = 0;
    $sql = "SELECT COUNT(*) FROM TestResult WHERE UserId ='$uid' AND IsPrime = 1 AND Result = 0 AND Date <= '$month_before_str'";
    $result = mysql_query($sql);
	if ($result){
		if($row = mysql_fetch_array($result))
			$pScore_m = $row['COUNT(*)'];
	}      
    
    $passScore = $pScore - $pScore_m;
    
    //echo $testResultScore." ".$testScore." ".$passScore."\n"; 



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

	$noteAddScore = $diyScore - $diyScore_m;


    
	//Question
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

	$questionScore = $emScore - $emScore_m;


    //Normal Question    
	$norScore = 0;
	$sql = "SELECT COUNT(*) FROM (SELECT * FROM QuestionTest WHERE UserId = '$uid' GROUP BY Score) as Tmp WHERE Type = 0 ";
	$result = mysql_query($sql);
	if ($result){
		if($row = mysql_fetch_array($result))
			$norScore = $row['COUNT(*)'];
	}
	
	$norScore_m = 0;
	$sql = "SELECT COUNT(*) FROM (SELECT * FROM QuestionTest WHERE UserId = '$uid' GROUP BY Score) as Tmp WHERE Type = 0 AND Date<= '$month_before_str'";
	$result = mysql_query($sql);
	if ($result){
		if($row = mysql_fetch_array($result))
			$norScore_m = $row['COUNT(*)'];
	}

	$normalScore = $norScore - $norScore_m;
    
    //Random Question
	$ranScore = 0;
	$sql = "SELECT COUNT(*) FROM (SELECT * FROM QuestionTest WHERE UserId = '$uid' GROUP BY Score) as Tmp WHERE Type = 1 ";
	$result = mysql_query($sql);
	if ($result){
		if($row = mysql_fetch_array($result))
			$ranScore = $row['COUNT(*)'];
	}
	
	$ranScore_m = 0;
	$sql = "SELECT COUNT(*) FROM (SELECT * FROM QuestionTest WHERE UserId = '$uid' GROUP BY Score) as Tmp WHERE Type = 1 AND Date<= '$month_before_str'";
	$result = mysql_query($sql);
	if ($result){
		if($row = mysql_fetch_array($result))
			$ranScore_m = $row['COUNT(*)'];
	}

	$randomScore = $ranScore - $ranScore_m;
    
    //echo $questionScore." ".$normalScore." ".$randomScore."\n";

    //Coping
	$vScore = 0;
	$sql = "SELECT Score FROM CopingSkill WHERE UserId='$uid' ORDER BY Timestamp DESC LIMIT 1";
	$result = mysql_query($sql);
	if ($result){
		if($row = mysql_fetch_array($result))
			$vScore = $row['Score'];
	}
	
	$vScore_m = 0;
	$sql = "SELECT Score FROM CopingSkill WHERE UserId='$uid' AND Date <= '$month_before_str' ORDER BY Timestamp DESC LIMIT 1";
	$result = mysql_query($sql);
	if ($result){
		if($row = mysql_fetch_array($result))
			$vScore_m = $row['Score'];
	}

	$copingScore = $vScore - $vScore_m;

    /*
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

	$storyScore = $readingScore + $testScore + $fbScore;*/

	$totalScore = $testResultScore+$noteAddScore+$questionScore+$copingScore;

	$interval_begin_to_today =  $join_date_date_time->diff($today_date_time);
	$interval_month_to_today =  $month_before_date_time->diff($today_date_time);
	$mt = $interval_month_to_today->format('%R%a');
	$bj = $interval_begin_to_today->format('%R%a')+1;
	$total_day = min($mt,$bj);
	if ($total_day > 0){
		$totalScore = floor($totalScore * 100 / $total_day);
		$testResultScore = floor($testResultScore * 100 / $total_day);
		$noteAddScore = floor($noteAddScore * 100 / $total_day);
		$questionScore = floor($questionScore * 100 / $total_day);
		$copingScore = floor($copingScore * 100 / $total_day);

 		$testScore = floor($testScore * 100 / $total_day);
		$passScore = floor($passScore * 100 / $total_day);
		$normalScore = floor($normalScore * 100 / $total_day);
		$randomScore = floor($randomScore * 3 * 100 / $total_day);
		       /*
		$questionnaireScore = floor($questionnaireScore*100/$total_day);
		$emotionDIYScore = floor($emotionDIYScore*100/$total_day);
	
		$voiceRecordScore = floor($voiceRecordScore*100/$total_day);	
		$emotionManagementScore = floor($emotionManagementScore*100/$total_day);
		$additionalQuestionScore = floor($additionalQuestionScore*100/$total_day);

		$readingScore = floor($readingScore*100/$total_day);
		$testScore = floor($testScore*100/$total_day);
		$fbScore = floor($fbScore*100/$total_day);*/

		$data = array($uid,$totalScore,$testResultScore,$noteAddScore,$questionScore,$copingScore,
        $testScore, $passScore, $normalScore, $randomScore);
		array_push($userStateArray,$data);
	}
}

mysql_close($conn);
//return json object.
echo json_encode($userStateArray);

?>
