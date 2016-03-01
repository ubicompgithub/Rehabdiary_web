<?php

   $DRUNK_THRESHOLD = 0.05;

   include_once('utility.php');
   date_default_timezone_set('Asia/Taipei');

   //get Alcoholics data from database
   include_once('connect_db.php');
   $conn = connect_to_db();
   $query = "SELECT * FROM  Alcoholic2";
   $result_all = mysql_query($query);
   $alcoholics = array();
   while($row = mysql_fetch_assoc($result_all)){
      $alcoholics[$row["UserId"]] = $row;
   }

   //get Detections data from database in yesterday
   $yesterday = date('Y-m-d', time() - 60 * 60 * 24);
   $yesterday_slash = date('Y/m/d', time() - 60 * 60 * 24);
   $query_detection = "SELECT * FROM Detection2 WHERE `Date` = '".$yesterday."' ORDER BY `Timestamp` ASC";
   $result_detection = mysql_query($query_detection);
   $detections = array();
   while($row = mysql_fetch_assoc($result_detection)){
      $detections[$row["UserId"]][$row["Timestamp"]] = $row;
   }

   //get Block information
   $query_block = "SELECT * FROM `TimeBlock`";
   $result_block = mysql_query($query_block);
   $blocks = array();
   while($row = mysql_fetch_assoc($result_block)){
      $blocks[$row["BlockID"]] = $row;
   }

   mysql_close($conn);

   $detections_valid = detection_validate($detections, $blocks);

   $counts = array();

   // for each alcoholic 
   foreach($alcoholics as $UserId => $alcoholic){
      // for each block
      foreach($blocks as $blockId => $block){
         // if the patient had tests yesterday
         if(array_key_exists($UserId, $detections)){
            $records = $detections_valid[$UserId][$yesterday_slash];
            // if the patient had tests in this block
            if(array_key_exists($blockId, $records)){
               // the patient passed
               if(floatval($records[$blockId]['Brac']) <= $DRUNK_THRESHOLD)
                  $counts[$UserId][$blockId] = 'O';
               // the patient drank
               else
                  $counts[$UserId][$blockId] = 'X';
            }
            // have no test
            else
               $counts[$UserId][$blockId] = '-';
         }
         else
            $counts[$UserId][$blockId] = '-';
      }
   }

/*
   function cmp_count($a, $b){
      if($a['drunk'] != $b['drunk']){
         return ($a['drunk'] < $b['drunk'])? 1 : -1;
      }
      else if($a['count'] == $b['count']) return 0;
      else return ($a['count'] < $b['count'])? -1 : 1;
   }
   uasort($user_count, 'cmp_count');
*/

   $message = "<pre><b>Dear all,</b>\n\n    This is an automatic daily email report of our patients.\n".
              "    The tests the patients have completed on ".$yesterday." are listed as following:\n".
              "    ('O' means the test is taken, 'X' means the test is not passed, '-' means the test is missed)\n".
              //"\t(The list is sorted by the number of missing tests from high to low.)\n\n".
              "        UserId       :  Morning  Afternoon   Night\n";

   foreach($counts as $userID => $count){
      $message = $message."        ".str_pad($userID, 13).": ";
      foreach($blocks as $blockId => $block){
         $message = $message.str_pad($count[$blockId], 10, " ", STR_PAD_BOTH);
      }
      if($alcoholics[$userID]['DropOut'] == 1 && $alcoholics[$userId]['DropOutDate'] <= $yesterday){
         $message = $message." (dropped)";
      }
      $message = $message."\n";
   }
              
   $message = $message."\n<b>Best Regards,\nAlcohol Project Team</b></pre>";
   echo $message."\n\n";

   include_once("email_utility.php");

   $to = array("cwyou2004@gmail.com", "b98901112@ntu.edu.tw", "stanley.msa011@gmail.com", "mywebhw@gmail.com");
   //$to = array("b98901112@ntu.edu.tw");
   $subject = "[Alcohol Project] Patient State Daily Report";
   $body = $message;
   send_email($to, $subject, $body);

/* old library
function send_email($message){
   require_once 'Mail.php';

   $from = "<ubicomplab.ntu@gmail.com>";
   //$to = "<cwyou2004@gmail.com>, <b98901112@ntu.edu.tw>, <stanley.msa011@gmail.com>, <mywebhw@gmail.com>";
   $to = "<ha531102@gmail.com>";
   $subject = "[Alcohol Project] Patient State Daily Report";
   $body = $message;

   $host = "ssl://smtp.gmail.com";
   $port = "465";
   $username = "ubicomplab.ntu";
   $password = "alcoholdetection";

   $headers = array ('From' => $from,
                     'To' => $to,
                     'Subject' => $subject);
   $smtp = Mail::factory('smtp',
              array ('host' => $host,
                     'port' => $port,
                     'auth' => true,
                     'username' => $username,
                     'password' => $password));

   $mail = $smtp->send($to, $headers, $body);

   if (PEAR::isError($mail)) {
      echo("Mail Error: " . $mail->getMessage()."\n");
   } else {
      echo("Message successfully sent\n");
   }
}

?>
*/
