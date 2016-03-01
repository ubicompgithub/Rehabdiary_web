<?php

// Update old data  or  Insert new data and file.


$uid = $_POST['uid'];
$result=$_POST['data'][2];
$deviceId=$_POST['data'][1];
$cassetteId=$_POST['data'][3];

$timestamp = $_POST['data'][0];
$isPrime = $_POST['data'][4];
$isFilled= $_POST['data'][5];
$score   = $_POST['data'][6];
$fileNum = $_POST['data'][8];    
$week    = $_POST['data'][7];
//for upload photo 

echo "FileNum: ".$fileNum;
include_once('../connect_db.php');
$dbhandle = connect_to_db();

$sql = "SELECT Timestamp FROM TestResult WHERE Timestamp = '$timestamp' AND UserId = '$uid' LIMIT 1";
$sql_result = mysql_query($sql);
$row = mysql_fetch_array($sql_result);
if($row){      // update result
    //echo "update result";
    $sql = "UPDATE TestResult SET Result = '$result' WHERE Timestamp = '$timestamp' AND UserId = '$uid'";
    $sql_result = mysql_query($sql);
    echo "\n$sql\n";
    if(!$sql_result)
        die('fail update');
}
else{         // upload new data


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


    $timestamp_in_sec = $timestamp/1000;	
    $date = date('Y-m-d', $timestamp_in_sec);
    $time = date('H:i:s', $timestamp_in_sec);
    $hr = intval(date('H', $timestamp_in_sec));


    $time_slot = 0;
    if (0 <= $hr && $hr < 12) {
        $time_slot = 0;
    } else if (12 <= $hr && $hr < 20) {
        $time_slot = 1;
    } else if (20 <= $hr && $hr < 24) {
        $time_slot = 2;
    }


    $datafile = $uploadDest.'/voltage.txt';
    $detailfile = $uploadDest.'/color_raw.txt';
    
    $j=0;
    for($i=0; $i<$len; $i++){
        $imgfilesob = $uploadDest.'/'.'IMG_'.$timestamp.'_'.$i.'.sob';
        $imgfile = $uploadDest.'/'.'IMG_'.$timestamp.'_'.$i.'.jpg';
        if (file_exists($imgfilesob)){
            rename($imgfilesob,$imgfile);
            $j++;
        }
    }
    $k = 0;
    for($i=0; $i<$len; $i++){
        $picfilesob = $uploadDest.'/'.'PIC_'.$timestamp.'_'.$i.'.sob';
        $picfile = $uploadDest.'/'.'PIC_'.$timestamp.'_'.$i.'.jpg';
        if (file_exists($picfilesob)){	
            rename($picfilesob,$picfile);
            $k++;
        }
    }
    $dir_fileNum = count(glob("$uploadDest/*.*"));
    if($fileNum == $dir_fileNum){
        echo "Same FileNum\n";
    }
    else{
        echo "Different Num\n";
    }
    

    /*
       $imgfilesob = $uploadDest.'/'.'IMG_'.$timestamp.'_1.sob';
       $imgfile = $uploadDest.'/'.'IMG_'.$timestamp.'_1.jpg';
       $imgfilesob2 = $uploadDest.'/'.'IMG_'.$timestamp.'_2.sob';
       $imgfile2 = $uploadDest.'/'.'IMG_'.$timestamp.'_2.jpg';
       $imgfilesob3 = $uploadDest.'/'.'IMG_'.$timestamp.'_3.sob';
       $imgfile3 = $uploadDest.'/'.'IMG_'.$timestamp.'_3.jpg';


       if (file_exists($imgfilesob))	
       rename($imgfilesob,$imgfile);
       if (file_exists($imgfilesob2))	
       rename($imgfilesob2,$imgfile2);
       if (file_exists($imgfilesob3))	
       rename($imgfilesob3,$imgfile3);

       if (file_exists($imgfile)&&file_exists($imgfile2)&&file_exists($imgfile3)){
       }else{
       echo 'no snapshots';
    //die('no snapshots');
    }*/

    if (!file_exists($detailfile)){
        die('no detail file');
    }

    $sql = "SELECT * FROM Date = '$date' AND IsPrime = 1";
    $sql_result = mysql_query($sql);
    $row = mysql_fetch_array($sql_result);
    if($row){
        $sql = "UPDATE TestResult SET IsPrime = 0 WHERE Date = '$date' AND IsPrime = 1";
        $sql_result = mysql_query($sql);
        echo "\n$sql\n";
        if(!$sql_result){
            die('Update Prime Fail');
        }

    }
    else{
    	$sql = "INSERT INTO TestResult (UserId,Result,DeviceId,
            CassetteId,Date,Time,Timestamp,isPrime,isFilled,TimeSlot,Score,Week) 
                VALUES ('$uid',$result,'$deviceId', '$cassetteId','$date','$time',
                        $timestamp,$isPrime,$isFilled,$time_slot,$score,$week)";
        $sql_result = mysql_query($sql);
        echo "\n$sql\n";
        if (!$sql_result){
            die ('invalid query');
        }
    }

}
mysql_close($dbhandle);

echo 'upload success';
?>
