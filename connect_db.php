<?php
function connect_to_db(){
	require_once('db_config.php');
	$connection = mysql_connect($DB_HOST,$SQL_ACC,$SQL_PWD);
	if(!$connection){
		die('Fail to connect to the database host');
	}
	mysql_query("SET NAMES utf8",$connection);
	mysql_query("SET CHARACTER_SET_CLIENT=utf8",$connection);
	mysql_query("SET CHARACTER_SET_RESULTS=utf8",$connection);
	mysql_select_db($DB,$connection) or die("Fail to access ".$DB);
	return $connection;
}
?>
