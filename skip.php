<?php

   // check if the user has logged in
   require_once('check_session.php');
   check_session_with_target('skip.php');

?>

<html>
<head>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<script src="js/jquery-1.10.0.min.js"></script>
<script src="js/jquery-ui_1.10.1.js"></script>
<script src="js/utility.js"></script>
<link rel="stylesheet" type="text/css" href="css/jquery-ui_1.10.1.css">
<link rel="stylesheet" type="text/css" href="css/index.css">

</head>

<body>

<!-- For Google Analytics-->
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-41411079-1', '140.112.30.165');
  ga('send', 'pageview');

</script>

<!-- header -->
<?php include 'header.php';?>

<?php

   //get current date
   $today = new DateTime();
   $start_day = $today->modify("-14 day");
   $start = $start_day->format("Y-m-d");
   $now = date('Y-m-d');
   
   //get Alcoholics data from database
   include_once('connect_db.php');
   $conn = connect_to_db();
   $query = "SELECT * FROM  Patient ORDER BY `UserId` ASC";
   $result_all = mysql_query($query);
   $alcoholics = array();
   $alcoholic_names = array();
   while($row = mysql_fetch_assoc($result_all)){
      $alcoholics[$row["UserId"]] = $row;
      $alcoholic_names[] = $row["UserId"];
   }

   //get Detections data from database in this day
   $query_detection = "SELECT * FROM `TestResult` WHERE `UserId` IN (SELECT `UserId` FROM `Patient`) AND `Date` >= '".$start."' ORDER BY `Timestamp` ASC";
   $result_detection = mysql_query($query_detection);
   $detections = array();
   while($row = mysql_fetch_assoc($result_detection)){
      $detections[$row["UserId"]][$row["Timestamp"]] = $row;
   }
   
/*
   //get Block information
   $query_block = "SELECT * FROM `TimeBlock`";
   $result_block = mysql_query($query_block);
   $blocks = array();
   while($row = mysql_fetch_assoc($result_block)){
      $blocks[$row["BlockID"]] = $row;
   }

   //get EmotionDIY
   $query_emotionDIY = "SELECT * FROM `EmotionDIY2` WHERE `Date` = '".$now."' ORDER BY `Timestamp` ASC";
   $result_emotionDIY = mysql_query($query_emotionDIY);
   $emotionDIYs = array();
   while($row = mysql_fetch_assoc($result_emotionDIY)){
      $emotionDIYs[$row["UserId"]][$row["Time"]] = $row;
   }

   //get EmotionManage
   $query_emotionManage = "SELECT * FROM `EmotionManage2` WHERE `Date` = '".$now."' ORDER BY `Timestamp` ASC";
   $result_emotionManage = mysql_query($query_emotionManage);
   $emotionManages = array();
   while($row = mysql_fetch_assoc($result_emotionManage)){
      $emotionManages[$row["UserId"]][$row["Time"]] = $row;
   }

   //get Questionnaire
   $query_Questionnaire = "SELECT * FROM `Questionnaire2` WHERE `Date` = '".$now."' ORDER BY `Timestamp` ASC";
   $result_Questionnaire = mysql_query($query_Questionnaire);
   $questionnaires = array();
   while($row = mysql_fetch_assoc($result_Questionnaire)){
      $questionnaires[$row["UserId"]][$row["Time"]] = $row;
   }
*/
   mysql_close($conn);

   //only retrieve valid records in each time span
   $detections_valid = detection_validate($detections);

   function UserIDtoDeviceID($userID){
      global $alcoholics;
      return $alcoholics[$userID]["DeviceID"];
   }

   function DeviceIDtoDeviceID($deviceID){
      global $alcoholics;
      foreach($alcoholics as $userID => $data){
         if($data["DeviceID"] == $deviceID) return $userID;
      }
      return "Not Found";
   }

   function detection_validate($detections){
      $detections_valid = array();
      foreach($detections as $userID => $records){
         $records_valid = array();
         foreach($records as $timestamp => $record){

            $index = 0;
            $hour = (int)($record["Time"].substr(0, 2));
            if($hour >= 6 and $hour < 10) {$index = 1;}
            else if($hour >= 10 and $hour < 18) {$index = 2;}
            else if($hour >= 18) {$index = 3;}
            else continue;
            
            $record_date = str_replace("-", "/", $record["Date"]);

            $record["Result"] = (float)$record["Result"]; // in order to eliminate redundant digits
            if(!array_key_exists($record_date, $records_valid)){
               $records_valid[$record_date][$index] = $record;
            }
            else if(!array_key_exists($index, $records_valid[$record_date])){
               $records_valid[$record_date][$index] = $record;
            }
            else continue;
         }
         $detections_valid[$userID] = $records_valid;
      }
      return $detections_valid;
   }

