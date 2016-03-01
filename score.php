<?php 
   require_once('check_session.php');
   check_session_with_target('score.php');
?>

<html>

<head>
<meta charset="UTF-8">
<script src="js/jquery-1.10.0.min.js"></script>
<script src="js/jquery-ui_1.10.1.js"></script>
<link rel="stylesheet" type="text/css" href="css/jquery-ui_1.10.1.css">
<link rel="stylesheet" type="text/css" href="css/index.css">
</head>
<body>

<?php include 'header.php'; ?>

<?php
  
   // init variables
   $today = date("Y-m-d");

   //get Alcoholics data from database
   include_once('connect_db.php');
   include_once('score_utility.php');
   include_once('utility.php');
   $conn = connect_to_db();
   $query = "SELECT * FROM Patient";
   $result_all = mysql_query($query);
   $alcoholics = array();
   $first_day = $today;
   while($row = mysql_fetch_assoc($result_all)){
      $uid = $row["UserId"];
     // if(!preg_match("/^rehab_\d{3}/", $uid)) continue;
      if($uid == "rehab_008") continue;

      $score_array = array();
      $cur_day = new DateTime($row["JoinDate"]);
      $cur_day_str = $cur_day->format("Y-m-d");
      if($cur_day_str < $first_day) $first_day = $cur_day_str;
      while($cur_day_str <= $today){
         $cur_day_ts = ((string)strtotime($cur_day_str))."000";
         $score_array[$cur_day_str] = getLatestScore($uid, $cur_day_ts);

         $cur_day->modify("+1 day");
         $cur_day_str = $cur_day->format("Y-m-d");
      }
      $alcoholics[$uid]["Score"] = $score_array;
   }

   mysql_close($conn);
?>

<script language="javascript" type="text/javascript">
   var alcoholics = <?php echo json_encode($alcoholics) ?>;
   var first_day = "<?php echo $first_day ?>";
</script>

<div class="container" style="width: 900px; text-align: center;">
   <div class="" style="position: relative;">
      <h3>Patient Score Board</h3>
      <ul class="nav nav-tabs">
         <li class="active"><a href="#score_time" data-toggle="tab" onclick="$('#score_time').fadeIn();">按時間</a></li>
         <li><a href="#score_day" data-toggle="tab" onclick="$('#score_time').fadeOut();">按天數</a></li>
      </ul>
      <div class="tab-content">
         <div id="score_day"  style="width: 900px; height: 400px; position: absolute;"></div>
         <div id="score_time"  style="width: 900px; height: 400px; position: absolute;"></div>
      </div>
   </div>
</div>

<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script language="javascript" type="text/javascript">

   // global values
   var today_str = dateToString(new Date(), "-");

   // ui initialize

   // load google api
   google.load("visualization", "1", {packages:["linechart"]});
   google.setOnLoadCallback(function(){
      draw_score_time(alcoholics, first_day);
      draw_score_day(alcoholics, first_day);
   });


function draw_score_time(alcoholics, first_day){

   var patient_data = new google.visualization.DataTable();
   patient_data.addColumn('date', 'Time');
   for(var userId in alcoholics)
      patient_data.addColumn('number', userId);

   var i = 0, cur_day = new Date(first_day), cur_day_str = first_day;
   while(cur_day_str <= today_str){
      patient_data.addRows(1);
      patient_data.setCell(i, 0, new Date(cur_day_str));
      var j = 1;
      for(var userId in alcoholics){
         patient_data.setCell(i, j++, alcoholics[userId]["Score"][cur_day_str]);
      }
      i++;
      cur_day.setDate(cur_day.getDate() + 1);
      cur_day_str = dateToString(cur_day, "-");
   }

   var table = new google.visualization.LineChart(document.getElementById("score_time"));
   table.draw(patient_data, {});

}

function draw_score_day(alcoholics, first_day){

   var patient_data = new google.visualization.DataTable();
   //patient_data.addColumn('number', 'Day');
   for(var userId in alcoholics)
      patient_data.addColumn('number', userId);

   var first = new Date(first_day);
   var now = new Date(today_str);
   var total = (now - first) / 86400000 + 1;

   patient_data.addRows(total);
   var i = 0;
   for(var j = 0; j < total; j++){
      //patient_data.setCell(j, i, 0);
   }

   var i = 0;
   for(var userId in alcoholics){
      var j = 0;
      for(var day in alcoholics[userId]["Score"]){
         patient_data.setCell(j++, i, alcoholics[userId]["Score"][day]);
      }
      i++
   }

   var table = new google.visualization.LineChart(document.getElementById("score_day"));
   table.draw(patient_data, {});

}

</script>

</body>
<html>
