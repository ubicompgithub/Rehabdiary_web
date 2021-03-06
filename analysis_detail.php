<?

   // check if the user has logged in
   require_once('check_session.php');
   check_session_with_target('manage.php');

   // if no UserId is input, return to manage.php
   $uid = $_POST['uid_d'];
   $item = $_POST['item_n'];
   if($uid == ""){
     header('Location:consultant.php?err=blank');
      die();
   }

   // Database connection
   include_once('connect_db.php');
   $conn = connect_to_db();

   // get Patients data from database
   $query_patient = "SELECT * FROM  Patient";
   $result_patient = mysql_query($query_patient);
   $patients = array();
   while($row = mysql_fetch_assoc($result_patient)){
      $patients[$row["UserId"]] = $row;
   }

   // find UserID (case-insensitive), if found, set to $target; if not found, return to manage.php
   $found = false;
   foreach($patients as $UserId => $patient){
      if(strtolower($UserId) == strtolower($uid)){
         $target = $patient;
         $found = true;
      }
   }
   if($found == false){
      header('Location:manage.php?err=invalid');
      die();
   }
?>

<html>
<head>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<script src="js/jquery-1.10.0.min.js"></script>
<script src="js/jquery-ui_1.10.1.js"></script>
<script src="js/bootstrap-datepicker.js"></script>
<script src="js/markerclusterer.js"></script>
<script src="js/clickLog_player.js"></script>
<script src="js/jquery.transit.min.js"></script>
<link rel="stylesheet" type="text/css" href="css/jquery-ui_1.10.1.css">
<link rel="stylesheet" type="text/css" href="css/index.css" charset="utf-8">
<link rel="stylesheet" type="text/css" href="css/think.css" charset="utf-8">
<link rel="stylesheet" type="text/css" href="css/datepicker.css">

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

<?php include_once 'utility.php';?>
<?php include_once 'score_utility.php';?>
<?php

   include_once('db_utility.php');
   $uid = $target["UserId"];

   $target["CurrentScore"] = getLatestScore($uid) - $target["UsedScore"];

   // all data
   $detections     = getTableData($uid, "TestResult");
   $questionnaires = getTableData($uid, "NoteAdd");
   $questionnaires_date = getTableData_Date($uid, "NoteAdd");
   $questionnaires_TriggerItems = getTableData_TriggerItems($uid, "NoteAdd");
   $question_tests    = getTableData($uid, "QuestionTest");
   $coping_skills  = getTableData($uid, "CopingSkill");
   $testdetail = getTableData($uid, "TestDetail");
   $exchanges      = getTableData($uid, "ExchangeHistory");
   $certainItemNote = getTabledata_Item($uid,$item);
 
   foreach($detections as $timestamp => $record){
      $detections[$timestamp]["debug"] = get_detection_debug($uid, $record["Timestamp"]);
   }

   $previous = 0;
   foreach($exchanges as $timestamp => $record){
      $exchanges[$timestamp]["Remain"] = getLatestScore($uid, $timestamp) - $record["NumOfCounter"] - $previous;
      $previous += $record["NumOfCounter"];
   }
/*
   //get AnswerContent
   mysql_query("SET CHARACTER SET utf8"); // need to read utf8_unicode_ci data
   $query_answer = "SELECT * FROM `AnswerContent`";
   $result_answer = mysql_query($query_answer);
   $answers = array();
   while($row = mysql_fetch_assoc($result_answer)){
      $answers[$row['Qid']][$row['Aid']] = $row['Text'];
   }
*/
   mysql_close($conn);

   // get valid records
   $valid_detections = detection_prime_person($detections);

   // get click log
   include_once('clickLog_utility.php');
   
   $clickLogs = get_patient_clickLogs($target["UserId"]);
?>

<script language="javascript" type="text/javascript">
   //pass data to client
   var patient      = <?php echo json_encode($target)?>;
   var detections_all = <?php echo json_encode($detections)?>;
   var detections     = <?php echo json_encode($valid_detections)?>;
   var questionnaires = <?php echo json_encode($questionnaires)?>;
   var questionnaires_items = <?php echo json_encode($questionnaires_TriggerItems)?>;

   var certain_items = <?php echo json_encode($certainItemNote)?>;
   var emotionDIYs    = <?php echo json_encode($emotionDIYs)?>;
   var emotionManages = <?php echo json_encode($emotionManages)?>;




 
   //var answers        = <?php echo json_encode($answers)?>;
   var clickLogs      = <?php echo json_encode($clickLogs)?>;
