<?php

include("connect_db.php");
include("score_utility.php");


   // read clickLog.map
   $idMap = array();
   $mapFH = fopen('clickLog.map', 'r');
   while(!feof($mapFH)){
      $line = fgets($mapFH);
      $token_text = strtok($line, " \n\t=;");
      $token_id = strtok(" \n\t=;");
      $idMap[$token_id] = $token_text;
   }

var_dump($idMap);
?>
