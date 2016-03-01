<?php 
   require_once('check_session.php');
   check_session_with_target('manage.php');
?>

<html>

<head>
<meta charset="UTF-8">
<script src="js/jquery-1.10.0.min.js"></script>
<script src="js/jquery-ui_1.10.1.js"></script>
<link rel="stylesheet" type="text/css" href="css/jquery-ui_1.10.1.css">
<link rel="stylesheet" type="text/css" href="css/manage.css">
<link rel="stylesheet" type="text/css" href="css/index.css">
</head>
<body>

<?php include 'header.php'; ?>

<?php
  
   $error = $_GET["err"];
   if($error == "blank")
      $error = "UserId is not given!";
   else if($error == "invalid")
      $error = "UserId is not found!";
   else
      $error = "";

   // init variables
   $today = date("Y-m-d");
   $week_ago = date("Y-m-d", strtotime("-1 week + 1 day"));
   $week_ago_ts = ((string)strtotime($week_ago))."000";
   $month_ago = date("Y-m-d", strtotime("-1 month + 1 day"));
   $month_ago_ts = ((string)strtotime($month_ago))."000";

   //get Alcoholics data from database
   include_once('connect_db.php');
   include_once('score_utility.php');
   include_once('utility.php');
   $conn = connect_to_db();
   $query = "SELECT * FROM Patient";
   $result_all = mysql_query($query);
   $alcoholics = array();
   while($row = mysql_fetch_assoc($result_all)){

      $uid = $row["UserId"];
      $join = $row["JoinDate"];
      $total = getLatestScore($uid);

      $month = $total - getLatestScore($uid, $month_ago_ts);
      if($join > $month_ago) $month /= day_diff($join, $today);
      else $month /= day_diff($month_ago, $today);

      $week = $total - getLatestScore($uid, $week_ago_ts);
      if($join > $week_ago) $week /= day_diff($join, $today);
      else $week /= day_diff($week_ago, $today);

      $cur = $total - $row["UsedScore"];

      $alcoholics[$uid] = $row;
      $alcoholics[$uid]["CurPoints"] = $cur;
      $alcoholics[$uid]["WeekPoints"] = $week;
      $alcoholics[$uid]["MonthPoints"] = $month;
      $alcoholics[$uid]["TotalPoints"] = $total;

   }

   // determin Ranking (Month)
   $MonthPoints = array();
   foreach($alcoholics as $UserId => $alcoholic){
      if(substr($UserId, 0, 6) == "rehab_" && $alcoholic['IsDropout'] == 0 && is_numeric(substr($UserId, 6,3 )))
         $MonthPoints[] = $alcoholic["MonthPoints"];
   }
   rsort($MonthPoints);
   foreach($alcoholics as $UserId => $alcoholic){
      if(substr($UserId, 0, 6) == "rehab_" && $alcoholic['IsDropout'] == 0 && is_numeric(substr($UserId, 6,3 )))
         $alcoholics[$UserId]["MonthRank"] = array_search($alcoholic["MonthPoints"], $MonthPoints) + 1;
      else
         $alcoholics[$UserId]["MonthRank"] = 999;
   }

   // determin Ranking (Week)
   $WeekPoints = array();
   foreach($alcoholics as $UserId => $alcoholic){
      if(substr($UserId, 0, 6) == "rehab_" && $alcoholic['IsDropout'] == 0 && is_numeric(substr($UserId, 6,3 )))
         $WeekPoints[] = $alcoholic["WeekPoints"];
   }
   rsort($WeekPoints);
   foreach($alcoholics as $UserId => $alcoholic){
      if(substr($UserId, 0, 6) == "rehab_" && $alcoholic['IsDropout'] == 0 && is_numeric(substr($UserId, 6,3 )) )
         $alcoholics[$UserId]["WeekRank"] = array_search($alcoholic["WeekPoints"], $WeekPoints) + 1;
      else
         $alcoholics[$UserId]["WeekRank"] = 999;
   }
  
   mysql_close($conn);

   include_once('clickLog_utility.php');
   $yesterday = date("Y_m_d", time() - 24 * 60 * 60);
   foreach($alcoholics as $UserId => $alcoholic){
      $result = countStartRestart($UserId, $yesterday);
      $alcoholics[$UserId]["start_restart"] = $result;
   }
