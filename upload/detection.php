<?php
	
	$uid = $_POST['uid'];
	
	$timestamp = $_POST['data'][0];
	$week = $_POST['data'][1];
	$emotion = $_POST['data'][2];
	$craving = $_POST['data'][3];
	$isPrime = $_POST['data'][4];
	$weeklyScore = $_POST['data'][5];
	$score = $_POST['data'][6];

	$uploadDest = '../patients/' . $uid . '/' . $timestamp;
	if (!file_exists($uploadDest)) {
		if (!mkdir($uploadDest, 0777, true)) {
			die("Failed to create directory: " . $uploadDest);
		}
	}
	$len = count($_FILES['file']['name']);
	if ($len > 0) {
		for ($i=0; $i < $len; $i++) {
			$tmpName = $_FILES['file']['tmp_name'][$i];
			if (is_uploaded_file($tmpName)) {
				$fname = basename($_FILES['file']['name'][$i]);
				if (!move_uploaded_file($tmpName, $uploadDest . "/" . $fname)) {
					die("Fail to move the files");
				}
			} else {
				die("No upload file exists");
			}
		}
	} else {
		die("No upload file");
	}
	
	include_once('../connect_db.php');
	$dbhandle = connect_to_db();

	$timestamp_in_sec = $timestamp/1000;	
	$date = date('Y-m-d', $timestamp_in_sec);
	$time = date('H:i:s', $timestamp_in_sec);
	$hr = intval(date('H', $timestamp_in_sec));

	// Determine time slot

	$time_slot = 0;
	if (0 <= $hr && $hr < 12) {
		$time_slot = 0;
	} else if (12 <= $hr && $hr < 20) {
		$time_slot = 1;
	} else if (20 <= $hr && $hr < 24) {
		$time_slot = 2;
	}

	$datafile = $uploadDest.'/'.$timestamp.'.txt';
	$geofile = $uploadDest.'/'.'geo.txt';
	$detailfile = $uploadDest.'/detection_detail.txt';
	$imgfilesob = $uploadDest.'/'.'IMG_'.$timestamp.'_1.sob';
	$imgfile = $uploadDest.'/'.'IMG_'.$timestamp.'_1.jpg';
	$imgfilesob2 = $uploadDest.'/'.'IMG_'.$timestamp.'_2.sob';
	$imgfile2 = $uploadDest.'/'.'IMG_'.$timestamp.'_2.jpg';
	$imgfilesob3 = $uploadDest.'/'.'IMG_'.$timestamp.'_3.sob';
	$imgfile3 = $uploadDest.'/'.'IMG_'.$timestamp.'_3.jpg';

	// Calculate the average BRAC valuei
	$brac = 0;
	$brac_arr=array();
	if (file_exists($datafile)){
		if (fopen($datafile, "rb")) {
			$filehandle = fopen($datafile, "rb");
			while (!feof($filehandle)) {
				$line = fgets($filehandle);
				$tmp = explode("	", $line);
				if (count($tmp) == 2) {
					if (count ($brac_arr)==0)
						$brac_arr = array(floatval($tmp[1]));
					else			
						array_push($brac_arr,floatval($tmp[1]));
				}
			}
			rsort($brac_arr);
			$middle_idx = round(count($brac_arr)/2);
			if(sizeof($brac_arr)%2==1)
				$middle_idx-=1;
			fclose($filehandle);
			$brac = $brac_arr[$middle_idx];
			$brac = substr($brac, 0, 5);
		} else {
			die ("Data file not opened");
		}
	}else{
		die ("Data file not found");
	}
	
	// Get the position information
	$latitude = NULL;
	$longitude = NULL;
	$hasGeo = false;
	if (file_exists($geofile)){
		if (fopen($geofile, "rb")) {
			$geohandle = fopen($geofile, "rb");
			while (!feof($geohandle)) {
				$line = fgets($geohandle);
				$tmp = explode("	", $line);
				if (count($tmp)==2){
					$latitude = $tmp[0];
					$longitude = $tmp[1];
				}
			}
			fclose($geohandle);
			$hasGeo = true;
		} 
	}

	if (file_exists($imgfilesob))	
		rename($imgfilesob,$imgfile);
	if (file_exists($imgfilesob2))	
		rename($imgfilesob2,$imgfile2);
	if (file_exists($imgfilesob3))	
		rename($imgfilesob3,$imgfile3);

	if (file_exists($imgfile)&&file_exists($imgfile2)&&file_exists($imgfile3)){
	}else{
		die('no snapshots');
	}

	if (!file_exists($detailfile)){
		die('no detail file');
	}

	if (!$hasGeo || $latitude==NULL || $longitude==NULL){
		$sql = "INSERT INTO Detection (UserId,Brac,Date,Time,Timestamp,Week,TimeSlot,Emotion,Craving,isPrime,WeeklyScore,Score) VALUES('$uid',$brac,'$date','$time',$timestamp,$week,$time_slot,$emotion,$craving,$isPrime,$weeklyScore,$score)";
		$result = mysql_query($sql);
		echo "\n$sql\n";
		if (!$result)
			die ('invalid query(no Geo)');
	}else{
		$sql = "INSERT INTO Detection (UserId,Brac,Date,Time,Timestamp,Week,TimeSlot,Emotion,Craving,isPrime,WeeklyScore,Score,Latitude,Longitude) VALUES('$uid',$brac,'$date','$time',$timestamp,$week,$time_slot,$emotion,$craving,$isPrime,$weeklyScore,$score,$latitude,$longitude)";
		$result = mysql_query($sql);
		echo "\n$sql\n";
		if (!$result)
			die ('invalid query(Geo)');
	}
	
	mysql_close($dbhandle);

	echo 'upload success';
?>
