<?php

   // check if the user has logged in
   require_once('check_session.php');
   check_session_with_target('index.php');

?>

<html>
<head>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<script src="js/jquery-1.10.0.min.js"></script>
<script src="js/jquery-ui_1.10.1.js"></script>
<script src="js/bootstrap-datepicker.js"></script>
<link rel="stylesheet" type="text/css" href="css/jquery-ui_1.10.1.css">
<link rel="stylesheet" type="text/css" href="css/index.css">
<link rel="stylesheet" type="text/css" href="css/datepicker.css">

</head>

<body>

<!-- For Google Analytics-->
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-41411079-1', '140.112.30.171');
  ga('send', 'pageview');

</script>

<!-- header -->
<?php include 'header.php';?>
<?php include 'config.php';?>

<?php

   //get current date
   $now = date('Y-m-d');
   
   //get Alcoholics data from database
   include_once('connect_db.php');
   $conn = connect_to_db();
   $query = "SELECT * FROM  Patient ORDER BY `UserId` ASC";
   $result_all = mysql_query($query);
   $alcoholics = array();
   $alcoholic_names = array();
   while($row = mysql_fetch_assoc($result_all)){
      $alcoholics[] = $row;
      $alcoholic_names[] = $row["UserId"];
   }
   
   //get Detections data from database
   $query_detection = "SELECT * FROM `TestResult` WHERE `UserId` IN (SELECT `UserId` FROM `Patient`) ORDER BY `Timestamp` ASC";
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

   //get EmotionDIY
   //$query_emotionDIY = "SELECT * FROM `EmotionDIY` ORDER BY `Timestamp` ASC";
   //$result_emotionDIY = mysql_query($query_emotionDIY);
   //$emotionDIYs = array();
   //while($row = mysql_fetch_assoc($result_emotionDIY)){
     // $emotionDIYs[$row["UserId"]][str_replace("-", "/", $row["Date"])][$row["Time"]] = $row;
   //}

   //get EmotionManage
   //$query_emotionManage = "SELECT * FROM `EmotionManagement` ORDER BY `Timestamp` ASC";
   //$result_emotionManage = mysql_query($query_emotionManage);
   //$emotionManages = array();
   //while($row = mysql_fetch_assoc($result_emotionManage)){
    //  $emotionManages[$row["UserId"]][str_replace("-", "/", $row["Date"])][$row["Time"]] = $row;
  // }

   //get Questionnaire
   $query_Questionnaire = "SELECT * FROM `TestResult` WHERE `UserId` IN (SELECT `UserId` FROM `Patient`) ORDER BY `Timestamp` ASC";
   $result_Questionnaire = mysql_query($query_Questionnaire);
   $questionnaires = array();
   while($row = mysql_fetch_assoc($result_Questionnaire)){
      $questionnaires[$row["UserId"]][str_replace("-", "/", $row["Date"])][$row["Time"]]= $row;
   }
   //get noteadd
   $query_NoteAdd = "SELECT * FROM `NoteAdd` WHERE `UserId` IN (SELECT `UserId` FROM `Patient`) ORDER BY `Timestamp` ASC";
   $result_NoteAdd = mysql_query($query_NoteAdd);
   $noteadds = array();
   while($row = mysql_fetch_assoc($result_NoteAdd)){
   $noteadds[$row["UserId"]][str_replace("-", "/", $row["Date"])][$row["Time"]] = $row;
   }
   //get random question data
   $query_random_ques = "SELECT * FROM `QuestionTest` WHERE `UserId` IN (SELECT `UserId` FROM `Patient`) ORDER BY `Timestamp` ASC";
   $result_random_ques = mysql_query($query_random_ques);
   $random_ques = array();
   while($row = mysql_fetch_assoc($result_random_ques)){
    $random_ques[$row["UserId"]][str_replace("-", "/", $row["Date"])][$row["Time"]] = $row;
   }
   //get Coping Skill data
   $query_copingskill_ques = "SELECT * FROM `CopingSkill` WHERE `UserId` IN (SELECT `UserId` FROM `Patient`) ORDER BY `Timestamp` ASC";
   $result_copingskill_ques = mysql_query($query_copingskill_ques);
   $copingskill_ques = array();
   while($row = mysql_fetch_assoc($result_copingskill_ques)){
    $copingskill_ques[$row["UserId"]][str_replace("-", "/", $row["Date"])][$row["Time"]] = $row;
   } 

   mysql_close($conn);

   include_once('utility.php');

  // only retrieve valid records in each time span
   $detections_valid = detection_prime($detections);

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

