<?php

// utility functions for database
// be sure mysql is connected before using

// notice the SQL injection
function getTableData($userId, $table_name){
   $query = "SELECT * FROM $table_name WHERE `UserId` = '$userId' ORDER BY `Timestamp` ASC";
   $result = mysql_query($query);
   $data = array();
   while($row = mysql_fetch_assoc($result))
      $data[$row["Timestamp"]] = $row;
   return $data;
}

function getTableData_date($userId, $table_name){
   $query = "SELECT * FROM $table_name WHERE `UserId` = '$userId' ORDER BY `RecordDate` ASC";
   $result = mysql_query($query);
   $data = array();
   while($row = mysql_fetch_assoc($result))
      $data[$row["RecordDate"]][$row["Timestamp"]] = $row;
   return $data;
}

function getTableData_detection_date($userId, $table_name){
   $query = "SELECT * FROM $table_name WHERE `UserId` = '$userId' ORDER BY `Timestamp` ASC";
   $result = mysql_query($query);
   $data = array();
   while($row = mysql_fetch_assoc($result))
      $data[$row["Date"]][$row["Timestamp"]] = $row;
   return $data;
}
function getTableData_TriggerItems($userId, $table_name){
   $query = "SELECT * FROM $table_name WHERE `UserId` = '$userId' ORDER BY `Items` ASC";
   $result = mysql_query($query);
   $data = array();
   while($row = mysql_fetch_assoc($result))
      $data[$row["Items"]][$row["Timestamp"]] = $row;
   return $data;
}
function getTableData_Item($userId, $item){
   $query = "SELECT * FROM `NoteAdd` WHERE `UserId` = '$userId' AND `Items` = '$item' ORDER BY `Timestamp` ASC";
   $result = mysql_query($query);
   $data = array();
   while($row = mysql_fetch_assoc($result))
      $data[$row["Timestamp"]] = $row;
   return $data;
}



?>
