<?php

if (isset($_POST["uid"]) && isset($_POST["message"])) {
	$uid = $_POST["uid"];
	$message = $_POST["message"];


	include_once('../connect_db.php');
	$dbhandle = connect_to_db();
	$length = sizeof($uid);
	if ($length==0)
		die('no selection');
	for($i=0;$i<$length;++$i){
	
		echo "<br/>$uid[$i] : $message<br/>";
		$sql = "SELECT * FROM Alcoholic WHERE UserId='$uid[$i]'";
		$result = mysql_query($sql);
		$row = mysql_fetch_array($result);
		if ($row){
			$regId = $row['GCM_Id'];
		}else{
			echo "Cannot find the target uid $uid[$i]";
		}
		include_once './GCM.php';
		$gcm = new GCM();
		$registatoin_ids = array($regId);
		$message.="\n\n戒酒小幫手團隊\n";
		$datetime = date('Y-m-d H:i');
		$message.=$datetime;
		$msg = array("gcm_message" => $message);


		$gcm_result = $gcm->send_notification($registatoin_ids, $msg);
	}
}
?>
