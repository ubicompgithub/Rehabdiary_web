<?php

   // utility functions

   // day_diff("2014-01-23", "2014-01-22") => 2
   function day_diff($day1, $day2){
      $day1_ = new Datetime($day1);
      $day2_ = new Datetime($day2);
      $interval = $day1_->diff($day2_, true); // always positive
      $diff = $interval->format('%a') + 1;
      return $diff;
   }

   // new version for 'IsPrime'
   // input: { timestamp: record, ...}
   // ouput: { date: { blockId: record, ...}, ...} and each time block has only the prime record
   function detection_prime_person($detections){
      $detections_valid = array();
      foreach($detections as $timestamp => $record){
         $record_date = str_replace("-", "/", $record["Date"]);
       //  $record["Brac"] = (float)$record["Brac"];
         if($record["IsPrime"])
            $detections_valid[$record_date][$record["TimeSlot"]+1] = $record;
      }
      return $detections_valid;
   }

   // input: { UserId: {timestamp: record, ...}, ...}
   // ouput: { UserId: {date: { blockId: record, ...}, ...}, ...} and each time block has only the prime record
   function detection_prime($detections){
      $detections_valid = array();
      foreach($detections as $UserId => $records){
         $records_valid = detection_prime_person($records, $time_blocks);
         $detections_valid[$UserId] = $records_valid;
      }
      return $detections_valid;
   }

   // input: { timestamp: record, ...}, { blockId: TimeBlock, ...}
   // ouput: { date: { blockId: record, ...}, ...} and each time block has only the first record
   // assumptions: $time_blocks is sorted by 'End' and blocks do not overlap
   // any record with 'Date' and 'Time' is valid to use this function (e.g. Questionnaire, EmotionDIY, EmotionManage, Storytelling, ...)
   function detection_validate_person($detections, $time_blocks){
      $detections_valid = array();
      foreach($detections as $timestamp => $record){

         $index = -1;
         $hour = (int)($record["Time"].substr(0, 2));
         foreach($time_blocks as $id => $time_block){
            if($hour <= $time_block['End']){
               $index = $id;
               break;
            }
         }
         if($index == -1) continue; // data does not make sense

         $record_date = str_replace("-", "/", $record["Date"]);

         $record["Brac"] = (float)$record["Brac"]; // in order to eliminate redundant digits

         if(    !array_key_exists($record_date, $detections_valid)            // first record in this day
             || !array_key_exists($index, $detections_valid[$record_date]) ){ // first record in this time block
            $detections_valid[$record_date][$index] = $record;
         }
         else continue; // there is a record in the time block
      }
      return $detections_valid;
   }

   // input: { UserId: {timestamp: record, ...}, ...} , { blockId: TimeBlock, ...}
   // ouput: { UserId: {date: { blockId: record, ...}, ...}, ...} and each time block has only the first record
   // assumptions: $time_blocks is sorted by 'End' and blocks do not overlap
   // any record with 'Date' and 'Time' is valid to use this function (e.g. Questionnaire, EmotionDIY, EmotionManage, Storytelling, ...)
   function detection_validate($detections, $time_blocks){
      $detections_valid = array();
      foreach($detections as $UserId => $records){
         $records_valid = detection_validate_person($records, $time_blocks);
         $detections_valid[$UserId] = $records_valid;
      }
      return $detections_valid;
   }

   // find the extra debug information for UserId & timestamp
   function get_detection_debug($UserId, $Timestamp){
      include_once('db_utility.php');
      $testdetail = getTableData($uid, "TestDetail");

      $debug = array();
      $debug["FirstVoltage"] =  $testdetail[$Timestamp]["FirstVoltage"] ;
      $debug["SecondVoltage"]   = $testdetail[$Timestamp]["SecondVoltage"] ;
     // $debug["avg_pressure"] = $debug_msg[2];
     // $debug["min_pressure"] = $debug_msg[3];
     // $debug["init_voltage"] = $debug_msg[4];
      
      return $debug;
   }
?>
