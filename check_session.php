<?php
function check_session(){
	session_start();
	$CUR_USER = $_SESSION['username'];
	if (!$CUR_USER){
		header('Location:login.php');
		die();
	}
}
function check_session_with_target($target){
	session_start();
	$CUR_USER = $_SESSION['username'];
	if (!$CUR_USER){
		header('Location:login.php?target='.$target);
		die();
	}
}
?>
