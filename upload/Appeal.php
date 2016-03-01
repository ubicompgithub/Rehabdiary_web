<?php

$uid = $_POST['uid'];
$timestamp = $_POST['data'][0];
$appealType = $_POST['data'][1];
$appealTimes = $_POST['data'][2];

include_once('../connect_db.php');
$dbhandle = connect_to_db();

$sql = "INSERT INTO Appeal (UserId,Timestamp,appealType,appealTimes) 
    VALUES ('$uid',$timestamp,$appealType,$appealTimes)";

$result = mysql_query($sql);
if (!$result){
    echo $sql;
    die("invalid mysql query");
}

$uploadDest = '../patients/' . $uid . '/' . 'appeal/'. $timestamp;
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
echo $sql;