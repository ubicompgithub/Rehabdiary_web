<?php

include_once('email_utility.php');

// find all the click sequence raw data and transform to .log file by ClickLogParser
// will not be used from client ( due to permission issues )
function parse_patient_clickLogs($uid){
   
   $path = "patients_clicklog/".$uid."/";
   if( $dir = opendir($path) ){
      while(false !== ($entry = readdir($dir))){
         if($entry != "." && $entry != ".."){
            if(substr($entry, -4, 4) == ".txt"){
               $entry_log = substr($entry, 0, -4).".log";
               if(file_exists($path.$entry_log)) // has been proccessed before
                  continue;
               else{
                  exec("java ClickLogParser ".$path.$entry." ".$path.$entry_log);
                  exec('echo "parsed '.$path.$entry.'" >> daily_clickLog_parser_log.txt');
               }
            }
         }
      }
      closedir($dir);
   }

}

function get_patient_clickLogs($uid){

   // read clickLog.map
   $idMap = array();
   $mapFH = fopen('clickLog.map', 'r');
   while(!feof($mapFH)){
      $line = fgets($mapFH);
      $token_text = strtok($line, " \n\t=;");
      $token_id = strtok(" \n\t=;");
      $idMap[$token_id] = $token_text;
   }

   // find all .log file
   $clickLogs = array();
   $path = "patients_clicklog/".$uid."/";
   if( $dir = opendir($path) ){
      while(false !== ($entry = readdir($dir))){
         if($entry != "." && $entry != ".."){
            if(substr($entry, -4, 4) == ".log"){
               $date = substr($entry, 0, -4);
               $openFH = fopen($path.$entry, 'r');
               while(!feof($openFH)){
                  $line = fgets($openFH);
                  $line = str_replace("\n", "", $line);
                  $data = explode("\t", $line);
                  $timestamp = $data[0];
                  if(strlen($timestamp) != 13) // because of previous format
                     break;

                  $timestamp_ms = substr($timestamp, -3);
                  $timestamp = substr($timestamp, 0, -3); // raw data is in ms
                  $clickId = $data[1];
                  $datetime = date('Y-m-d H:i:s', $timestamp).$timestamp_ms;
                  if(array_key_exists($clickId, $idMap))
                     $clickLogs[$datetime] = $idMap[$clickId];
                  else{
                     if(substr($clickId, 0, 3) == "305"){
                        $clickLogs[$datetime] = $idMap["30500000"]." (".substr($clickId, 4, 4).")";
                     }
                     else if(substr($clickId, 0, 2) == "01"){
                        $clickLogs[$datetime] = $idMap[substr($clickId, 0, 4)."0000"]." (".substr($clickId, 4, 4).")";
                     }
                     else{
                        $clickLogs[$datetime] = "unknown: ".$clickId;
      //                  send_email(array("b98902090@ntu.edu.tw"), "Unknown Clicklog: $clickId", "Solve it!");
                     }
                  }
               }
            }
         }
      }
      closedir($dir);
   }

   return $clickLogs;
}

// count the start/restart click times of $uid in $date.
// Ex. countStartRestart('Eric', '2013_09_24') => return {'start': 3, 'restart': 4};
function countStartRestart($uid, $date){

   // read clickLog.map
   $idMap = array();
   $mapFH = fopen('clickLog.map', 'r');
   while(!feof($mapFH)){
      $line = fgets($mapFH);
      $token_text = strtok($line, " \n\t=;");
      $token_id = strtok(" \n\t=;");
      $idMap[$token_id] = $token_text;
   }

   // find the .log file
   $result = array();
   $result['start'] = 0;
   $result['restart'] = 0;
   $file = "patients_clicklog/".$uid."/".$date.".log";
   $openFH = fopen($file, 'r');
   if(!$openFH){
      $result['start'] = -1;
      $result['restart'] = -1;
      return $result;
   }

   while(!feof($openFH)){
      $line = fgets($openFH);
      $line = str_replace("\n", "", $line);
      $data = explode("\t", $line);
      $timestamp = $data[0];
      if(strlen($timestamp) != 13) // because of previous format
         break;

      $clickId = $data[1];
      $text = $idMap[$clickId];
      if($text == "TEST_START_BUTTON")   $result['start'] = $result['start'] + 1;
      if($text == "TEST_RESTART_BUTTON") $result['restart'] = $result['restart'] + 1;
   }
   fclose($openFH);

   return $result;   
}

?>
