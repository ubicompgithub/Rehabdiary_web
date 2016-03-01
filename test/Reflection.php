<?php

$uid = $_POST['uid'];
$timestamp = $_POST['data'][0];
$action = $_POST['data'][1];
$feeling = $_POST['data'][2];
$thinking = $_POST['data'][3];
$key = $_POST['data'][4];

$timestamp_in_sec = $timestamp/1000;
$date = date('Y-m-d', $timestamp_in_sec);
$time = date('H:i:s', $timestamp_in_sec);
$week = 0;

include_once('../connect_db.php');
$dbhandle = connect_to_db();

$description = mysql_real_escape_string($description); //prevent SQL query error

$sql = "INSERT INTO Reflection (UserId,Date,Time,Timestamp,Week,ReflectionDate,ReflectionTime,ExpectionAction,ExpectionFeeling,ExpectionThinking,RelationKey) VALUES ('$uid','$date','$time',$timestamp,$week,'$date','$time','{$action}','{$feeling}','{$thinking}',$key)";
    $result = mysql_query($sql);
    if (!$result){
        echo $sql;
        die("invalid mysql query");
    }
mysql_close($dbhandle);
echo "upload success";
?>