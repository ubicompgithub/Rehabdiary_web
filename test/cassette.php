<?php

header('Content-type:application/json');

include_once('../connect_db.php');
$conn = connect_to_db();

$sql="SELECT * FROM Saliva ";
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
	$isUsed = intval($row['IsUsed']);
    $cassetteId = $row['CassetteId'];
	
	$data = array($cassetteId, $isUsed);
	array_push($userStateArray,$data);
	
}

mysql_close($conn);
//return json object.
echo json_encode($userStateArray);

?>
