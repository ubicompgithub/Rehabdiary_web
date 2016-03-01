<?php

$uid = $_POST['uid'];
$deviceId = $_POST['data'][0];
$timestamp = $_POST['data'][2];
$cassetteId = $_POST['data'][1];
//$cassetteId = "saliva_".$timestamp;
$failedState = $_POST['data'][3];
$firstVoltage = $_POST['data'][4];
$secondVoltage = $_POST['data'][5];
$devicePower = $_POST['data'][6];
$colorReading = $_POST['data'][7];
$connectionFailRate = $_POST['data'][8];
$failedReason = $_POST['data'][9];
$week = $_POST['data'][10];
$hardwareVersion = $_POST['data'][11];
$appVersion = $_POST['data'][12];

$timestamp_in_sec = $timestamp/1000;
$date = date('Y-m-d', $timestamp_in_sec);
$time = date('H:i:s', $timestamp_in_sec);

include_once('../connect_db.php');
$dbhandle = connect_to_db();

$sensorId = mysql_real_escape_string($sensorId);	
$deviceId = mysql_real_escape_string($deviceId);
$cassetteId = mysql_real_escape_string($cassetteId);
$failedReason = mysql_real_escape_string($failedReason);

$sql = "INSERT INTO TestDetail (UserId,Date,Time,Timestamp,DeviceId,
	CassetteId, FailedState, FirstVoltage, SecondVoltage, DevicePower,
	ColorReading, ConnectionFailRate, FailedReason,Week, HardwareVersion, AppVersion) VALUES 
	('$uid','$date','$time',$timestamp,'{$deviceId}', '{$cassetteId}', $failedState,
	 $firstVoltage, $secondVoltage, $devicePower, $colorReading, $connectionFailRate,
	 '{$failedReason}',$week, '$hardwareVersion', '$appVersion')";
$result = mysql_query($sql);
if (!$result){
	echo $sql;
	die("invalid mysql query");
}
echo $sql;

if($failedState >= 4 && $firstVoltage > 110){    
	$isUsed = 1;


	$sql = "SELECT * FROM Saliva WHERE CassetteId = '$cassetteId'";
	$sql_result = mysql_query($sql);
	$row = mysql_fetch_array($sql_result);
	if($row){
		$sql3 = "UPDATE Saliva SET IsUsed = 1, Date='$date', Time='$time', Timestamp='$timestamp', Week='$week', UserId='$uid' WHERE CassetteId = '$cassetteId'";
		$sql_result3 = mysql_query($sql3);
		if(!$sql_result3)
			die('invalid mysql query');
		echo $sql3;
	}
	else{

		$sql2 = "INSERT INTO Saliva (CreateDate, Date, Time, Timestamp, Week, CassetteId, IsUsed, UserId) VALUES
			('$date', '$date', '$time', $timestamp, $week, '$cassetteId', $isUsed, '$uid')";
		$result = mysql_query($sql2);
		if(!$result){
			//echo $sql2;
			die("invalid mysql query");
		}
		echo $sql2;
	}

}

/***/
	$uploadDest = '../patients/' . $uid . '/' . $timestamp;
    if (!file_exists($uploadDest)) {
        if (!mkdir($uploadDest, 0777, true)) {
            $error = error_get_last();
            echo $error['message'];
            die("Failed to create directory: " . $uploadDest);
        }
    }

    $len = count($_FILES['file']['name']);
    echo "Len: ".$len."\n";
    if ($len > 0) {
        for ($i=0; $i < $len; $i++) {
            $tmpName = $_FILES['file']['tmp_name'][$i];
            if (is_uploaded_file($tmpName)) {
                $fname = basename($_FILES['file']['name'][$i]);
                if (!move_uploaded_file($tmpName, $uploadDest . "/" . $fname)) {
                    die("Fail to move the files\n");
                }
            } else {
                die("No upload file exists\n");
            }
        }
    } else {
        die("No upload file\n");
    }
/***/

mysql_close($dbhandle);
echo "upload success";

?>

