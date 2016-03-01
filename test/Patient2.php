<?php

// Get the user id
$uid = $_POST['uid'];

$joinDate = date("Y-m-d");
$joinDate = $_POST['userData'][0];
//$joinDate = date("Y-m-d");

$DeviceId = "unknown";
$DeviceId = $_POST['userData'][1];

$usedCounter = $_POST['userData'][2];
$App = $_POST['userData'][3];
$week = 0;
$week =$_POST['userData'][4];

$position = $_POST['userData'][5];
$point    = $_POST['userData'][6];

include('../connect_db.php');
$dbhandle = connect_to_db();

$datetime = date("Y-m-d H:i:s");
echo $uid."\n";

$sql = "SELECT UserId FROM Patient WHERE UserId = '$uid' LIMIT 1";
$result = mysql_query($sql);
$row = mysql_fetch_array($result);
if ($row) {
	echo "update user\n";
	$uidOld = $row['UserId'];
	$devIdOld = $row['DeviceId'];
	if ($devIdOld <> $DeviceId){
		$sql = "UPDATE Patient SET DeviceId = '$DeviceId' WHERE UserId = '$uidOld'";
		$result = mysql_query($sql);
		if (!$result)
			die('fail 0');
	}
}
else{
	echo "add new user\n";
	$sql = "INSERT INTO Patient (UserId,JoinDate) VALUES ('$uid','$joinDate')";
	$result = mysql_query($sql);
	if (!$result)
		die('fail 1');
}

$sql = "UPDATE Patient SET JoinDate = '$joinDate', ConnectionCheckTime = '".$datetime."', AppVersion = '".$App."', Week = '$week', Position = '$position', TotalScore = '$point' WHERE UserId= '".$uid."'";
$result = mysql_query($sql);
if (!$result)
	die('fail');

	$sql = "UPDATE Patient SET UsedScore = $usedCounter WHERE UserId='$uid'";
	$result = mysql_query($sql);
if (!$result)
	die('fail');


	mysql_close($dbhandle);
	echo "upload success";

	?>

