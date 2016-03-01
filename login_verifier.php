<?php
	session_start();
	if($_POST['login']=="登入"){
		include_once('connect_db.php');
		$conn = connect_to_db();
		$ACC=substr($_POST['account'],0,12);
		$PWD=substr($_POST['pwd'],0,12);
		$TARGET=$_POST['target'];

		#prevent sql injection
		$ACC = mysql_real_escape_string($ACC);
		$PWD = mysql_real_escape_string($PWD);

		#md5 hashing
		$PWD_MD5 = md5($PWD);

		$sql="SELECT UserID, Password FROM WebUser WHERE UserID='".$ACC."' LIMIT 1";
		$result = mysql_query($sql);
		#sql error
		if(!$result){
			$header='Location:login.php?condition=fail';
			mysql_close($conn);
			header($header);
			die();
		}

		$row = mysql_fetch_array($result);

		$header='Location:login.php?condition=fail';

		if($row){
			$PWD_DB=$row['Password'];
			if ($PWD_DB==$PWD_MD5){
				$_SESSION['username']=$ACC;
				$header='Location:'.$TARGET;
			}
		}
		mysql_close($conn);
		header($header);
		die();
	}
	header('Location:login.php');
	die();
?>