?>

<script language="javascript" type="text/javascript">
   var alcoholics = <?php echo json_encode($alcoholics) ?>;
   var error = "<?php echo $error ?>";
   
</script>

<div class="container" style="width: 900px; text-align: center;">
   <div id="err_div" class="alert alert-danger alert-dismissable" style="display: none;">
      <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
      <strong>Error:</strong> <?php echo $error ?>
   </div>

   <div class="" style="position: relative;">
      <h3>Patient List</h3>
      <form style="position: absolute; top: 5px; left: 0px;" action="download_db.php" method="get">
         <button class="btn btn-primary" type="submit">Export Database</button>
      </form>
      <button class="btn btn-warning" style="position: absolute; top: 5px; left: 150px;" onclick="window.open('../../phpmyadmin')">
         Go To phpAdmin</button>
      <button class="btn btn-info" style="position: absolute; top: 5px; left: 303px;" onclick="window.open('gcm_control.php')">
         GCM</button>

      <ul class="nav nav-tabs" id="tab">
         <li id="patient-li" class="active"><a onclick="changeTab('patient');">Patients</a></li>
         <li id="IsDropout-li"><a onclick="changeTab('IsDropout');">Drop-Outs</a></li>
         <li id="other-li"><a onclick="changeTab('other');">Others</a></li>
      </ul>

      <div id="patient_table" class="tab" style="position: absolute;">
      </div>
      <div id="IsDropout_table" class="tab" style="position: absolute; display: none">
      </div>
      <div id="other_table" class="tab" style="position: absolute; display: none">
      </div>
   </div>
</div>
<form  id="to-patient" action="patient_newUI.php" method="post">
<input id="patientNameInput" type="hidden" name="patientName" value="">
<input id="patientArrayInput" type="hidden" name="patientArray" value="">
</form>


<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script language="javascript" type="text/javascript">

   function goPatientDetail(patientName){
      document.getElementById('patientNameInput').value = patientName;
      document.getElementById('to-patient').submit();
   }

   function detail_click(caller){
      toPatientDetail(caller.name);
   }

   function drop_click(caller){
      if( confirm('Are you sure to drop "' + caller.name + '"?') ){
         drop_person(caller.name);
      }
      else{
         console.log('cancel');
      }
   }

   // drop a person
   function drop_person(uid){

      // use AJAX to send data back to database in background
      var xmlhttp;
      if(window.XMLHttpRequest){
         // code for IE7+, Firefox, Chrome, Opera, Safari
         xmlhttp = new XMLHttpRequest();
      }
      else{
         // code for IE6, IE5
         xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
      }
      xmlhttp.onreadystatechange = function()
      {
         if(xmlhttp.readyState == 4 && xmlhttp.status == 200)
         {
            console.log(xmlhttp.responseText);
            window.location.reload();
         }
      }
      xmlhttp.open("POST", "ajax_drop_alcoholic.php", true);
      xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
      xmlhttp.send("uid=" + uid);

   }

   // ui initialize
   $("#manage").addClass("active");
   if(error != "")
      $("#err_div").fadeIn();

   // make alcoholics array
   var alcoholics_array = [];
   for(var userId in alcoholics)
      alcoholics_array.push(alcoholics[userId]);

   // load google api
   var patient_array = [], IsDropout_array = [], other_array = [];
   google.load("visualization", "1", {packages:["table"]});
   google.setOnLoadCallback(function(){
      for(var i = 0; i < alcoholics_array.length; i++){
         if(!isNaN(parseInt(alcoholics_array[i].UserId.substr(6, 9)))){
            if(alcoholics_array[i].IsDropout == "0")
               patient_array.push(alcoholics_array[i]);
            else
               IsDropout_array.push(alcoholics_array[i]);
         }
         else
            other_array.push(alcoholics_array[i]);
      }
      draw_table("patient_table", patient_array);
      draw_table("IsDropout_table", IsDropout_array);
      draw_table("other_table", other_array);
   });


