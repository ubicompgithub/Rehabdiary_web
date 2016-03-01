<?php


if ( isset($_POST["uid"]) && isset($_POST["regId"])) {
	$gcm_regid = $_POST["regId"]; // GCM Registration ID
	$uid = $_POST["uid"];
	$gcm_regid = preg_replace('/\s\s+/','',$gcm_regid);
	$uid = preg_replace('/\s\s+/','',$uid);

	include_once('../connect_db.php');
	$dbhandle = connect_to_db(); 

	$sql = "UPDATE Alcoholic SET GCM_Id='$gcm_regid' WHERE UserId='$uid'";
	$result = mysql_query($sql);
	if(!$result){
		die("failed");
	}


	mysql_close($dbhandle);
	echo "upload success";
}

?>
