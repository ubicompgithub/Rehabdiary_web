<?php

require_once("debug.php");

// functions for calculating patient score
// be sure mysql is connected before using

// get latest score of a patient in a database table
// notice the SQL injection problem
function getLatestScoreTable($userId, $table, $before_timestamp = NULL){
   
   $score = 0;

   if($before_timestamp == NULL) $query_date = "";
   else $query_date = "AND  `Timestamp` <  $before_timestamp ";
   $query = "SELECT * FROM  `$table` WHERE  `UserId` =  '$userId' $query_date ORDER BY  `$table`.`id` DESC LIMIT 0 , 1";

   $result = mysql_query($query);
   $num_rows = mysql_num_rows($result);

   if($num_rows == 0) return 0;
   else{
      $row = mysql_fetch_assoc($result);
      return $row['Score'];
   }
}

function getLatestScore($userId, $before_timestamp = NULL){
   $tables = array("NoteAdd", "TestResult", "QuestionTest", "CopingSkill");
   $score = 0;

   foreach($tables as $table){
      $score += getLatestScoreTable($userId, $table, $before_timestamp);
   }
   
   return $score;
}

?>