?>
<script language="javascript" type="text/javascript">
   //pass data to client
   var alcoholics = <?php echo json_encode($alcoholics) ?>;
   var detections = <?php echo json_encode($detections) ?>;
   var detections_valid = <?php echo json_encode($detections_valid) ?>;
   var alcoholic_names = <?php echo json_encode($alcoholic_names)?>;
   var blocks = <?php echo json_encode($blocks)?>;
   //var emotion_diy = <?php echo json_encode($emotionDIYs)?>;
   //var emotion_manage = <?php echo json_encode($emotionManages)?>;
   var questionnaire = <?php echo json_encode($questionnaires)?>;
   var noteadd = <?php echo json_encode($noteadds)?>;
   var random_ques = <?php echo json_encode($random_ques)?>;
   var copingskill_ques = <?php echo json_encode($copingskill_ques)?>;
</script>

<div class="container" style="width: 900px; position: relative;">

   <div id="title" ><h2>Daily</h2></div>
   <div id="block_time"></div>
   <div id="tab_block" style="width: 700px; margin: 0px auto;" class="tabbable">
      <ul class="nav nav-tabs" id="tab">
	 <li class="active"><a href="#pie_block" data-toggle="tab">Tests</a></li>
         <li><a href="#questionnaire_block" data-toggle="tab">Questionnaires(N)</a></li>
      </ul>

      <div id="statistics" class="tab-content">
         <div class="tab-pane fade active in" id="pie_block" style="width: 700px; height: 500px; margin: 0px auto; position: relative">
            <div id="chart_div" style="width: 700px; height: 500px;"></div>
            <div id="table_div" style="width: 150px; position: absolute; left: 550px; top: 100px;"></div>
         </div>
 
         <div class="tab-pane fade" id="questionnaire_block" style="width: 700px; height: 500px; margin: 0px auto; position: relative">
            <div id="ques_table" style="width: 400px"></div>
            <div id="ques_name_table" style="width: 200px; position: absolute; left: 450px; top: 0px;"></div>
         </div>
      </div>
   </div>
   <div  id="block_button" class="btn-group btn-group-vertical hidden" style="position: absolute; left: 0px; top: 100px;">
      <button id="calendar" class="btn btn-warning" style="height: 30px;" data-toggle="tooltip" title="Change Date" title data-placement="top"
         data-date=today_str data-date-format="yyyy/mm/dd">
         <i class="icon-calendar icon-white"></i>
      </button>
      <button class="btn" id="morning_btn" onclick="changeBlockId('1', curDay);">早</button>
      <button class="btn" id="afternoon_btn" onclick="changeBlockId('2', curDay);">午</button>
      <button class="btn" id="night_btn" onclick="changeBlockId('3', curDay);">晚</button>
      <button class="btn" id="allday_btn" onclick="changeBlockId('4', curDay);">全</button>
   </div>

   <form  id="to-patient" action="patient_newUI.php" method="post">
   <input id="patientNameInput" type="hidden" name="patientName" value="">
   </form>
      
</body>

<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript">

   // global variables
   var pass = 0, fail = 0, very_fail = 0, skipped = 0, dropped = 0; // number of each state
   var pass_pp = [], fail_pp = [], very_fail_pp = [], skipped_pp = [], dropped_pp = []; // names of each state
   var questionnaire_pp = [], noteactive_pp = [], random_ques_pp = []; regular_ques_pp = []; copingskill_ques_pp = []; // names of each type of questionnaire
   var max_blockId = 0; // do not accept higher block id
   var init = 0; // indicate whether the pie chart is already rendered
   var now = new Date(); // current time
   var today = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 0, 0, 0, 0); // today date
   var today_str = dateToString(today); // today string
   var curDay = today_str; // draw charts based on this day
   var blockId = 0; // draw charts based on this blockId

   // ui initialize
   $("#daily").addClass("active");
   $("#calendar").tooltip();
   $("#calendar").datepicker({
      // disable future date
      onRender: function(_date){
         return _date.valueOf() > today.valueOf() ? 'disabled' : '';
      }
   }).on('changeDate', function(ev){
      // change date when selected
      changeBlockId(blockId, dateToString(ev.date));
      $(".datepicker").fadeOut();
   });

   // find the current block time
   
   var hour = now.getHours();
   for(var id in blocks){
      if(hour <= blocks[id]['End']){ // the blocks should be sorted
         blockId = id;
         break;
      }
   }

   max_blockId = blockId;
   if(max_blockId < 2) $("#afternoon_btn").addClass("disabled");
   if(max_blockId < 3) $("#night_btn").addClass("disabled");

   google.load("visualization", "1", {packages:["corechart", "table"]});
   google.setOnLoadCallback(function(){changeBlockId(blockId, today_str);});

