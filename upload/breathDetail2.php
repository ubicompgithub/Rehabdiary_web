<?php
	
	$uid = $_POST['uid'];
	$timestamp = $_POST['data'][0];
	$blowStartTimes = $_POST['data'][1];
	$blowBreakTimes = $_POST['data'][2];
	$pressureDiffMax = $_POST['data'][3];
	$pressureMin = $_POST['data'][4];
	$pressureAverage = $_POST['data'][5];
	$voltageInit = $_POST['data'][6];
	$disconnectionMillis = $_POST['data'][7];
	$serialDiffMax = $_POST['data'][8];
	$serialDiffAverage = $_POST['data'][9];
	$sensorId = $_POST['data'][10];

	$timestamp_in_sec = $timestamp/1000;
	$date = date('Y-m-d', $timestamp_in_sec);
	$time = date('H:i:s', $timestamp_in_sec);

	include_once('../connect_db.php');
	$dbhandle = connect_to_db();

	$sensorId = mysql_real_escape_string($sensorId);	
	$sql = "INSERT INTO BreathDetail (UserId,Date,Time,Timestamp,BlowStartTimes,BlowBreakTimes,PressureDiffMax,PressureMin,PressureAverage,VoltageInit,DisconnectionMillis,SerialDiffMax,SerialDiffAverage, SensorId) VALUES ('$uid','$date','$time',$timestamp,$blowStartTimes,$blowBreakTimes,$pressureDiffMax,$pressureMin,$pressureAverage,$voltageInit,$disconnectionMillis,$serialDiffMax,$serialDiffAverage, '{$sensorId}')";
	$result = mysql_query($sql);
	if (!$result){
		echo $sql;
		die("invalid mysql query");
	}
	mysql_close($dbhandle);
	echo "upload success";
?>