?>
<script language="javascript" type="text/javascript">
   //pass data to client
   var alcoholics = <?php echo json_encode($alcoholics) ?>;
   var detections = <?php echo json_encode($detections) ?>;
   var detections_valid = <?php echo json_encode($detections_valid) ?>;
   var alcoholic_names = <?php echo json_encode($alcoholic_names)?>;
/*
   var blocks = <?php echo json_encode($blocks)?>;
   var emotion_diy = <?php echo json_encode($emotionDIYs)?>;
   var emotion_manage = <?php echo json_encode($emotionManages)?>;
   var questionnaire = <?php echo json_encode($questionnaires)?>;
*/
</script>

<div class="container">

   <div style="width: 900px; margin: 0px auto; position: relative;">
      <h3># of Skipped People</h3>
      <div id="skipped_table" style="width: 600px; position: absolute;"></div>
      <div id="table_block" style="width: 150px; position: absolute; left: 650px;">
         <div id="skipped_name_table" style="width: 150px"></div>
         <a id="mail_btn" class="btn btn-mini btn-warning hidden" style="margin: 3px;" href="mailto:">Send Email</a>
      </div>
   </div>

</body>

<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript">

   // global variables
   var patient_table;
   var skipped_table;
   var date_array;

   // ui initialize
   $("#skip").addClass("active");

   // load google api
   google.load("visualization", "1", {packages:["table"]});
   google.setOnLoadCallback(function(){draw_table();});

// functions

// helper function of getRecentDayArray
function dateToString(curDate){
   return curDate.toISOString().substring(0, 10).replace('-', '/').replace('-', '/');
}

// get the recent 'num' of dates in array
function getRecentDayArray(num, cur_day){
   date_array = new Array();
   var curDay = new Date();
   curDay.setTime(cur_day.getTime());
   for(var i = 0; i < num; i++){
      date_array.push(dateToString(curDay));
      curDay.setDate(curDay.getDate() - 1);
   }
   date_array = date_array.reverse();
   return date_array;
}

