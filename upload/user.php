<?php

	// Get the user id
	$uid = $_POST['uid'];

	$joinDate = $_POST['userData'][0];
	$sensorId = "unknown";
	$sensorId = $_POST['userData'][1];
	$usedCounter = $_POST['userData'][2];
	$App = $_POST['userData'][3];

	include('../connect_db.php');
	$dbhandle = connect_to_db();

	$datetime = date("Y-m-d H:i:s");

	$sql = "SELECT UserId, DeviceId FROM Alcoholic WHERE UserId = '$uid' LIMIT 1";
	$result = mysql_query($sql);
	$row = mysql_fetch_array($result);
	if ($row) {
		echo "update user\n";
		$uidOld = $row['UserId'];
		$devIdOld = $row['DeviceId'];
		if ($devIdOld <> $sensorId){
			$sql = "UPDATE Alcoholic SET DeviceId = '$sensorId' WHERE UserId = '$uidOld'";
			$result = mysql_query($sql);
			if (!$result)
				die('fail 0');
		}
	}
	else{
		echo "add new user\n";
		$sql = "INSERT INTO Alcoholic (UserId,DeviceId,JoinDate) VALUES ('$uid','$sensorId','$joinDate')";
		$result = mysql_query($sql);
		if (!$result)
			die('fail 1');
	}

	$sql = "UPDATE Alcoholic SET ConnectionCheckTime = '".$datetime."', AppVersion = '".$App."' WHERE UserId= '".$uid."'";
	$result = mysql_query($sql);
	if (!$result)
		die('fail');
	$sql = "UPDATE Alcoholic SET UsedScore = $usedCounter WHERE UserId='$uid'";
	$result = mysql_query($sql);
	if (!$result)
		die('fail');
	mysql_close($dbhandle);
	echo "upload success";
	
?>