function draw_table(table_name, patient_array){

   var patient_data = new google.visualization.DataTable();
   patient_data.addColumn('string', 'UserId');
   patient_data.addColumn('string', 'Join Date');
   patient_data.addColumn('number', 'Current Points (Coupons)');
   patient_data.addColumn('number', 'Level   (百分比)');
   patient_data.addColumn('number', 'Ranking (Month)');
   patient_data.addColumn('number', 'Ranking (Week)');
   patient_data.addColumn('string', 'start / restart');
   patient_data.addColumn('string', 'App Ver');
   patient_data.addColumn('string', 'WifiCheck');
   patient_data.addColumn('string', 'DId');
   patient_data.addColumn('string', 'Action');

   var i = 0; var cell_style = {style: 'text-align: center'};
   for(var index in patient_array){
      var j = 0;
      var guy = patient_array[index];
      patient_data.addRows(1);
      patient_data.setCell(i, j++, guy['UserId'], null, cell_style);
      patient_data.setCell(i, j++, guy['JoinDate'].substr(5).replace("-","/"), null, cell_style);
      patient_data.setCell(i, j++, guy['CurPoints'], guy['CurPoints'].toString() + " (" + (Math.floor(guy['CurPoints']/20)).toString() + ")", cell_style);
      patient_data.setCell(i, j++, guy['TotalPoints'], (Math.floor(guy['TotalPoints']/20)).toString() + " (" + (guy['TotalPoints']%20*5).toString() + "%)",cell_style);
      if(guy['MonthRank'] == 999)
         patient_data.setCell(i, j++, 999, 'unrank', cell_style);
      else
         patient_data.setCell(i, j++, guy['MonthRank'], guy['MonthRank'].toString() + " (" + guy['MonthPoints'].toFixed(2) + ")", cell_style);
      if(guy['WeekRank'] == 999)
         patient_data.setCell(i, j++, 999, 'unrank', cell_style);
      else
         patient_data.setCell(i, j++, guy['WeekRank'], guy['WeekRank'].toString() + " (" + guy['WeekPoints'].toFixed(2) + ")", cell_style);
      if(guy['start_restart']['start'] != -1)
         patient_data.setCell(i, j++, guy['start_restart']['start'] + '/' + guy['start_restart']['restart'], null, cell_style);
      else
         patient_data.setCell(i, j++, 'no data', null, cell_style)
      
      patient_data.setCell(i, j++, guy['AppVersion'], null, cell_style);
      if(guy['ConnectionCheckTime'] == null)
         patient_data.setCell(i, j++, '-', null, cell_style);
      else
         patient_data.setCell(i, j++, guy['ConnectionCheckTime'], null, cell_style);
      patient_data.setCell(i, j++, guy['DeviceId'], null, cell_style);
      if(guy["IsDropout"] == "0")
         patient_data.setCell(i, j++, '<button class="btn btn-mini btn-danger" onclick="drop_click(this);" name="' + guy['UserId'] + '">Drop</button> ' +
                              '<button class="btn btn-mini btn-info" onclick="detail_click(this);" name="' + guy['UserId'] + '">Detail</button>',
                               null, {style: 'text-align: center; width: 100px;'});
      else
         patient_data.setCell(i, j++, '<button class="btn btn-mini disabled" name="' + guy['UserId'] + '">Drop</button> ' +
                              '<button class="btn btn-mini btn-info" onclick="detail_click(this);" name="' + guy['UserId'] + '">Detail</button>',
                               null, {style: 'text-align: center; width: 100px;'});
      i++;
   }

   var table = new google.visualization.Table(document.getElementById(table_name));
   table.draw(patient_data, {allowHtml: true, sortColumn: 4});

}

function changeTab(id){
   $("#tab li:not(#" + id + "-li)").removeClass('active');
   $("#" + id + "-li").addClass('active');

   $(".tab:not(#" + id + "_table)").removeClass('in').fadeOut();
   setTimeout(function(){$("#" + id + "_table").fadeIn();}, 100);
}
</script>

</body>
<html>