// draw main table
function draw_table(){

   // find record for each person in each day
   var today = new Date();
   var date_array = getRecentDayArray(14, today);

   // find record for each person in each day
   patient_table = {};
   for(var i in alcoholic_names){
      var name = alcoholic_names[i];
      patient_table[name] = {};
      var join_date = new Date(alcoholics[name].JoinDate);
      join_date.setDate(join_date.getDate() - 7);
      join_date = dateToString(join_date);
      //var join_date = alcoholics[name].JoinDate.replace(/-/g, '/');
      if(alcoholics[name].IsDropout == 1)
         var drop_date = alcoholics[name].DropoutDate.replace(/-/g, '/');
      for(var j in date_array){
         patient_table[name][date_array[j]] = {};
         if(join_date <= date_array[j] && (alcoholics[name].IsDropout == 0 || drop_date >= date_array[j])){
            patient_table[name][date_array[j]]['join'] = true;
            if(detections_valid[name] !== undefined && detections_valid[name][date_array[j]] !== undefined){
               patient_table[name][date_array[j]]['test'] = true;
            }
            else
               patient_table[name][date_array[j]]['test'] = false;
         }
         else{
            patient_table[name][date_array[j]]['join'] = false;
            patient_table[name][date_array[j]]['test'] = false;
         }
      }
   }

   // # of skipped people in each day
   total_table = [];
   skipped_table = [];
   for(var i in date_array){
      var sum = 0;
      var total = 0;
      for(var j in alcoholic_names){
         if(patient_table[alcoholic_names[j]][date_array[i]]['join'] == true){
            total++;
            if(patient_table[alcoholic_names[j]][date_array[i]]['test'] == false)
               sum++;
         }
      }
      skipped_table.push(sum);
      total_table.push(total);
   }

   var data = new google.visualization.DataTable();
   data.addColumn('string', 'Date');
   data.addColumn('number', '# of skipped people');
   data.addColumn('number', 'Total');
   data.addRows(14);
   for(var i = 0; i < 14; i++){
      data.setCell(i, 0, date_array[i], null, {style: 'text-align:center;'});
      data.setCell(i, 1, skipped_table[i], null, {style: 'text-align:center;'});
      data.setCell(i, 2, total_table[i], null, {style: 'text-align:center;'});
   }

   function selectHandler(){
      var selectedItem = table.getSelection();
      show_skipped_name_table(selectedItem);
   }
   
   var table = new google.visualization.Table(document.getElementById('skipped_table'));
   google.visualization.events.addListener(table, 'select', selectHandler);
   table.draw(data, {sort: 'disable', allowHtml: true});

   var default_select = [{row:13},{row:12},{row:11}];
   table.setSelection(default_select);
   show_skipped_name_table(default_select);
}

function show_skipped_name_table(selectedItem){
   if(selectedItem.length == 0){
      $("#skipped_name_table").text("");
      $("#mail_btn").addClass("hidden");
      return;
   }

   var dates = "";
   var _tested = {};
   for(var i in alcoholic_names)
      _tested[alcoholic_names[i]] = false;
   for(var i in selectedItem){
      var _date = date_array[selectedItem[i].row];
      dates = dates + "   " + _date + "%0A";
      for(var j in alcoholic_names){
         var name = alcoholic_names[j];
         if(patient_table[name][_date]['test'] == true ||
            patient_table[name][_date]['join'] == false) _tested[name] = true;
      }
   }

   var data = new google.visualization.DataTable();
   data.addColumn('string', 'Skipped Name');

   var cur = 0;
   var names = "";
   for(var name in _tested){
      if(_tested[name] == false && name.substr(0,7) == "rehab_"){
         data.addRows(1);
         data.setCell(cur, 0, name, null, {style: 'text-align: center; font-weight: bold;'});
         names = names + "   " + name + "%0A";
         cur++;
      }
   }
   for(var name in _tested){
      if(_tested[name] == false && name.substr(0,7) != "rehab_"){
         data.addRows(1);
         data.setCell(cur, 0, name, null, {style: 'text-align: center;'});
         names = names + "   " + name + "%0A";
         cur++;
      }
   }

   if(cur == 0){ // no skipped patients
      $("#skipped_name_table").text("");
      $("#mail_btn").addClass("hidden");
      return;
   }
   else{
      $("#mail_btn").removeClass("hidden");
      $("#mail_btn").attr("href", "mailto: ?body=The patients who did not have tests during:%0A%0A"
                                  + dates + "%0Aare:%0A%0A" + names);
   }
   

   function selectHandler(){
      var selectedItem = table.getSelection()[0];
      toPatientDetail(data.getFormattedValue(selectedItem.row, 0));
   }

   var table = new google.visualization.Table(document.getElementById('skipped_name_table'));
   google.visualization.events.addListener(table, 'select', selectHandler);
   table.draw(data, {allowHtml: true, sort: 'disable'});
}

function UserId2Name(UserId){
   return alcoholics[UserId].Name;
}

</script>

</html>