</script>



  <div id="information">
      <h2 style="text-align: center;">病患事件紀錄狀況總覽</h2>
      <p style="font-size: 25px; text-align: center;">UserID： <b id="uid_title"></b>  JoinDate： <span id="uid_joindate"></span>  實驗週數： <span id="uid_week"></span></p>
      <p style="font-size: 22px";>近期(一個月內)吸毒次數：<b id="take_drug">0</b></p> 
  </div>   
  <div id="sub_content">

         <div style="position: absolute; top: 20px; left: 50px;">
            <button type="button" class="btn btn-info chinese-font" onclick="location.href='consultant.php'">回病患總覽</button>
            <button type="button" class="btn btn-success chinese-font" onclick="analysis_click(patient.UserId)">到狀況分析</button>
            <button type="button" class="btn btn-primary chinese-font" onclick="recent_click(patient.UserId)">到近期狀況</button>
         </div>
  </div>


 <div class="main">

  
       <div id="think_table" style="position: absolute; top: 70px;  width: 900px; height: 400px;"></div>
         <div class="btn-group" style="position: absolute; top: 20px;" id="record_btn_group">
            <button id="calendar_record" class="btn btn-warning" 
                   style="height: 30px;" 
                   data-toggle="tooltip" title="End Date" title data-placement="top"
                   data-date=today_str data-date-format="yyyy/mm/dd">
               <i class="icon-calendar icon-white"></i>
            </button>
            <button id="all_btn"  class="btn" onclick="changeXaxis('all', this, record_endDate);">所有</button>
            <button id="mon_btn"  class="btn" onclick="changeXaxis('month', this, record_endDate);">月</button>
            <button id="week_btn" class="btn active" onclick="changeXaxis('week', this, record_endDate);">週</button>
            <button id="day_btn"  class="btn" onclick="changeXaxis('day', this, record_endDate);">日</button>
         </div>
      
     
    
   
  

  </div>
  </div>
  <div id="footer">
  </div>







</body>