// render the pie block, and questionnaire table according to the _blockId and curDay
function changeBlockId(_blockId, _curDay){
   if(_curDay == today_str && _blockId > max_blockId && _blockId != 4)
      if(curDay != today_str)
         _blockId = max_blockId;
      else
         return;
   if(init != 0){
      if(_curDay == curDay && _blockId == blockId) return;
   }
   init = 1;
   
   // initialize
   pass = 0; very_fail = 0; skipped = 0, dropped = 0; // number of each state
   pass_pp = []; fail_pp = []; very_fail_pp = []; skipped_pp = [], dropped_pp = []; // names of each state
   questionnaire_pp = []; noteactive_pp = []; random_ques_pp = []; regular_ques_pp= []; copingskill_ques_pp = []; // names of each type of questionnaire
   blockId = _blockId;
   curDay = _curDay;

   // reset ui
   $("#morning_btn").removeClass("btn-info");
   $("#afternoon_btn").removeClass("btn-info");
   $("#night_btn").removeClass("btn-info");
   $("#allday_btn").removeClass("btn-info");
   $("#table_div").text("")
   $("#ques_name_table").text("");

   switch(blockId){
      case "1": 
              $("#morning_btn").addClass("btn-info");
              break;
      case "2": 
              $("#afternoon_btn").addClass("btn-info");
              break;
      case "3": 
              $("#night_btn").addClass("btn-info"); 
              break;
      case "4":
              $("#allday_btn").addClass("btn-info");        
              break;
      default:
              console.log("Unknown blockId: " + blockId);
   }
   
   if(curDay == today_str){
      if(max_blockId < 2) $("#afternoon_btn").addClass("disabled");
      if(max_blockId < 3) $("#night_btn").addClass("disabled");
   
   }
   else{
      $("#morning_btn").removeClass("disabled")
      $("#afternoon_btn").removeClass("disabled")
      $("#night_btn").removeClass("disabled")
   }
   
   // make title
   
   var block_time = document.getElementById('block_time');
   var _date = curDay; //now.toISOString().substr(0,10).replace(/\-/g, '/');
   var _time = blocks[blockId]['Name'];
   var _range = '(' + blocks[blockId]['Start'] + ':00-' +  blocks[blockId]['End'] + ':59)';
   block_time.innerHTML = '<h3>' + _date + ' ' + _time + ' ' + _range + '</h3>';

   // init
   var found = {};
   for(var i in alcoholic_names){
      found[alcoholic_names[i]] = false;
   }

   // parse detections_valid
   var fail_thresh = <?php echo $LAPSE_THRESHOLD ?>;
   var very_fail_thresh = <?php echo $RELAPSE_THRESHOLD ?>;
   var patient_num = alcoholics.length;
   for(var id in detections_valid){
      // for each patient (appear in detections)
      var records = detections_valid[id];
      for(var _date in records){
         // for each day
         if(_date == curDay){ // current day
            for(var slot in records[_date]){
               // for each slot
               var hour = records[_date][slot]['Time'].substr(0,2);
               if(hour >= blocks[blockId]['Start'] && hour <= blocks[blockId]['End']){
                  var  result = records[_date][slot]['Result'];
                  if(result == 0){
                     pass++;
                     pass_pp.push(id);
                     found[id] = true;
                  }
                  else if(result == 1){
                     very_fail++;
                     very_fail_pp.push(id);
                     found[id] = true;
                  }
                  else{
                     very_fail++;
                     very_fail_pp.push(id);
                     found[id] = true;
                  }
               }
            } // end of each slot
         } // end of the current day
      } // end for each day
   } // end for each patient
   // find who have been dropped
   for(var i in alcoholics){
      if(alcoholics[i]['DropOut'] == 1 && alcoholics[i]['DropOutDate'].replace(/-/g,'/') <= curDay){
         dropped++;
         dropped_pp.push(alcoholics[i]['UserId']);
         found[alcoholics[i]['UserId']] = true;
      }
   }
   skipped = patient_num - pass - fail - very_fail - dropped;
   for(var i in alcoholic_names){
      if(found[alcoholic_names[i]] == false) skipped_pp.push(alcoholic_names[i]);
   }
   
   for(var name in questionnaire){
      var records = questionnaire[name][curDay];
      for(var time in records){
         var hour = time.substr(0,2);
         if(hour >= blocks[blockId]['Start'] && hour <= blocks[blockId]['End'] && records[time]['IsFilled'] == '1'){
            if(questionnaire_pp.indexOf(name) == -1)
               questionnaire_pp.push(name);
         }

      }
   }
   
   for(var name in noteadd){
      var records = noteadd[name][curDay];
      for(var time in records){
         if(records[time]['Selection'] == "-1") continue; // SelfHelpCounter dummy data
         var hour = time.substr(0,2);
         if(hour >= blocks[blockId]['Start'] && hour <= blocks[blockId]['End']){
            if(noteactive_pp.indexOf(name) == -1 && records[time]['IsAfterTest']== 0)
               noteactive_pp.push(name);
         }
      }
   }


   for(var name in random_ques){
      var records = random_ques[name][curDay];
      for(var time in records){
        if(records[time]['Selection'] == "-1") continue; // SelfHelpCounter dummy data
        var hour = time.substr(0,2);
        if(hour >= blocks[blockId]['Start'] && hour <= blocks[blockId]['End']){
          if(random_ques_pp.indexOf(name) == -1 && records[time]['Type']==0)
            random_ques_pp.push(name);
        }
       }
    } 

    for(var name in random_ques){
        var records = random_ques[name][curDay];
        for(var time in records){
        if(records[time]['Selection'] == "-1") continue;
        var hour = time.substr(0,2);
        if(hour >= blocks[blockId]['Start'] && hour <= blocks[blockId]['End']){
        if(regular_ques_pp.indexOf(name) == -1 && records[time]['Type']==1)
        regular_ques_pp.push(name);
        }
    }
  }
   
   for(var name in copingskill_ques){
      var records = copingskill_ques[name][curDay];
      for(var time in records){
         if(records[time]['Choose'] == "-1") continue; // SelfHelpCounter dummy data
         var hour = time.substr(0,2);
         if(hour >= blocks[blockId]['Start'] && hour <= blocks[blockId]['End']){
            if(copingskill_ques_pp.indexOf(name) == -1)
               copingskill_ques_pp.push(name);
         }
      }
   }
   
  
   drawPieChart();
   showQuesTable();
   $("#block_button").removeClass('hidden');
   
}

   // for drawing chart, according to global variables
   function drawPieChart(){
      var data = google.visualization.arrayToDataTable([
         ['Category', 'number'],
         ['Pass',         pass],
         ['fail',        fail],
         ['Fail',   very_fail],
         ['Skipped',      skipped],
         ['Dropped',      dropped],
      ]);

      var options = {
         title: 'Result',
         colors: ['#109618', '#FF9900', '#DC3912', 'grey', 'black'],
         width: 700,
         height: 500
      };

      var chart = new google.visualization.PieChart(document.getElementById('chart_div'));
      function selectHandler() {
         var selectedItem = chart.getSelection()[0];
         if (selectedItem) {
            switch(selectedItem.row){
               case 0: 
                       showTable('Pass', pass_pp);
                       break;
               case 1: 
                       showTable('fail', fail_pp);
                       break;
               case 2: 
                       showTable('Fail', very_fail_pp);
                       break;
               case 3: 
                       showTable('Skipped', skipped_pp);
                       break;
               case 4: 
                       showTable('Dropped', dropped_pp);
                       break;
            }
         }
      } 
      google.visualization.events.addListener(chart, 'select', selectHandler);
      chart.draw(data, options);
   }

   // for showing table, according to state and names
   function showTable(state, names){
      var data = new google.visualization.DataTable();
      data.addColumn('string', state);
      data.addRows(names.length);
      var idx = 0;
      for(var i = 0; i < names.length; i++){
         if(names[i].substr(0, 7) === "rehab_0")
            data.setCell(idx++, 0, names[i], null, {style: 'text-align: center; font-weight: bold;'});
      }
      for(var i = 0; i < names.length; i++){
         if(names[i].substr(0, 7) !== "rehab_0")
            data.setCell(idx++, 0, names[i], null, {style: 'text-align: center;'});
      }
      function selectHandler(){
         var selectedItem = table.getSelection()[0];
         var uid = data.getFormattedValue(selectedItem.row, 0);
         toPatientDetail(uid);
      }

      var table = new google.visualization.Table(document.getElementById('table_div'));
      google.visualization.events.addListener(table, 'select', selectHandler);
      table.draw(data, {allowHtml: true});
   }

   function showQuesTable(){
      var _style = {style: 'text-align: center; width: 200px; font-family: "微軟正黑體", "Microsoft JhengHei", ' +
                           '"Helvetica Neue", Helvetica, Arial, sans-serif;'};

      var data = new google.visualization.DataTable();
      data.addColumn('string', 'Type');
      data.addColumn('number', 'Number');
      data.addRows(5);
      data.setCell(0, 0, "<b>活動問卷(測試後)</b>", null, _style);
      data.setCell(1, 0, "<b>活動問卷(主動加)</b>", null, _style);
      data.setCell(2, 0, "<b>常態問題</b>", null, _style);
      data.setCell(3, 0, "<b>隨機式問題</b>", null, _style);
      data.setCell(4, 0, "<b>應對技巧</b>", null, _style);
      data.setCell(0, 1, questionnaire_pp.length, null, _style);
      data.setCell(1, 1, noteactive_pp.length, null, _style);
      data.setCell(2, 1, regular_ques_pp.length, null, _style);
      data.setCell(3, 1, random_ques_pp.length, null, _style);
      data.setCell(4, 1, copingskill_ques_pp.length, null, _style);
      //data.setCell(2, 1, emotion_manage_pp.length, null, _style);

      function selectHandler(){
         var selectedItem = table.getSelection()[0];
         if(selectedItem !== undefined){
            switch(selectedItem.row){
               case 0: // questionnaire
                       showQuesNameTable("活動問卷", questionnaire_pp);
                       break;
               case 1: // noteactive
                       showQuesNameTable("活動問卷(主動加)", noteactive_pp);
                       break;
               case 2: // regular_ques
                       showQuesNameTable("常態問題", regular_ques_pp);
                       break;         
               case 3: // random ques
                       showQuesNameTable("隨機式問題", random_ques_pp);
                       break;
               case 4: // copingskill ques
                       showQuesNameTable("應對技巧", copingskill_ques_pp);
                       break;        
            }
         }         
      }

      var table = new google.visualization.Table(document.getElementById('ques_table'));
      google.visualization.events.addListener(table, 'select', selectHandler);
      table.draw(data, {allowHtml: true, width: '400px', sort: 'disable'});
   }

   // for showing questionnaire name table
   function showQuesNameTable(type, names){
      if(names.length == 0){
         $("#ques_name_table").text("");
         return;
      }

      var data = new google.visualization.DataTable();
      data.addColumn('string', type);
      data.addRows(names.length);
      for(var i = 0; i < names.length; i++){
         data.setCell(i, 0, names[i], null, {style: 'text-align: center;'});
      }
      function selectHandler(){
         var selectedItem = table.getSelection()[0];
         var uid = data.getFormattedValue(selectedItem.row, 0);
         toPatientDetail(uid);
      }

      var table = new google.visualization.Table(document.getElementById('ques_name_table'));
      google.visualization.events.addListener(table, 'select', selectHandler);
      table.draw(data, {allowHtml: true});
   }

   function UserId2Name(UserId){
      for(var i = 0; i < alcoholics.length; i++){
         if(alcoholics[i].UserId == UserId)
            return alcoholics[i].Name;
      }
      return "Not Found";
   }

</script>

</html>
