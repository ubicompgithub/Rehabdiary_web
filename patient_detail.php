<?php

   // check if the user has logged in
   require_once('check_session.php');
   check_session_with_target('manage.php');

   // if no UserId is input, return to manage.php
   $uid = $_POST['uid'];
   if($uid == ""){
      header('Location:manage.php?err=blank');
      die();
   }

   // Database connection
   include_once('connect_db.php');
   $conn = connect_to_db();

   // get Alcoholics data from database
   $query_alcoholic = "SELECT * FROM  Patient";
   $result_alcoholic = mysql_query($query_alcoholic);
   $alcoholics = array();
   while($row = mysql_fetch_assoc($result_alcoholic)){
      $alcoholics[$row["UserId"]] = $row;
   }

   // find UserID (case-insensitive), if found, set to $target; if not found, return to manage.php
   $found = false;
   foreach($alcoholics as $UserId => $alcoholic){
      if(strtolower($UserId) == strtolower($uid)){
         $target = $alcoholic;
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
<link rel="stylesheet" type="text/css" href="css/patient_detail.css" charset="utf-8">
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
 //  $additionals    = getTableData($uid, "AdditionalQuestionnaire");
   $question_tests    = getTableData($uid, "QuestionTest");
   $coping_skills  = getTableData($uid, "CopingSkill");
   $testdetail = getTableData($uid, "TestDetail");
   $exchanges      = getTableData($uid, "ExchangeHistory");
   $salivas      = getTableData($uid, "Saliva");
   //$storyReadings  = getTableData($uid, "StorytellingReading");
  // $storyRecords   = getTableData($uid, "StorytellingRecord");
  // $salivass     = getTableData($uid, "StorytellingTest");

   foreach($detections as $timestamp => $record){
      $detections[$timestamp]["debug"] = $testdetail[$timestamp];
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
   var alcoholic      = <?php echo json_encode($target)?>;
   var detections_all = <?php echo json_encode($detections)?>;
   var detections     = <?php echo json_encode($valid_detections)?>;
   var questionnaires = <?php echo json_encode($questionnaires)?>;
  //var additionals    = <?php echo json_encode($additionals)?>;
   //var emotionDIYs    = <?php echo json_encode($emotionDIYs)?>;
   var coping_skills = <?php echo json_encode($coping_skills)?>;
   var coupons        = <?php echo json_encode($exchanges)?>;
   var question_tests      = <?php echo json_encode($question_tests)?>;
   //var storyReadings  = <?php echo json_encode($storyReadings)?>;
   //var storyRecords   = <?php echo json_encode($storyRecords)?>;
   //var salivass     = <?php echo json_encode($salivass)?>;
   var salivas        = <?php echo json_encode($salivas)?>;
   var testdetail       = <?php echo json_encode($testdetail)?>;
   var date_questionnaires = <?php echo json_encode($questionnaires_date)?>;
   var clickLogs      = <?php echo json_encode($clickLogs)?>;
</script>

<div class="container">

   <div style="width: 900px; margin: 0px auto; position: relative;">
      <h2 style="text-align: center;">UserID: <b id="uid_title"></b></h2>
      <div class="page-header"><h3>Information</h3></div>
      <table class="table table-bordered">
         <tr>
            <th>Join Date</th>
            <th>Drop Out</th>
            <th>Device ID</th>
            <th>Current Score</th>
            <th>Current Level(progress)</th>
            <th>Current Status(-10~10)</th>>
            <th>Used Score</th>
            <th>App Version</th>
         </tr>
         <tr>
            <td id="join_date"></td>
            <td id="drop_out"></td>
            <td id="device_id"></td>
            <td id="current_score"></td>
            <td id="current_level"></td>
            <td id="current_status"></td>
            <td id="used_score"></td>
            <td id="app_version"></td>
         </tr>
      </table>

      <div class="page-header"><h3>Records</h3></div>
      <div style="position: relative">
         <div id="record_linechart" style="width: 900px; height: 400px;"></div>
         <div class="btn-group" style="position: absolute; left: 800px; top: 30px;" id="record_btn_group">
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
         <div style="position: absolute; left: 100px; top: 30px;">
            <button id= "brac_btn" type="button" class="btn btn-primary chinese-font" onclick="toggleYdata('brac', this)">是否用K</button>
         </div>

      </div>

      <div class="page-header"><h3>Questionnaires Records_SelfCondition </h3></div>
      <div style="position: relative">
         <div id="record_annotation" style="width: 900px; height: 400px;"></div>
         <div class="btn-group" style="position: absolute; left: 800px; top: 30px;" id="ques_record_btn_group">
            <button id="calendar_record" class="btn btn-warning" 
                   style="height: 30px;" 
                   data-toggle="tooltip" title="End Date" title data-placement="top"
                   data-date=today_str data-date-format="yyyy/mm/dd">
               <i class="icon-calendar icon-white"></i>
            </button>
            <button id="all_btn_ques"  class="btn" onclick="changeXaxis_ques('all', this, record_endDate);">所有</button>
            <button id="mon_btn_ques"  class="btn" onclick="changeXaxis_ques('month', this, record_endDate);">月</button>
            <button id="week_btn_ques" class="btn active" onclick="changeXaxis_ques('week', this, record_endDate);">週</button>
            <button id="day_btn_ques"  class="btn" onclick="changeXaxis_ques('day', this, record_endDate);">日</button>
         </div>
         <div style="position: absolute; left: 100px; top: 30px;">
            <button id="desire_btn" type="button" class="btn btn-warning chinese-font" onclick="toggleYdata_ques('desire', this)">自我狀態</button>
         </div>


      </div>
      
      <div class="page-header"><h3>Questionnaires Records_Relationship </h3></div>
      <div style="position: relative">
         <div id="record_annotation2" style="width: 900px; height: 400px;"></div>
         <div class="btn-group" style="position: absolute; left: 800px; top: 30px;" id="ques_record_btn_group2">
            <button id="calendar_record" class="btn btn-warning" 
                   style="height: 30px;" 
                   data-toggle="tooltip" title="End Date" title data-placement="top"
                   data-date=today_str data-date-format="yyyy/mm/dd">
               <i class="icon-calendar icon-white"></i>
            </button>
            <button id="all_btn_ques2"  class="btn" onclick="changeXaxis_ques2('all', this, record_endDate);">所有</button>
            <button id="mon_btn_ques2"  class="btn" onclick="changeXaxis_ques2('month', this, record_endDate);">月</button>
            <button id="week_btn_ques2" class="btn active" onclick="changeXaxis_ques2('week', this, record_endDate);">週</button>
            <button id="day_btn_ques2"  class="btn" onclick="changeXaxis_ques2('day', this, record_endDate);">日</button>
         </div>
         <div style="position: absolute; left: 100px; top: 30px;">
            <button id="emotion_btn" type="button" class="btn btn-success chinese-font" onclick="toggleYdata_ques2('emotion', this)">人際關係</button>
         </div>


      </div>


      <div class="page-header"><h3>Questionnaires</h3></div>
      <div style="width: 900px; height: 531px; position: relative;">
         <ul class="nav nav-tabs" id="ques_ul">
            <li class="active"><a href="#ques_table" data-toggle="tab">活動問卷(測試後)</a></li>
            <li><a href="#emotionDIY_table" data-toggle="tab">活動問卷(主動)</a></li>
            <li><a href="#regular_ques_table" data-toggle="tab">常態問題</a></li>
            <li><a href="#random_ques_table" data-toggle="tab">隨機式問題</a></li>
            <li><a href="#coping_skill_table" data-toggle="tab">應對技巧</a></li>
           
         </ul>
         
         <div class="tab-content">
            <div class="tab-pane fade active in" id="ques_table">
            </div>
            
            <div class="tab-pane fade" id="emotionDIY_table">
            </div>

            <div class="tab-pane fade" id="regular_ques_table">
            </div>

            <div class="tab-pane fade" id="random_ques_table">
            </div>

            <div class="tab-pane fade" id="coping_skill_table">
            </div>
                                    

         </div>

         <button id="calendar_ques" class="btn btn-warning" 
                style="position: absolute; top: 50px; left: -50px; height: 30px;" 
                data-toggle="tooltip" title="Jump Date" title data-placement="left"
                data-date=today_str data-date-format="yyyy/mm/dd">
            <i class="icon-calendar icon-white"></i>
         </button>
      </div>

      <div class="page-header"><h3>Others</h3></div>
      <div style="width: 900px; height: 531px; position: relative;">
         <ul class="nav nav-tabs" id="other_ul">
          <!--  <li class="active"><a href="#geo_map" data-toggle="tab">Geometry Data</a></li> !-->
            <li><a href="#clickLog_block" data-toggle="tab">Click Log</a></li>
            <li><a href="#coupon_table" data-toggle="tab">Coupon</a></li>
            <li><a href="#detail_table" data-toggle="tab">Test Detail</a></li>
            <li><a href="#salivas_table" data-toggle="tab">Used Salivas</a></li>
          <!--  <li><a href="#storyRecord_table" data-toggle="tab">Storytelling Records</a></li>
            <li><a href="#salivas_table" data-toggle="tab">Storytelling Tests</a></li>
            <li><a href="#facebook_table" data-toggle="tab">Facebook</a></li>  !-->
         </ul>
         
         <div class="tab-content" style="position: relative; height: 550px;">
            <div class="tab-pane fade active in" id="geo_map" style="width: 900px; height: 473px;">
            </div>

            <div class="tab-pane fade" id="clickLog_block" style="position: relative;">
               <div id="clickLog_table"></div>
            </div>
            
            <div class="tab-pane fade" id="coupon_table">
            </div>
            <div class="tab-pane fade" id="detail_table">
            </div>

            <div class="tab-pane fade" id="salivas_table">
            </div>
<!--
            <div class="tab-pane fade" id="storyRecord_table">
            </div>

            <div class="tab-pane fade" id="salivas_table">
            </div>

            <div class="tab-pane fade" id="facebook_table">
            </div>
--!>            
         </div>

         <button id="calendar" class="btn btn-warning" 
                style="position: absolute; top: 50px; left: -50px; height: 30px;" 
                data-toggle="tooltip" title="Jump Date" title data-placement="left"
                data-date=today_str data-date-format="yyyy/mm/dd">
            <i class="icon-calendar icon-white"></i>
         </button>
     </div>
<!--
     <div class="page-header"><h3>ClickLog Player</h3></div>
         <div id="phone_block" style="width:900px; height: 500px; position: relative; overflow: hidden;">
            <div id="app_group" style="position: absolute; z-index: 10; width: 900px; height: 500px;">
               <div id="test_page" style="z-index: 30;">
                  <img width="270" height="480" class="clicklog_img" style="position: absolute; top: 0px; left: 315px;" _src="img/phone/Test.png"></img>
                  <img width="270" height="480" class="clicklog_img test_tab" style="position: absolute; top: 0px; left: 315px; display: none" _src="img/phone/Tutorial_3.png"   id="test_tutorial"></img>
                  <img width="270" height="480" class="clicklog_img test_tab" style="position: absolute; top: 0px; left: 315px; display: none" _src="img/phone/Test_Start.png"   id="test_start"></img>
                  <img width="270" height="480" class="clicklog_img test_tab" style="position: absolute; top: 0px; left: 315px; display: none" _src="img/phone/Test_Restart.png" id="test_restart"></img>
                  <img width="270" height="480" class="clicklog_img test_tab" style="position: absolute; top: 0px; left: 315px; display: none" _src="img/phone/Test_Ques.png"    id="test_question"></img>
               </div>

               <div id="statistic_page" style="z-index: 40;">
                  <img width="270" height="480" class="clicklog_img" style="position: absolute; top: 0px; left: 315px;" _src="img/phone/Statistic.png"></img>
                  <img width="810" height="211" class="clicklog_img" style="position: absolute; top: 0px; left: 315px;" _src="img/phone/Statistics_Record.png"           id="Statistics_Record"></img>
                  <img width="270" height="480" class="clicklog_img" style="position: absolute; top: 0px; left: 315px; display: none" _src="img/phone/Questionnaire.png" id="Questionnaire"></img>
                  <img width="23"  height="23"  class="clicklog_img" style="position: absolute; top: 11px; left: 550px;" _src="img/phone/Statistic_question_button.png">
                  </img>
               </div>

               <div id="story_page" style="z-index: 40;">
                  <img width="270" height="480" class="clicklog_img" style="position: absolute; top: 0px; left: 315px;" _src="img/phone/Story.png"></img>
                  <img width="270" height="285" class="clicklog_img" style="position: absolute; top: -285px; left: 315px;" _src="img/phone/Story_Paint.png"    id="Story_Paint"></img>
                  <img width="270" height="141" class="clicklog_img" style="position: absolute; top: 285px; left: 315px;" _src="img/phone/Story_All.png"       id="story_all"       class="story_tab"></img>
                  <img width="270" height="141" class="clicklog_img" style="position: absolute; top: 285px; left: 315px;" _src="img/phone/Story_Detection.png" id="story_detection" class="story_tab"></img>
                  <img width="270" height="141" class="clicklog_img" style="position: absolute; top: 285px; left: 315px;" _src="img/phone/Story_Desire.png"    id="story_desire"    class="story_tab"></img>
                  <img width="270" height="141" class="clicklog_img" style="position: absolute; top: 285px; left: 315px;" _src="img/phone/Story_Emotion.png"   id="story_emotion"   class="story_tab"></img>
                  <img width="30"  height="30"  class="clicklog_img" style="position: absolute; top: 320px; left: 400px; display: none" _src="img/phone/Story_chart_circle.png" id="story_chart_circle"></img>
                  <img width="270" height="480" class="clicklog_img" style="position: absolute; top: 0px; left: 315px; display: none" _src="img/phone/Story_Record.png"         id="story_record"></img>
                  <img width="270" height="480" class="clicklog_img" style="position: absolute; top: 0px; left: 315px; display: none" _src="img/phone/Story_Recording.png"      id="story_recording"></img>
               </div>

               <div id="menu_page" style="z-index: 50;">
                  <img width="188" height="110" class="clicklog_img" style="position: absolute; top: 370px; left:356px; display: none" _src="img/phone/Menu.png"
                       id="menu"></img>
               </div>

               <div id="emotionDIY_page" style="z-index: 60;">
                  <img width="270" height="480" class="clicklog_img" style="position: absolute; top: 0px; left: 315px; display: none" _src="img/phone/EmotionDIY.png"         id="emotionDIY"></img>
                  <img width="270" height="480" class="clicklog_img" style="position: absolute; top: 0px; left: 315px; display: none" _src="img/phone/EmotionDIY_Play.png"    id="emotionDIY_play"></img>
                  <img width="270" height="480" class="clicklog_img" style="position: absolute; top: 0px; left: 315px; display: none" _src="img/phone/EmotionDIY_Playing.png" id="emotionDIY_playing"></img>
               </div>

               <div id="emotionManage_page" style="z-index: 70;">
                  <img width="270" height="480" class="clicklog_img" style="position: absolute; top: 0px; left: 315px; display: none" _src="img/phone/EmotionManage.png"      id="emotionManage"></img>
               </div>

               <div id="about_page" style="z-index: 80;">
                  <img width="270" height="480" class="clicklog_img" style="position: absolute; top: 0px; left: 315px; display: none" _src="img/phone/About.png"              id="about"></img>
               </div>
            </div>

            <div id="mask" style="position: absolute; z-index: 20; width: 900px; height: 500px;">
               <div style="position: absolute; top:0px; left: 0px; width: 315px; height: 500px; background-color: white;"></div>
               <div style="position: absolute; top:0px; left: 585px; width: 315px; height: 500px; background-color: white;"></div>
            </div>

            <div id="click_place" style="position: absolute; z-index: 30; width: 900px; height: 500px;">
               <div style="position: absolute;" id="rect_click"></div>
               <div style="position: absolute; -webkit-border-radius: 999px; -moz-border-radius: 999px; border-radius: 999px;" id="circle_click"></div>
            </div>

            <div id="no_data_alert" class="alert alert-error" style="position: absolute; top:100px; left: 325px; z-index: 30; width: 200px; display: none">
               No Click Logs in this day.
            </div>

            <div class="btn-group btn-group-vertical" style="position: absolute; top: 0px; left: 150px; z-index: 40;">
               <button id="calendar_player" class="btn btn-warning"
                       style="height: 30px;"
                       data-toggle="tooltip" title="尚未選擇日期" data-placement="right"
                       data-date=today_str data-date-format="yyyy/mm/dd">
                  <i class="icon-calendar icon-white"></i>
               </button>

               <button class="btn" style="height: 30px;" onclick="startPlayingClickLog();" id="play_btn"
                       data-toggle="tooltip" title="自動播放" title data-placement="right">
                  <i class="icon-play"></i>
               </button>

               <button class="btn" style="height: 30px;" onclick="stopPlayingClickLog();" id="stop_btn"
                       data-toggle="tooltip" title="停止播放" title data-placement="right">
                  <i class="icon-stop"></i>
               </button>
               
               <button class="btn" style="height: 30px;" onclick="stepPlayingClickLog();" id="step_btn"
                       data-toggle="tooltip" title="單步播放" title data-placement="right">
                  <i class="icon-arrow-right"></i>
               </button>
            </div>

            <div id="play_info" style="position: absolute; top: 150px; left: 55px; width: 220px; height: 80px; z-index: 50;
                                       box-shadow: 0px 0px 10px 1px grey; -webkit-border-radius: 5px; -moz-border-radius: 5px; border-radius: 5px">
               <div style="height: 40px; line-height: 40px;" class="chinese-font"><b># of click logs:</b> <span id="total_logs">0</span></div>
               <div style="height: 40px; line-height: 20px; text-align: center; font-size: 12px" class="chinese-font">
                  <b><span id="cur_log">-</span></b>
               </div>
            </div>


         </div>
   </div>
-->

</body>

<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false"></script>
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript">

   // global variables
   var data;      // for detection table
   var data2;
   var begin;     // indicate min X axis
   var end;       // indicate max X axis
   var type;      // indicates X axis scale
   var show_brac = true;    // whether to show brac data
   var show_emotion = true; // whether to show emotion data
   var show_desire = true;  // whether to show desire data
   var cur_options;         // options in use
   var cur_ques_options;
   var data_view;           // for changing hidden columns
   var data_view2;
   var now = new Date();    // current time
   var today = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 0, 0, 0, 0); // today date
   var today_str = dateToString(today);            // today string (yyyy/mm/dd)
   var today_date = today_str.replace(/\//g, '-'); // today string (yyyy-mm-dd)
   // global DataTable variables in order to change selected rows
   var ques_data;
   var NoteAdd_pa_data;
   var NoteAdd_In_data;
   var clickLog_data;
   var coupon_data;
   var regular_ques_data; 
   var random_ques_data; 
   var coping_skill_data;
   var detail_data; 
   // end date of record LineChart
   var record_endDate = new Date(); // default to today
   var status;
   // const value setting
   var brac_max = 100;
   var basic_options = { curveType: 'none', 
                         legend: {position: 'none'},
                         pointSize: 5,
                         tooltip: {isHtml: true} };
   var vAxis_brac = {title: 'Test Result', minValue: 0};
   var vAxis_desire = {maxValue: 3, minValue: -3, title: 'Self Condition'};
   var vAxis_emotion = {maxValue: 3, minValue: -3, title: 'Relationship', textPosition: 'in'};
   var color_brac = "#2266CC";
   var color_desire = "FF9900";
   var color_emotion = "#0F9F49";

   // ui initialize
   $("#uid_title").text(alcoholic.UserId);

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
   $("#join_date").text(alcoholic.JoinDate);
   if(alcoholic.IsDropout == 0){
      $("#drop_out").text("No");
   }
   else{
      $("#drop_out").text("Yes (" + alcoholic.DropOutDate + ")");
   }
   $("#device_id").text(alcoholic.DeviceId);
   $("#current_score").text(alcoholic.CurrentScore);
   $("#current_level").text(Math.floor(parseInt(alcoholic.TotalScore)/20)+"("+(parseInt(alcoholic.TotalScore)%20)*5+"%)");
   $("#used_score").text(alcoholic.UsedScore);
   if(alcoholic.AppVersion == null)
      $("#app_version").text('-');
   else
      $("#app_version").text(alcoholic.AppVersion);
      getstatus();
      $("#current_status").text(status);
   google.load("visualization", "1", {packages:["corechart", "table"]});
   google.setOnLoadCallback(drawAllCharts);
   function getstatus(){
       status=0;
      /*for(var _timestamp in detections_all){
        
              if(detections_all[_timestamp]["Result"]==1 && status!= -10){
                  status-=1;
             }else if(detections_all[_timestamp]["Result"]==1 && status== -10){
                  status=-10;   
             }else if(detections_all[_timestamp]["Result"]==0 && status!= 10){
                 status=parseInt(status)+1;                 
             }else if(detections_all[_timestamp]["Result"]==1 && status== 10){
                 status==10;
             }
              
      }
       */
       status=alcoholic["Position"];
       
       }
   function getToolTip(detection){
      var img = "patients/" + detection["UserId"] + "/" + detection["Timestamp"] + "/IMG_" + detection["Timestamp"] + "_";
      var pimg = "patients/" + detection["UserId"] + "/" + detection["Timestamp"] + "/PIC_" + detection["Timestamp"] + "_";
      var img1 = img + "1.jpg";
      var img2 = img + "2.jpg";
      var img3 = pimg + "0.jpg";
      
      var content = "<img class='blow_img blow_img1' src='" + img1 + "'></img>";
      content += "<img class='blow_img blow_img2' src='" + img2 + "'></img>";
      content += "<img class='blow_img blow_img3' src='" + img3 + "'></img>";

      // debug messages
      var has_debug = (detection["debug"] == null)? false : true;
      var debug_msg = "(No Debug Information)";
      var has_retry = detection["HasVoiceFeedback"] == "1";
      var retry_msg = "<br>(No Retry Voice Record)";
      if(has_debug)
         debug_msg = "<br>" +
                     "Start:<b>" + detection["debug"]["start"] + "</b> " +
                     "End:<b>" + detection["debug"]["end"] + "</b> " + 
                     "FirstVoltage:<b>" + parseFloat(detection["debug"]["avg_pressure"]).toFixed(1) + "</b><br>" +
                     "SecondVoltage:<b>" + detection["debug"]["min_pressure"] + "</b> " +
                     "Init.Voltage:<b>" + detection["debug"]["init_voltage"] + "</b> ";
   /*   if(has_retry){
         var voice_path = "patients/" + detection["UserId"] + "/feedback/" + detection["Timestamp"] + ".3gp";
         retry_msg = "<br>" +
                     "Retry Record: <a href=\"" + voice_path + "\">Download</a>";
      }               
     */    
u=detection["UserId"];
t=detection["Timestamp"];
d=detection["Date"];
      link="<a href='certain_test_detail.php?uid="+u+"&timestamp="+t+"&date="+d+"' title='Deatil of this test'>Detail of this test </a>";
      content += "<div class='blow_time'><b>" + detection["Date"] + " " + detection["Time"] + "</b></div>";
      content += "<div class='blow_data'>Test Result:<b>" + detection["Result"] +
                 //   "Emotion:<b>" + detection["Emotion"] + "</b> " + 
                  //  "Craving:<b>" + detection["Craving"] + "</b> " + 
                   // debug_msg + 
                    link 
                    "</div>";

      // to expand up the tooltip
      if(has_debug){
         content += "<div style='height: 1500px'></div>";
      }
      else{
         content += "<div style='height: 1500px'></div>";
      }
      content += "<div style='height: 1500px;'></div>";
      return content;
   }

   function drawAllCharts(){
      drawRecordLineChart();
      drawQuesRecordLineChart();
      drawQuesRecordLineChart2();
      drawQuestionnaireChart();
      
     // drawCouponTable();
      drawClickLogTable();
      drawTestDetailTable();
      $("#brac_btn").click();
      $("#brac_btn").click();
      drawCouponTable(); 
      $("#desire_btn").click();
      $("#desire_btn").click();
     
      $("#emotion_btn").click();
      $("#emotion_btn").click();
      //drawStoryReadingTable();
      //drawStoryRecordTable();
      drawSalivasTable();
     // drawFacebookTable();
     // drawMap();
      setTimeout(function(){$(".clicklog_img").each(function(){$(this).attr('src', $(this).attr('_src'));})}, 1000);
   }

   function drawRecordLineChart() {
      try{
         data = new google.visualization.DataTable();

    
      }
      catch(err){
         console.log(err.message);
         return;
      }
      data.addColumn('datetime', 'Time');
      data.addColumn('number', 'Test Result');
      data.addColumn({type: 'string', role: 'tooltip', 'p': {'html': true}});

      var i = 0;
      for(var _date in detections){
         for(var slot in detections[_date]){
            var j = 0;

           var _datetime = new Date(_date + ' ' + detections[_date][slot]['Time']);
            data.addRows(1);

            // Time
            data.setValue(i, j++, _datetime);
            // Brac
            data.setValue(i, j++, Math.min(detections[_date][slot]['Result'], brac_max));
            data.setValue(i, j++, getToolTip(detections[_date][slot]));
                            

            i++;
         }
      }  
      //var _start, _end;
      if(i == 1){ // only one data point
         data.addRows(1);
         _start = new Date(data.getValue(0, 0));
         _start.setDate(_start.getDate() - 1);
         _end = new Date(data.getValue(0, 0));
         _end.setDate(_end.getDate() + 1);
         var _date = new Date(data.getValue(0, 0));
         _date.setSeconds(_date.getSeconds() + 1);
         data.setValue(1, 0, _date);
         for(var j = 1; j < 3; j++){
            data.setValue(1, j, data.getValue(0, j));
         }
      }

      data_view = new google.visualization.DataView(data);

      begin = new Date(alcoholic['JoinDate']);
      end = new Date();
      var _options = basic_options;
      _options['hAxis'] = { minValue: begin, 
                            maxValue: end
                          };
      _options['vAxes'] = [vAxis_brac],
      _options['series'] = [{targetAxisIndex: 0, color: color_brac}];
      _options['tooltip']={isHtml: true, trigger:'selection'};
      cur_options = _options;
      var chart = new google.visualization.LineChart(document.getElementById('record_linechart'));
      

      chart.draw(data, _options);

    // var  chart2 = new google.visualization.LineChart(document.getElementById('record_annotation'));
      //   chart2.draw(data, _options);
      $("#week_btn").click(); // change to week-scale
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
         begin = new Date(alcoholic['JoinDate']);
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

      $("#brac_btn").click();
      $("#brac_btn").click();
 //    var  chart2 = new google.visualization.LineChart(document.getElementById('record_annotation'));
   //   chart.draw(data_view, cur_options);
   }

   function toggleYdata(data_type, caller){
      switch(data_type){
         case 'brac':    show_brac = !show_brac;
                         if(show_brac)
                            caller.className = "btn btn-primary chinese-font";
                         else
                            caller.className = "btn chinese-font";
                         break;
      /*   case 'emotion': show_emotion = !show_emotion;
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
                         break;*/
      }

      data_view = new google.visualization.DataView(data);
      var hidden = [];
      if(!show_brac) {hidden.push(1), hidden.push(2)};
     // if(!show_desire) {hidden.push(3), hidden.push(4)};
     // if(!show_emotion) {hidden.push(5), hidden.push(6)};
      data_view.hideColumns(hidden);

      var i = 0;
      cur_options['vAxes'] = [];
      cur_options['series'] = [];
      if(show_brac){
         cur_options['vAxes'].push(vAxis_brac);
         cur_options['series'].push({targetAxisIndex: i++, color: color_brac});
      }
     /* if(show_desire){
         cur_options['vAxes'].push(vAxis_desire);
         cur_options['series'].push({targetAxisIndex: i++, color: color_desire});
      }
      if(show_emotion){
         cur_options['vAxes'].push(vAxis_emotion);
         cur_options['series'].push({targetAxisIndex: i++, color: color_emotion});
      }*/

      var table = new google.visualization.LineChart(document.getElementById('record_linechart'));
      table.draw(data_view, cur_options);
   }

   function drawQuestionnaireChart(){

      // NoteAdd(passive)
      NoteAdd_pa_data = new google.visualization.DataTable();
      NoteAdd_pa_data.addColumn('string', 'Time');
      NoteAdd_pa_data.addColumn('string', 'Week');
      NoteAdd_pa_data.addColumn('string', 'TimeSlot');
      NoteAdd_pa_data.addColumn('string', 'Type');
      NoteAdd_pa_data.addColumn('string', 'Item');
      NoteAdd_pa_data.addColumn('string', 'Impact');
      NoteAdd_pa_data.addColumn('string', 'Description');

      var i = 0;
      for(var timestamp in questionnaires){
        if(questionnaires[timestamp]['IsAfterTest']==1){
         NoteAdd_pa_data.addRows(1);
         NoteAdd_pa_data.setCell(i, 0, questionnaires[timestamp]['Date'] + ' ' + questionnaires[timestamp]['Time'], null, getStyle(150));
         NoteAdd_pa_data.setCell(i, 1, questionnaires[timestamp]['Week'], null, getStyle(0));
         NoteAdd_pa_data.setCell(i, 2, questionnaires[timestamp]['RecordTimeslot'], null, getStyle(0));
         NoteAdd_pa_data.setCell(i, 3, questionnaires[timestamp]['Type'], null, getStyle(0));
         NoteAdd_pa_data.setCell(i, 4, questionnaires[timestamp]['Items'], null, getStyle(0));
         NoteAdd_pa_data.setCell(i, 5, questionnaires[timestamp]['Impact'], null, getStyle(0));
         NoteAdd_pa_data.setCell(i, 6, questionnaires[timestamp]['Description'], null, getStyle(0));

         i++;
         }
      }

      var chart = new google.visualization.Table(document.getElementById('ques_table'));
      chart.draw(NoteAdd_pa_data, {allowHtml: true, page: 'enable', pageSize: 20, width: '900px', sortColumn: 0, sortAscending: false});
      changeTableDate('ques_table', today_date);
      if(questionnaires.length == 0){$("#ques_table").text("No Record!");}

      //NoteAdd initiative   
      NoteAdd_In_data = new google.visualization.DataTable();
      NoteAdd_In_data.addColumn('string', 'Time');
      NoteAdd_In_data.addColumn('string', 'Week');
      NoteAdd_In_data.addColumn('string', 'Recordate');
      NoteAdd_In_data.addColumn('string', 'TimeSlot');
      NoteAdd_In_data.addColumn('string', 'Type');
      NoteAdd_In_data.addColumn('string', 'Item');
      NoteAdd_In_data.addColumn('string', 'Impact');
      NoteAdd_In_data.addColumn('string', 'Description');
                                    


      var i = 0;
      for(var timestamp in questionnaires){  
       if(questionnaires[timestamp]['IsAfterTest']==0){
        NoteAdd_In_data.addRows(1);
        NoteAdd_In_data.setCell(i, 0, questionnaires[timestamp]['Date'] + ' ' + questionnaires[timestamp]['Time'], null, getStyle(150));
        NoteAdd_In_data.setCell(i, 1, questionnaires[timestamp]['Week'], null, getStyle(0));
        NoteAdd_In_data.setCell(i, 2, questionnaires[timestamp]['RecordDate'], null, getStyle(0));
        NoteAdd_In_data.setCell(i, 3, questionnaires[timestamp]['RecordTimeslot'], null, getStyle(0));
        NoteAdd_In_data.setCell(i, 4, questionnaires[timestamp]['Type'], null, getStyle(0));
        NoteAdd_In_data.setCell(i, 5, questionnaires[timestamp]['Items'], null, getStyle(0));
        NoteAdd_In_data.setCell(i, 6, questionnaires[timestamp]['Impact'], null, getStyle(0));
        NoteAdd_In_data.setCell(i, 7, questionnaires[timestamp]['Description'], null, getStyle(0));

        i++;              
        }  
      }

     var chart = new google.visualization.Table(document.getElementById('emotionDIY_table'));
     chart.draw(NoteAdd_In_data, {allowHtml: true, page: 'enable', pageSize: 20, width: '900px', sortColumn: 0, sortAscending: false});
     changeTableDate('emotionDIY_table', today_date);
     if(questionnaires.length == 0){$("#emotionDIY_table").text("No Record!");}


      // Copyskill
      coping_skill_data = new google.visualization.DataTable();
      coping_skill_data.addColumn('string', 'Time');
      coping_skill_data.addColumn('string', 'Week');
      coping_skill_data.addColumn('string', 'TimeSlot');
      coping_skill_data.addColumn('string', 'Skill Type');
      coping_skill_data.addColumn('string', 'Skill Select');
      coping_skill_data.addColumn('string', 'Recreation');
      coping_skill_data.addColumn('string', 'Score');

      var i = 0;
      for(var timestamp in coping_skills){
         coping_skill_data.addRows(1);
         coping_skill_data.setCell(i, 0, coping_skills[timestamp]['Date'] + ' ' + coping_skills[timestamp]['Time'], null, getStyle(150));
         coping_skill_data.setCell(i, 1, coping_skills[timestamp]['Week'], null, getStyle(0));
         coping_skill_data.setCell(i, 2, coping_skills[timestamp]['Timeslot'], null, getStyle(0));
         coping_skill_data.setCell(i, 3, coping_skills[timestamp]['SkillType'], null, getStyle(0));
         coping_skill_data.setCell(i, 4, coping_skills[timestamp]['SkillSelect'], null, getStyle(0));
         coping_skill_data.setCell(i, 5, coping_skills[timestamp]['Recreation'], null, getStyle(0));
         coping_skill_data.setCell(i, 6, coping_skills[timestamp]['Score'], null, getStyle(0));
         i++;
      }

      var chart = new google.visualization.Table(document.getElementById('coping_skill_table'));
      chart.draw(coping_skill_data, {allowHtml: true, page: 'enable', pageSize: 20, width: '900px', sortColumn: 0, sortAscending: false});
      changeTableDate('coping_skill_table', today_date);
      if(coping_skills.length == 0){$("#coping_skill_table").text("No Record!");}

      //RegulareQuestionnaire
      regular_ques_data = new google.visualization.DataTable();
      regular_ques_data.addColumn('string', 'Time');
      regular_ques_data.addColumn('string', 'TimeSlot');
      regular_ques_data.addColumn('string', 'isCorrect');
      regular_ques_data.addColumn('string', 'Selection');
      regular_ques_data.addColumn('string', 'Choose');
      regular_ques_data.addColumn('string', 'Score');


      var i = 0;
      for(var timestamp in question_tests){
         var _q = question_tests[timestamp];
         if(_q['Type']==0){
         regular_ques_data.addRows(1);
         regular_ques_data.setCell(i, 0, question_tests[timestamp]['Date'] + ' ' + question_tests[timestamp]['Time'], null, getStyle(150));
         regular_ques_data.setCell(i, 1, _q['Timeslot'], null, getStyle(0));
         regular_ques_data.setCell(i, 2, _q['isCorrect'], null, getStyle(0));
         regular_ques_data.setCell(i, 3, _q['Selection'], null, getStyle(0));
         regular_ques_data.setCell(i, 4, _q['Choose'], null, getStyle(0));
         regular_ques_data.setCell(i, 5, _q['Score'], null, getStyle(0));
         i++;
         }
      }

      var chart = new google.visualization.Table(document.getElementById('regular_ques_table'));
      chart.draw(regular_ques_data, {allowHtml: true, page: 'enable', pageSize: 20, sortColumn: 0, sortAscending: false});
      changeTableDate('regular_ques_table', today_date);
      if(question_tests.length == 0){$("#emotionManage_table").text("No Record!");}

      // random ques
      random_ques_data = new google.visualization.DataTable();
      random_ques_data.addColumn('string', 'Time');
      random_ques_data.addColumn('string', 'TimeSlot');
      random_ques_data.addColumn('string', 'isCorrect');
      random_ques_data.addColumn('string', 'Selection');
      random_ques_data.addColumn('string', 'Choose');
      random_ques_data.addColumn('string', 'Score');

      var i = 0;
      for(var timestamp in question_tests){
         var _d = question_tests[timestamp];
         if(_d['Type']==1){
         random_ques_data.addRows(1);
         random_ques_data.setCell(i, 0, _d['Date'] + ' ' + _d['Time'], null, getStyle(150));
         random_ques_data.setCell(i, 1, _d['Timeslot'], null, getStyle(0));
         random_ques_data.setCell(i, 2, _d['isCorrect'], null, getStyle(0));
         random_ques_data.setCell(i, 3, _d['Selection'], null, getStyle(0));
         random_ques_data.setCell(i, 4, _d['Choose'], null, getStyle(0));
         random_ques_data.setCell(i, 5, _d['Score'] ,null, getStyle(0));
         i++;
         }
      }

      var chart = new google.visualization.Table(document.getElementById('random_ques_table'));
      chart.draw(random_ques_data, {allowHtml: true, page: 'enable', pageSize: 20, sortColumn: 0, sortAscending: false});
      changeTableDate('random_ques_table', today_date);
      if(question_tests.length == 0){$("#additional_table").text("No Record!");}

      $(".google-visualization-table-table").css('width', '900px') // the hidden table width are not generated correctly

   }

   function drawClickLogTable(){
      clickLog_data = new google.visualization.DataTable();
      clickLog_data.addColumn('string', 'Time');
      clickLog_data.addColumn('string', 'Action');
      
      var i = 0;
      for(var timestamp in clickLogs){
         clickLog_data.addRows(1);
         timestamp_date = timestamp.substr(0, 19);
         clickLog_data.setCell(i, 0, timestamp_date, null, getStyle(150));
         clickLog_data.setCell(i, 1, clickLogs[timestamp]);
         i++;
      }

      clickLog_data.sort({column: 0});

      var table = new google.visualization.Table(document.getElementById('clickLog_table'));
      table.setSelection([{row: 1}]);
      table.draw(clickLog_data, {allowHtml: true, page: 'enable', pageSize: 20, sortAscending: false});

      $(".google-visualization-table-table").css('width', '900px') // the hidden table width are not generated correctly
   }

   function findRowId(_date, tdata){
      //find same _date in column 0 in the DataTable
      var length = tdata.getNumberOfRows();
      for(var i = 0; i < length; i++){
         if(tdata.getValue(i, 0).substr(0, 10) == _date) return i;
      }
      return -1;
   }

   function changeQuesTableDate(_date){
      var id = $("ul#ques_ul li.active a").attr("href").substr(1);
      changeTableDate(id, _date);
   }

   function changeOtherTableDate(_date){
      var id = $("ul#other_ul li.active a").attr('href').substr(1);
      changeTableDate(id, _date);
   }

   function changeTableDate(id, _date){
      var data;
      switch(id){
         case 'ques_table':          tdata = NoteAdd_pa_data;    break;
         case 'emotionDIY_table':    tdata = NoteAdd_In_data;    break;
         case 'regular_ques_table':  tdata = regular_ques_data;  break;
         case 'coping_skill_table':  tdata = coping_skill_data;    break;
         case 'clickLog_block':      tdata = clickLog_data;      break;
         case 'coupon_table':        tdata = coupon_data;        break;
         case 'storyReading_table':  tdata = storyReading_data;  break;
         case 'storyRecord_table':   tdata = storyRecord_data;   break;
         case 'salivas_table':     tdata = salivas_data;     break;
         case 'facebook_table':      tdata = detail_data;      break;
         case 'geo_map':             drawMap(_date);             return;
         default: return;
      }
      rowId = findRowId(_date, tdata);
      if(rowId == -1){ // no data
         return;
      }
      var table = new google.visualization.Table(document.getElementById(id));
      table.draw(tdata, {page: 'enable', pageSize: 20, startPage: Math.floor(rowId / 20), allowHtml: true});
      table.setSelection([{row: rowId}]);
   }

   function drawCouponTable(){
      if(coupons.length == 0){$("#coupon_table").text("No Record!"); return;}
      coupon_data = new google.visualization.DataTable();
      coupon_data.addColumn('string', '時間');
      coupon_data.addColumn('string', '當時兌換點數');
      coupon_data.addColumn('number', '當時剩餘點數');

      var i = 0;
      for(var timestamp in coupons){
         coupon_data.addRows(1);
         coupon_data.setCell(i, 0, coupons[timestamp]["Date"] + " " + coupons[timestamp]["Time"], null, getStyle(150));
         coupon_data.setCell(i, 1, coupons[timestamp]["NumOfCounter"], null, getStyle(0));
         coupon_data.setCell(i, 2, coupons[timestamp]["Remain"], null, getStyle(0));
         i++;
      }

      coupon_data.sort({column: 0});

      var table = new google.visualization.Table(document.getElementById('coupon_table'));
      table.draw(coupon_data, {allowHtml: true, page: 'enable', pageSize: 20, sortAscending: true, sortColumn: 0});
      $(".google-visualization-table-table").css('width', '900px') // the hidden table width are not generated correctly

   }
   function drawQuesRecordLineChart() {
      try{
         data2 = new google.visualization.DataTable();
            
      }
      catch(err){
         console.log(err.message);
         return;
      }
     
      data2.addColumn('datetime', 'Time');
      data2.addColumn('number', 'Selfcondition');
      //data2.addColumn('number', 'Relationship');

      var i = 0;

      for(var _date in date_questionnaires){
      var sum_desire=0; 
      var n_desire=0;
      var sum=0;
      var n=0;
      var k=0;
      var chk=0;
      var _datetime = new Date(_date + ' ' +"00:00:00" );
     
         for(var _timestamp in date_questionnaires[_date]){
        
            if(date_questionnaires[_date][_timestamp]['Type']<6){
           chk=1;
            }
         }
      if(chk ==1){ data2.addRows(1);
      // Time          
      data2.setValue(i, k++, _datetime);
         for(var _timestamp in date_questionnaires[_date]){
             // self condition
            if(date_questionnaires[_date][_timestamp]['Type']<6){
                 sum_desire=sum_desire+parseInt(date_questionnaires[_date][_timestamp]['Impact'])-3;
                 n_desire=n_desire+1;                   
            }
            
            // Relationship
            if(date_questionnaires[_date][_timestamp]['Type']>5){
                 sum=sum+parseInt(date_questionnaires[_date][_timestamp]['Impact'])-3;
                 n=n+1;
            }
         }

            var desire = sum_desire/n_desire;
          if(n_desire!=0){         
                        data2.setValue(i, k++, desire);
          }else{
                k=k+1;
          }                    
                   
                    
      /*      var emotion = sum/n;
           if(n!=0){
               data2.setValue(i, k++, emotion);
           }else{
               k=k+1;
           }*/
            i++;
      }  }
      //var _start, _end;
      if(i == 1){ // only one data point
         data2.addRows(1);
         _start = new Date(data2.getValue(0, 0));
         _start.setDate(_start.getDate() - 1);
         _end = new Date(data2.getValue(0, 0));
         _end.setDate(_end.getDate() + 1);
         var _date = new Date(data2.getValue(0, 0));
         _date.setSeconds(_date.getSeconds() + 1);
         data2.setValue(1, 0, _date);
         for(var j = 1; j < 2 ; j++){
            data2.setValue(1, j, data2.getValue(0, j));
         }
      }

      
      data_view2 = new google.visualization.DataView(data2);
      begin = new Date(alcoholic['JoinDate']);
      end = new Date();
      var ques_options = basic_options;
      ques_options['hAxis'] = { minValue: begin, 
                            maxValue: end
                          };
      ques_options['vAxes'] = [ vAxis_desire, vAxis_emotion],
      ques_options['series'] = [ 
                            {targetAxisIndex: 1, color: color_desire} 
            //                {targetAxisIndex: 2, color: color_emotion}
                            ];
      cur_ques_options = ques_options;
           
      var chart2 = new google.visualization.LineChart(document.getElementById('record_annotation'));
     
      chart2.draw(data2,ques_options);
    // var  chart2 = new google.visualization.LineChart(document.getElementById('record_annotation'));
      //   chart2.draw(data, _options);
      $("#week_btn_ques").click(); // change to week-scale


   }
   function changeXaxis_ques(_type, caller, end){
      type = _type;
      $("#all_btn_ques").removeClass('active');
      $("#mon_btn_ques").removeClass('active');
      $("#week_btn_ques").removeClass('active');
      $("#day_btn_ques").removeClass('active');
      caller.className = caller.className + " active";

      begin = new Date(end);
      //end = new Date();
      if(type == "all"){
         begin = new Date(alcoholic['JoinDate']);
         end = new Date();
      }
      else if(type == "month")
         begin.setDate(begin.getDate() - 30);
      else if(type == "week")
         begin.setDate(begin.getDate() - 7);
      else if(type == "day")
         begin.setDate(begin.getDate() - 2);

      cur_ques_options['hAxis'] = {minValue: begin, maxValue: end, viewWindow: {min: begin, max: end}};
      var chart2 = new google.visualization.LineChart(document.getElementById('record_annotation'));
      chart2.draw(data_view2, cur_ques_options);

      $("#desire_btn").click();
      $("#desire_btn").click();
 //    var  chart2 = new google.visualization.LineChart(document.getElementById('record_annotation'));
   //   chart.draw(data_view, cur_options);
   }

   function toggleYdata_ques(data_type, caller){
      switch(data_type){
       /*  case 'brac':    show_brac = !show_brac;
                         if(show_brac)
                            caller.className = "btn btn-primary chinese-font";
                         else
                            caller.className = "btn chinese-font";
                         break;
         case 'emotion': show_emotion = !show_emotion;
                         if(show_emotion)
                            caller.className = "btn btn-success chinese-font";
                         else
                            caller.className = "btn chinese-font";
                         break;*/
         case 'desire':  show_desire = !show_desire;
                         if(show_desire)
                            caller.className = "btn btn-warning chinese-font";
                         else
                            caller.className = "btn chinese-font";
                         break;
      }

      data_view2 = new google.visualization.DataView(data2);
      var hidden = [];
    //i  if(!show_brac) {hidden.push(1), hidden.push(2)};
      if(!show_desire) {hidden.push(1)};
      if(!show_emotion) {hidden.push(2)};
      data_view2.hideColumns(hidden);

      var i = 0;
      cur_ques_options['vAxes'] = [];
      cur_ques_options['series'] = [];
 /*     if(show_brac){
         cur_options['vAxes'].push(vAxis_brac);
         cur_options['series'].push({targetAxisIndex: i++, color: color_brac});
      }*/
      if(show_desire){
         cur_ques_options['vAxes'].push(vAxis_desire);
         cur_ques_options['series'].push({targetAxisIndex: i++, color: color_desire});
      }
      if(show_emotion){
         cur_ques_options['vAxes'].push(vAxis_emotion);
         cur_ques_options['series'].push({targetAxisIndex: i++, color: color_emotion});
      }

      var table2 = new google.visualization.LineChart(document.getElementById('record_annotation'));
      table2.draw(data_view2, cur_ques_options);
   }
/*
   function drawStoryReadingTable(){
      storyReading_data = new google.visualization.DataTable();
      storyReading_data.addColumn('string', 'Time');
      storyReading_data.addColumn('string', 'Week');
      storyReading_data.addColumn('string', 'Page');

      var i = 0;
      for(var timestamp in storyReadings){
         storyReading_data.addRows(1);
         storyReading_data.setCell(i, 0, storyReadings[timestamp]["Date"] + " " + storyReadings[timestamp]["Time"], null, getStyle(150));
         storyReading_data.setCell(i, 1, storyReadings[timestamp]["Week"], null, getStyle(0));
         storyReading_data.setCell(i, 2, storyReadings[timestamp]["Page"], null, getStyle(0));
         i++;
      }

      storyReading_data.sort({column: 0});

      if(i == 0){$("#storyReading_table").text("No Record!"); return;}
      
      var table = new google.visualization.Table(document.getElementById('storyReading_table'));
      table.draw(storyReading_data, {page: 'enable', pageSize: 20, sortAscending: false, sortColumn: 0, allowHtml: true});
      $(".google-visualization-table-table").css('width', '900px') // the hidden table width are not generated correctly

   }

   function drawStoryRecordTable(){
      storyRecord_data = new google.visualization.DataTable();
      storyRecord_data.addColumn('string', 'Time');
      storyRecord_data.addColumn('string', 'Week');
      storyRecord_data.addColumn('string', 'TimeSlot');
      storyRecord_data.addColumn('string', 'Record Date');
      storyRecord_data.addColumn('string', 'Audio');

      var i = 0;
      for(var timestamp in storyRecords){
         storyRecord_data.addRows(1);
         storyRecord_data.setCell(i, 0, storyRecords[timestamp]["Date"] + " " + storyRecords[timestamp]["Time"], null, getStyle(150));
         storyRecord_data.setCell(i, 1, storyRecords[timestamp]["Week"], null, getStyle(0));
         storyRecord_data.setCell(i, 2, storyRecords[timestamp]["TimeSlot"], null, getStyle(0));
         storyRecord_data.setCell(i, 3, storyRecords[timestamp]["RecordDate"], null, getStyle(0));
         storyRecord_data.setCell(i, 4, "<a href='/soberdiary/" + storyRecords[timestamp]["RecordPath"].substr(3) + "'>Download</a>", null, getStyle(0));
         i++;
      }

      storyRecord_data.sort({column: 0});

      if(i == 0){$("#storyRecord_table").text("No Record!"); return;}
      
      var table = new google.visualization.Table(document.getElementById('storyRecord_table'));
      table.draw(storyRecord_data, {page: 'enable', pageSize: 20, sortAscending: false, sortColumn: 0, allowHtml: true});
      $(".google-visualization-table-table").css('width', '900px') // the hidden table width are not generated correctly

   }

*/
   function drawSalivasTable(){
      salivas_data = new google.visualization.DataTable();
      salivas_data.addColumn('string', 'CassetteId');
      salivas_data.addColumn('string', 'Used Time');
      salivas_data.addColumn('string', 'Used Week');
      salivas_data.addColumn('string', 'CreateDate');

      var i = 0;
      for(var timestamp in salivas){
         salivas_data.addRows(1);
         salivas_data.setCell(i, 0, salivas[timestamp]["CassetteId"], null, getStyle(0));
         salivas_data.setCell(i, 1, salivas[timestamp]["Date"] + " " + salivas[timestamp]["Time"], null, getStyle(150));
         salivas_data.setCell(i, 2, salivas[timestamp]["Week"], null, getStyle(0));
         salivas_data.setCell(i, 3, salivas[timestamp]["CreateDate"], null, getStyle(0));
         i++;
      }

      salivas_data.sort({column: 0});

      if(i == 0){$("#salivas_table").text("No Record!"); return;}
      
      var table = new google.visualization.Table(document.getElementById('salivas_table'));
      table.draw(salivas_data, {page: 'enable', pageSize: 20, sortAscending: false, sortColumn: 0, allowHtml: true});
      $(".google-visualization-table-table").css('width', '900px') // the hidden table width are not generated correctly

   }

   function drawTestDetailTable(){
      detail_data = new google.visualization.DataTable();
      detail_data.addColumn('string', 'Time');
      detail_data.addColumn('string', 'Timestamp');
      detail_data.addColumn('string', 'DeviceID');
      detail_data.addColumn('string', 'CassetteID');
      detail_data.addColumn('string', 'FailedState');
      detail_data.addColumn('string', 'FirstVoltage');
      detail_data.addColumn('string', 'SecondVoltage');
      detail_data.addColumn('string', 'DevicePower');
      detail_data.addColumn('string', 'ColorReading');
      detail_data.addColumn('string', 'ConnectionFailRate');
      detail_data.addColumn('string', 'FailedReason');
      detail_data.addColumn('string', 'Hardwareversion');
      detail_data.addColumn('string', 'Appversion');
      var i = 0;
      for(var timestamp in testdetail){
         detail_data.addRows(1);
         detail_data.setCell(i, 0, testdetail[timestamp]["Date"] + " " + testdetail[timestamp]["Time"], null, getStyle(150));
         detail_data.setCell(i, 1, testdetail[timestamp]["Timestamp"], null, getStyle(1));
	 detail_data.setCell(i, 2, testdetail[timestamp]["DeviceId"], null, getStyle(1));
         detail_data.setCell(i, 3, testdetail[timestamp]["CassetteId"], null, getStyle(0));
         detail_data.setCell(i, 4, testdetail[timestamp]["FailedState"], null, getStyle(0));
         detail_data.setCell(i, 5, testdetail[timestamp]["FirstVoltage"], null, getStyle(0));
         detail_data.setCell(i, 6, testdetail[timestamp]["SecondVoltage"], null, getStyle(0));
         detail_data.setCell(i, 7, testdetail[timestamp]["DevicePower"] , null, getStyle(0));
         detail_data.setCell(i, 8, testdetail[timestamp]["ColorReading"] , null, getStyle(0));
         detail_data.setCell(i, 9, testdetail[timestamp]["ConnectionFailRate"] , null, getStyle(0));
         detail_data.setCell(i, 10, testdetail[timestamp]["FailedReason"], null, getStyle(0));
         detail_data.setCell(i, 11, testdetail[timestamp]["HardwareVersion"], null, getStyle(0));
         detail_data.setCell(i, 12, testdetail[timestamp]["AppVersion"], null, getStyle(0));
         i++;
      }

      detail_data.sort({column: 0});

      if(i == 0){$("#detail_table").text("No Record!"); return;}
      
      var table = new google.visualization.Table(document.getElementById('detail_table'));
      table.draw(detail_data, {page: 'enable', pageSize: 20, sortAscending: false, sortColumn: 0, allowHtml: true});
      //table.style.width='2000px';

      $("#detail_table").css('width', '1050px') // the hidden table width are not generated correctly

   }
/*
   function drawMap(_date){
      var check_date = (_date)? true : false;

      var markers = [];
      for(var timestamp in detections_all){
         var record = detections_all[timestamp];
         if(record['Latitude'] == null) continue;
         if(record['Latitude'] == 999 || record['Latitude'] == 9999) continue;
         if(check_date && record['Date'] != _date) continue;
         else{
            var latLng = new google.maps.LatLng(record['Latitude'], record['Longitude']);
            var _title = record['Date'] + ' ' + record['Time'] + '\nBrac: ' + record['Brac'] +
                         ' Emotion: ' + record['Emotion'] + ' Craving: ' + record['Craving'];
            var marker = new google.maps.Marker({'position': latLng, 'title': _title});

            // find the latest same place marker in previous ones
            var index = -1;
            for(var i = 0; i < markers.length; i++){
               var mk = markers[i];
               if(mk.position.toString() == marker.position.toString())
                  index = i;
            }
            if(index != -1) marker.title += '\n\n' + markers[index].title;

            markers.push(marker);
         }
      }
      if(lengthOf(markers) == 0){$("#geo_map").text("No Record!"); return;}

      var options = {
                      'zoom': 13,
                      'center': markers[0]['position'],
                      'mapTypeId': google.maps.MapTypeId.ROADMAP
                    };

      var map = new google.maps.Map(document.getElementById("geo_map"), options);
      var mcOptions = {maxZoom: 20};
      var mc = new MarkerClusterer(map, markers, mcOptions);
   }
*/
function detail_click(uid,timestamp,d){

      toCertainTestDetail(uid,timestamp,d);
   }
function drawQuesRecordLineChart2() {
      try{
         data3 = new google.visualization.DataTable();
            
      }
      catch(err){
         console.log(err.message);
         return;
      }
     
      data3.addColumn('datetime', 'Time');
      //data2.addColumn('number', 'Selfcondition');
      data3.addColumn('number', 'Relationship');

      var i = 0;

      for(var _date in date_questionnaires){
      var sum_desire=0; 
      var n_desire=0;
      var sum=0;
      var n=0;
      var k=0;
      var chk=0;
      var _datetime = new Date(_date + ' ' +"00:00:00" );
     
         for(var _timestamp in date_questionnaires[_date]){
        
            if(date_questionnaires[_date][_timestamp]['Type']>5){
           chk=1;
            }
         }
      if(chk ==1){ data3.addRows(1);
      // Time          
      data3.setValue(i, k++, _datetime);
         for(var _timestamp in date_questionnaires[_date]){
            
            // Relationship
            if(date_questionnaires[_date][_timestamp]['Type']>5){
                 sum=sum+parseInt(date_questionnaires[_date][_timestamp]['Impact'])-3;
                 n=n+1;
            }
         }

                   
                    
            var emotion = sum/n;
           if(n!=0){
               data3.setValue(i, k++, emotion);
           }else{
               k=k+1;
           }
            i++;
      } 
    }
      //var _start, _end;
      if(i == 1){ // only one data point
         data3.addRows(1);
         _start = new Date(data3.getValue(0, 0));
         _start.setDate(_start.getDate() - 1);
         _end = new Date(data3.getValue(0, 0));
         _end.setDate(_end.getDate() + 1);
         var _date = new Date(data3.getValue(0, 0));
         _date.setSeconds(_date.getSeconds() + 1);
         data3.setValue(1, 0, _date);
         for(var j = 1; j < 2 ; j++){
            data3.setValue(1, j, data3.getValue(0, j));
         }
      }

      
      data_view3 = new google.visualization.DataView(data3);
      begin = new Date(alcoholic['JoinDate']);
      end = new Date();
      var ques_options2 = basic_options;
      ques_options2['hAxis'] = { minValue: begin, 
                            maxValue: end
                          };
      ques_options2['vAxes'] = [ vAxis_emotion],
      ques_options2['series'] = [ 
                         //   {targetAxisIndex: 1, color: color_desire}
                           {targetAxisIndex: 1 , color: color_emotion}
                            ];
      cur_ques_options2 = ques_options2;
           
      var chart3 = new google.visualization.LineChart(document.getElementById('record_annotation2'));
     
      chart3.draw(data3,ques_options2);
    // var  chart2 = new google.visualization.LineChart(document.getElementById('record_annotation'));
      //   chart2.draw(data, _options);
      $("#week_btn_ques2").click(); // change to week-scale


   }
function changeXaxis_ques2(_type, caller, end){
      type = _type;
      $("#all_btn_ques2").removeClass('active');
      $("#mon_btn_ques2").removeClass('active');
      $("#week_btn_ques2").removeClass('active');
      $("#day_btn_ques2").removeClass('active');
      caller.className = caller.className + " active";

      begin = new Date(end);
      //end = new Date();
      if(type == "all"){
         begin = new Date(alcoholic['JoinDate']);
         end = new Date();
      }
      else if(type == "month")
         begin.setDate(begin.getDate() - 30);
      else if(type == "week")
         begin.setDate(begin.getDate() - 7);
      else if(type == "day")
         begin.setDate(begin.getDate() - 2);

      cur_ques_options['hAxis'] = {minValue: begin, maxValue: end, viewWindow: {min: begin, max: end}};
      var chart3 = new google.visualization.LineChart(document.getElementById('record_annotation2'));
      chart3.draw(data_view3, cur_ques_options2);

      $("#emotion_btn").click();
      $("#emotion_btn").click();
 //    var  chart2 = new google.visualization.LineChart(document.getElementById('record_annotation'));
   //   chart.draw(data_view, cur_options);
}

   function toggleYdata_ques2(data_type, caller){
      switch(data_type){
       /*  case 'brac':    show_brac = !show_brac;
                         if(show_brac)
                            caller.className = "btn btn-primary chinese-font";
                         else
                            caller.className = "btn chinese-font";
                         break;*/
         case 'emotion': show_emotion = !show_emotion;
                         if(show_emotion)
                            caller.className = "btn btn-success chinese-font";
                         else
                            caller.className = "btn chinese-font";
                         break;
 /*        case 'desire':  show_desire = !show_desire;
                         if(show_desire)
                            caller.className = "btn btn-warning chinese-font";
                         else
                            caller.className = "btn chinese-font";
                         break;*/
      }

      data_view3 = new google.visualization.DataView(data3);
      var hidden = [];
    //i  if(!show_brac) {hidden.push(1), hidden.push(2)};
      if(!show_desire) {hidden.push(1)};
      if(!show_emotion) {hidden.push(2)};
      data_view2.hideColumns(hidden);

      var i = 0;
      cur_ques_options['vAxes'] = [];
      cur_ques_options['series'] = [];
 /*     if(show_brac){
         cur_options['vAxes'].push(vAxis_brac);
         cur_options['series'].push({targetAxisIndex: i++, color: color_brac});
      }
      if(show_desire){
         cur_ques_options['vAxes'].push(vAxis_desire);
         cur_ques_options['series'].push({targetAxisIndex: i++, color: color_desire});
      }*/
      if(show_emotion){
         cur_ques_options['vAxes'].push(vAxis_emotion);
         cur_ques_options['series'].push({targetAxisIndex: i++, color: color_emotion});
      }

      var table3 = new google.visualization.LineChart(document.getElementById('record_annotation2'));
      table3.draw(data_view3, cur_ques_options);
   }
</script>

</html>