<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false"></script>
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript">

   // global variables
   var data;      // for detection table
   var begin;     // indicate min X axis
   var end;       // indicate max X axis
   var type;      // indicates X axis scale
   var show_brac = true;    // whether to show brac data
   var show_emotion = true; // whether to show emotion data
   var show_desire = true;  // whether to show desire data
   var cur_options;         // options in use
   var data_view;           // for changing hidden columns
   var now = new Date();    // current time
   var today = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 0, 0, 0, 0); // today date
   var today_str = dateToString(today);            // today string (yyyy/mm/dd)
   var today_date = today_str.replace(/\//g, '-'); // today string (yyyy-mm-dd)
   // global DataTable variables in order to change selected rows
   var ques_data;
   var emotionDIY_data;
   var emotionManage_data;
   var clickLog_data;
   var coupon_data;
   var story_data; 
   var storyUsage_data; 
   var storyFling_data; 
   // end date of record LineChart
   var record_endDate = new Date(); // default to today

   // const value setting
   var brac_max = 100;
   var basic_options = { curveType: 'none', 
                         legend: {position: 'none'},
                         pointSize: 5,
                         tooltip: {isHtml: true} };
   var vAxis_brac = {title: 'Brac', minValue: 0};
   var vAxis_desire = {maxValue: 10, minValue: 1, title: 'Craving'};
   var vAxis_emotion = {maxValue: 5, minValue: 1, title: 'Emotion', textPosition: 'in'};
   var color_brac = "#2266CC";
   var color_desire = "#DC3912";
   var color_emotion = "#FF9900";

   // ui initialize
   $("#uid_title").text(patient.UserId);
   $("#uid_joindate").text(patient.JoinDate);
   $("#uid_week").text(patient.Week);

   $("#calendar_record").tooltip();
   $("#calendar_record").datepicker({
      // disable future date
      onRender: function(_date){
         return _date.valueOf() > today.valueOf() ? 'disabled' : '';
      }
   }).on('changeDate', function(ev){
      // change date when selected
      $(".datepicker").fadeOut();
      record_endDate = ev.date;
      $("#record_btn_group button.active").click();
   });


   $("#calendar_ques").tooltip();
   $("#calendar_ques").datepicker({
      // disable future date
      onRender: function(_date){
         return _date.valueOf() > today.valueOf() ? 'disabled' : '';
      }
   }).on('changeDate', function(ev){
      // change date when selected
      $(".datepicker").fadeOut();
      var _date = dateToString(ev.date).replace(/\//g, '-');
      changeQuesTableDate(_date);
   });


   $("#calendar").tooltip();
   $("#calendar").datepicker({
      // disable future date
      onRender: function(_date){
         return _date.valueOf() > today.valueOf() ? 'disabled' : '';
      }
   }).on('changeDate', function(ev){
      // change date when selected
      $(".datepicker").fadeOut();
      var _date = dateToString(ev.date).replace(/\//g, '-');
      changeOtherTableDate(_date);
   });

   $("#calendar_player").tooltip();
   $("#calendar_player").datepicker({
      // disable future date
      onRender: function(_date){
         return _date.valueOf() > today.valueOf() ? 'disabled' : '';
      }
   }).on('changeDate', function(ev){
      // change date when selected
      $("#no_data_alert").hide();
      $(".datepicker").fadeOut();
      var _date = dateToString(ev.date).replace(/\//g, '-');
      $("#calendar_player").attr('data-original-title', _date);

      var rid = findRowId(_date, clickLog_data);
      if(rid == -1) $("#no_data_alert").fadeIn();
      setClickLog(_date);
   });

   $("#play_btn").tooltip();
   $("#stop_btn").tooltip();
   $("#step_btn").tooltip();

   // information table filling
   $("#join_date").text(patient.JoinDate);
   if(patient.DropOut == 0){
      $("#drop_out").text("No");
   }
   else{
      $("#drop_out").text("Yes (" + patient.DropOutDate + ")");
   }
   $("#device_id").text(patient.DeviceId);
   $("#current_score").text(patient.CurrentScore);
   $("#used_score").text(patient.UsedScore);
   if(patient.AppVersion == null)
      $("#app_version").text('-');
   else
      $("#app_version").text(patient.AppVersion);

   google.load("visualization", "1", {packages:["corechart", "table"]});
   google.setOnLoadCallback(drawAllCharts);

   function analysis_click(caller){
      toPatientAnalysis(caller);
   }
   function recent_click(caller){
      toPatientRecent(caller);
   }
   
   function drawAllCharts(){
//
      drawCertainItemNoteTable();
   
  //    setTimeout(function(){$(".clicklog_img").each(function(){$(this).attr('src', $(this).attr('_src'));})}, 1000);
   }


   function changeXaxis(_type, caller, end){
      type = _type;
      $("#all_btn").removeClass('active');
      $("#mon_btn").removeClass('active');
      $("#week_btn").removeClass('active');
      $("#day_btn").removeClass('active');
      caller.className = caller.className + " active";

      begin = new Date(end);
      //end = new Date();
      if(type == "all"){
         begin = new Date(patient['JoinDate']);
         end = new Date();
      }
      else if(type == "month")
         begin.setDate(begin.getDate() - 30);
      else if(type == "week")
         begin.setDate(begin.getDate() - 7);
      else if(type == "day")
         begin.setDate(begin.getDate() - 2);

      cur_options['hAxis'] = {minValue: begin, maxValue: end, viewWindow: {min: begin, max: end}};
      var chart = new google.visualization.LineChart(document.getElementById('record_linechart'));
      chart.draw(data_view, cur_options);

   }

   function toggleYdata(data_type, caller){
      switch(data_type){
         case 'brac':    show_brac = !show_brac;
                         if(show_brac)
                            caller.className = "btn btn-primary chinese-font";
                         else
                            caller.className = "btn chinese-font";
                         break;
         case 'emotion': show_emotion = !show_emotion;
                         if(show_emotion)
                            caller.className = "btn btn-warning chinese-font";
                         else
                            caller.className = "btn chinese-font";
                         break;
         case 'desire':  show_desire = !show_desire;
                         if(show_desire)
                            caller.className = "btn btn-danger chinese-font";
                         else
                            caller.className = "btn chinese-font";
                         break;
      }

      data_view = new google.visualization.DataView(data);
      var hidden = [];
      if(!show_brac) {hidden.push(1), hidden.push(2)};
      if(!show_desire) {hidden.push(3), hidden.push(4)};
      if(!show_emotion) {hidden.push(5), hidden.push(6)};
      data_view.hideColumns(hidden);

      var i = 0;
      cur_options['vAxes'] = [];
      cur_options['series'] = [];
      if(show_brac){
         cur_options['vAxes'].push(vAxis_brac);
         cur_options['series'].push({targetAxisIndex: i++, color: color_brac});
      }
      if(show_desire){
         cur_options['vAxes'].push(vAxis_desire);
         cur_options['series'].push({targetAxisIndex: i++, color: color_desire});
      }
      if(show_emotion){
         cur_options['vAxes'].push(vAxis_emotion);
         cur_options['series'].push({targetAxisIndex: i++, color: color_emotion});
      }

      var table = new google.visualization.LineChart(document.getElementById('record_linechart'));
      table.draw(data_view, cur_options);
   }
  
   function drawCertainItemNoteTable(){

   var patient_data = new google.visualization.DataTable();
   patient_data.addColumn('string', '日期&時間');
   patient_data.addColumn('string', '影響');
   patient_data.addColumn('string', '行為');
   patient_data.addColumn('string', '情緒');
   patient_data.addColumn('string', '想法');
   patient_data.addColumn('number', '反思次數');
   patient_data.addColumn('string', '反思日期');
   patient_data.addColumn('string', '反思內容');
   
      var i = 0;
      for(var timestamp in certain_items){
        
         patient_data.addRows(1);
         patient_data.setCell(i, 0, certain_items[timestamp]['Date'] + ' ' + certain_items[timestamp]['Time'], null, getStyle(150));
         patient_data.setCell(i, 1, certain_items[timestamp]['Impact'], null, getStyle(0));
         patient_data.setCell(i, 2, certain_items[timestamp]['Action'], null, getStyle(0));
         patient_data.setCell(i, 3, certain_items[timestamp]['Feeling'], null, getStyle(0));
         patient_data.setCell(i, 4, certain_items[timestamp]['Thinking'], null, getStyle(0));
         patient_data.setCell(i, 5, 0, null, getStyle(0));
         patient_data.setCell(i, 6, "NULL", null, getStyle(0));
         patient_data.setCell(i, 7, "NULL", null, getStyle(0));

         i++;
         
      }


   var table = new google.visualization.Table(document.getElementById('think_table'));
   table.draw(patient_data, {allowHtml: true, sortColumn: 0});

   }  
  




</script>

</html>
