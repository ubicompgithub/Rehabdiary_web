<?php

   // check if the user has logged in
   require_once('check_session.php');
   check_session_with_target('manage.php');

   // if no UserId is input, return to manage.php
   $uid = $_GET['uid'];
   $times=$_GET['timestamp'];
   $date=$_GET['date'];
  
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
   $i=1;
   while(file("patients/{$uid}/{$times}/IMG_{$times}_{$i}.jpg")!=false){
       $i++;
       }
       
   $j=0;
   while(file("patients/{$uid}/{$times}/PIC_{$times}_{$j}.jpg")!=false){
       $j++;
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

  ga('create', 'UA-41411079-1', '140.112.30.171');
  ga('send', 'pageview');

</script>

<!-- header -->
<?php include 'header.php';?>

<?php include_once 'utility.php';?>

<?php

   include_once('db_utility.php');
   $uid = $target["UserId"];



   // all data
   $detections     = getTableData($uid, "TestResult");
 //  $alldetections = getTableData($uid, "NoteAdd");
   $all_detections     = getTableData_detection_date($uid, "TestResult");
  // $alldetections_date = getTableData_Date($uid, "NoteAdd");
 //  $additionals    = getTableData($uid, "AdditionalQuestionnaire");
  // $question_tests    = getTableData($uid, "QuestionTest");
   //$coping_skills  = getTableData($uid, "CopingSkill");
   $testdetail = getTableData($uid, "TestDetail");
   //$exchanges      = getTableData($uid, "ExchangeHistory");
   //$testdetail      = getTableData($uid, "Facebook");
   //$storyReadings  = getTableData($uid, "StorytellingReading");
  // $storyRecords   = getTableData($uid, "StorytellingRecord");
  // $storyTests     = getTableData($uid, "StorytellingTest");

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
   var detections     = <?php echo json_encode($valid_detections)?>;
   var alldetections     = <?php echo json_encode($all_detections)?>;
   var testdetail = <?php echo json_encode($testdetail)?>;
   var tstamp =<?php echo json_encode($times)?>;
   var _date=<?php echo json_encode($date)?>;
   var _UID = <?php echo json_encode($uid)?>;
   var picnum=<?php echo json_encode($i)?>;
   var testpicnum=<?php echo json_encode($j)?>;
   var othertest_data;
</script>

<div class="container">

   <div style="width: 900px; margin: 0px auto; position: relative;">
      <h2 style="text-align: center;"> <b id="uid_title"></b></h2>
      <div class="page-header"></div><div id =verifyFacePicNum></div>
      <script>
      var i=1;
      var img = "patients/" +_UID + "/" + tstamp + "/IMG_" +tstamp + "_";
      var pimg = "patients/" +_UID + "/" + tstamp + "/PIC_" +tstamp + "_";
     // var fso = new ActiveXObject("Scripting.FileSystemObject");
     if(picnum != 1){
       for (var i=1;i<picnum;i++){
         document.write("<img src="+img+i+".jpg >");
       }
     }else{
      document.write("No Data!!");
     }

      
      document.getElementById('verifyFacePicNum').innerHTML='<h3> Pictures of Paitient: '+(picnum-1)+'</h3>';
    
      </script>

      <div class="page-header"></div><div id=verifyTestPicNum></div>
      <div style="position: relative">
      </div>
      <script>
     // var fso = new ActiveXObject("Scripting.FileSystemObject");
     for (var i=0;i<testpicnum;i++){
      document.write("<img src="+pimg+i+".jpg >");
      }
     if(testpicnum<6){
	
      document.getElementById('verifyTestPicNum').innerHTML='<h3 style="color:#FF0000">Pictures of Test Paper:  '+testpicnum+' Color reading: '+testdetail[tstamp]['ColorReading']+'</h3>';

     }else{
          document.getElementById('verifyTestPicNum').innerHTML='<h3>Pictures of Test Paper:  '+testpicnum+' Color reading: '+testdetail[tstamp]['ColorReading']+'</h3>';
        }
      </script>
<h3>Other Tests Today </h3>
      <div class="page-header" id="other_test"></div>
      <div style="position: relative">
      </div>
</div>
</body>

<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false"></script>
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript">
//make title
var page_title=document.getElementById('uid_title')
page_title.innerHTML='<h2>UserID: '+_UID+'</h2><br><h2>Date: '+_date+' Time:'+alldetections[_date][tstamp]['Time']+' Timestamp:'+alldetections[_date][tstamp]['Timestamp']+'</h2>';
   google.load("visualization", "1", {packages:["corechart", "table"]});
   google.setOnLoadCallback(drawtabletoday);


function drawtabletoday(){
      
      // other test
      othertest_data = new google.visualization.DataTable();
      othertest_data.addColumn('string', 'Time');
     
      othertest_data.addColumn('string', 'Result');
      othertest_data.addColumn('string', 'CassetteId');
      othertest_data.addColumn('string', 'DeviceId');
      othertest_data.addColumn('string', 'GoToPictures');

      var i = 0;
      for(var timestamp in alldetections[_date]){
        link="<a href='certain_test_detail.php?uid="+_UID+"&timestamp="+timestamp+"&date="+_date+"'>Go to Picture</a>";
         othertest_data.addRows(1);
         othertest_data.setCell(i, 0, alldetections[_date][timestamp]['Time'], null, getStyle(150));
         
         othertest_data.setCell(i, 1, alldetections[_date][timestamp]['Result'], null, getStyle(0));
         othertest_data.setCell(i, 2, alldetections[_date][timestamp]['CassetteId'], null, getStyle(0));
         othertest_data.setCell(i, 3, alldetections[_date][timestamp]['DeviceId'], null, getStyle(0));
         othertest_data.setCell(i, 4, link, null, getStyle(0));

         i++;
      }

      var chart = new google.visualization.Table(document.getElementById('other_test'));
      chart.draw(othertest_data, {allowHtml: true, page: 'enable', pageSize: 10, width: '900px', sortColumn: 0, sortAscending: false});
    
      if(othertest_data.length == 0){$("#other_test").text("No Record!");}
    
    
    
    
    
    
    
    
    
    
    
    
    
    }
</script>

</html>
