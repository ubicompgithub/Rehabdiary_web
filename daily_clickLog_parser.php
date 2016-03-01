<?php

   // Due to permission issues, clients are not allowed to use java to parse raw click log data.
   // This script will parse all log data for patients in Alcoholic.
   // see /etc/crontab for scheduling; this script should be configured to be executed at least everyday
   // see daily_clickLog_parser_log.txt for executing result

   chdir('/var/www_https/rehabdiary');

   // write log
   $now = date("Y-m-d H:i:s");
   exec('echo "=== executed at '.$now.'" >> daily_clickLog_parser_log.txt');

   // get patient uids
   require_once('connect_db.php');
   $conn = connect_to_db();
   $query = "SELECT * FROM  Patient";
   $result = mysql_query($query);
   $alcoholics = array();
   while($row = mysql_fetch_assoc($result)){
      $alcoholics[] = $row["UserId"];
   }
   mysql_close($conn);

   // parse logs
   require_once('clickLog_utility.php');
   foreach($alcoholics as $uid){
      parse_patient_clickLogs($uid);
   }
?>
