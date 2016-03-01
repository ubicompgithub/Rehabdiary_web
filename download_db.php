<?php
//   require_once('check_session.php');
//   check_session_with_target('manage.php');

   // generate files

   // open a random tmp directory to improve security(?)
   $path = getcwd();
   $code = "tmpDB_".substr(md5(rand()), 0, 6);
   mkdir($code);

   include_once('connect_db.php');
   $conn = connect_to_db();

   // save a table to a file in the tmp directory
   $tableName = 'Alcoholic2';
   $fileName = $tableName;
   $localFileName = $path."/".$code."/".$fileName.".csv";

   $columnFile = $localFileName.".col";
   $rowFile = $localFileName.".row";
/*
   // write column table
   $writeFH = fopen($columnFile, 'w');
   $query = "SELECT `COLUMN_NAME` FROM `INFORMATION_SCHEMA`.`COLUMNS` WHERE `TABLE_SCHEMA`='alcohol_project' AND `TABLE_NAME`='".$tableName."';";
   $result = mysql_query($query);
   $columnHeader = "";
   while($row = mysql_fetch_assoc($result)){
      $columnHeader .= $row['COLUMN_NAME'].",";
   }
   $columnHeader = substr($columnHeader, 0, -1)."\n";
   fwrite($writeFH, $columnHeader);
  */ 
   // write row table
   $query = "SELECT * INTO OUTFILE '/var/www_https/develop/drunk_detection/tmp.csv' FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '\"'".
            " LINES TERMINATED BY '\n' FROM alcohol_project.Alcoholic2;";
   echo $query;
   $result = mysql_query($query);
   echo $result;

   //exec("cat ".$columnFile." ".$rowFile." > ".$localFileName);
   //unlink($columnFile);
   //unlink($rowFile);

   
   mysql_close($conn);

/*
   // transfer file
   $fileName = "AlcoholProject_Database.csv";

   header('Content-disposition: attachment; filename='.$fileName);
   header("Content-type: text/csv");
   header("Content-Length: " . filesize($localFileName) ."; ");
   header('Content-Transfer-Encoding: binary');
   ob_clean();
   flush();

   $chunksize = 1 * (1024 * 1024); // how many bytes per chunk
   if (filesize($localFileName) > $chunksize) {
      $handle = fopen($localFileName, 'rb');
      $buffer = '';

      while (!feof($handle)) {
         $buffer = fread($handle, $chunksize);
         echo $buffer;
         ob_flush();
         flush();
      }

      fclose($handle);
   } else {
      readfile($localFileName);
   }

   //unlink($localFileName);
*/
?>
