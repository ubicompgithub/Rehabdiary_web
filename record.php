<?php

   // check if the user has logged in
   require_once('check_session.php');
   check_session_with_target('record.php');

?>

<html>
<head>

<title>Patients List</title>
<meta charset="UTF-8">
<script src="js/jquery-1.10.0.min.js"></script>
<script src="js/jquery-ui_1.10.1.js"></script>
<link rel="stylesheet" type="text/css" href="css/jquery-ui_1.10.1.css">
<link rel="stylesheet" type="text/css" href="css/record.css">

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
<?php include 'config.php';?>

<?php
   
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


   //get questionnaire data
   $query_ques = "SELECT * FROM `NoteAdd` WHERE `UserId` IN (SELECT `UserId` FROM `Patient`) ORDER BY `Timestamp` ASC";
   $result_ques = mysql_query($query_ques);
   $ques = array();
   while($row = mysql_fetch_assoc($result_ques)){
       $ques[$row["UserId"]][str_replace("-","/",$row["Date"])][$row["Time"]]= $row;
   }
   mysql_close($conn);

   include_once('utility.php');

   //only retrieve valid records in each time span
   $detections_valid = detection_prime($detections);

?>
<script language="javascript" type="text/javascript">
   //pass Alcoholics & Detections data to client
   var ques = <?php echo json_encode($ques) ?>;
   var alcoholics = <?php echo json_encode($alcoholics) ?>;
   var detections_valid = <?php echo json_encode($detections_valid) ?>;
   var alcoholic_names = <?php echo json_encode($alcoholic_names)?>;
</script>

<div class="container">

   <div id="dummy" style="position: fixed; width: 100%; height: 110px; background-color: white; z-index: 1; top: 40px;"></div>
   <div id="title" ><h2>Records</h2></div>

   <!--div id="search-bar">
      <div id="search-list">
         <div id="patient-text">Choose a Patient:</div>
         <div id="patient-list">
         </div>
      </div>
      <div class="input-append">
         <input id="patient-name-input" class="span2" type="text" placeholder="Patient UserId" style="height:30px;"
          onkeypress="if(event.keyCode==13) PatientDetail();">
         <button class="btn" type="button" onclick="PatientDetail()">Search</button>
      </div>
   </div-->

   <div id="data-table">
      
      <!-- date-row -->
      <div id="date-div" style="position: fixed; height: 44px; width: 100%; top: 100px; z-index: 2;">
         <!-- left arrow -->
         <div id="date-button" class="pagination pagination-large">
            <ul onclick="dateToLeft()">
               <li><a>&laquo;</a></li>
               <li><a style="display: none">dummy</a></li>
            </ul>
         </div>
         <!-- dates -->
         <div id="date-row" class="pagination pagination-large" style="float: left; margin: 0px; width: 588px;">
            <ul>
            </ul>
         </div>
         <!-- right arrow -->
         <div id="date-button" class="pagination pagination-large">
            <ul onclick="dateToRight()">
               <li><a style="display: none">dummy</a></li>
               <li id="rarrow" class="disabled"><a>&raquo;</a></li>
            </ul>
         </div>
      </div>
      <!-- end of date-row -->

      <div id="dummy" style="width: 100%; height: 100px; background-color: white;"></div>

      <!-- data-table -->
      <div id="data-div" style="position: relative; left: -50px;">
         <!-- user column-->
         <div id="patient-name-column" style="width:100px; float: left;">
         </div>
         <!-- detection column-->
         <div id="patient-data" style="float: left; width: 616px;">
         </div>
      </div>
   </div>


</div><!--container end-->

<script language="javascript" type="text/javascript">

// map date string to day of week
function string2Day(datestring){
   var text = ['日', '一', '二', '三', '四', '五', '六'];
   var _date = new Date();
   _date.setYear(datestring.substr(0, 4));
   _date.setMonth(datestring.substr(5, 2)-1);
   _date.setDate(datestring.substr(8, 2));
   return text[_date.getDay()];   
}

// get the recent 'num' of dates in array
function getRecentDayArray(num, cur_day){
   var date_array = new Array();
   var curDay = new Date();
   curDay.setTime(cur_day.getTime());
   for(var i = 0; i < num; i++){
      date_array.push(dateToString(curDay));
      curDay.setDate(curDay.getDate() - 1);
   }
   date_array = date_array.reverse();
   return date_array;
}

// generate data table
function fillDateRow(day_num, cur_day){
   var date_array = getRecentDayArray(day_num, cur_day);
   $("#date-row ul").append('<li><a style="display: none">dummy</a></li>');
   for(var i = 0; i < date_array.length; i++){
      $("#date-row ul").append('<li><a>' + date_array[i].substr(5) + '(' + string2Day(date_array[i]) + ')</a></li>');
   }
   $("#date-row ul").append('<li><a style="display: none">dummy</a></li>');
   return date_array;
}

// generate patient name column
function fillPatientNames(){
   patient_name = [];
   for(var i = 0; i < alcoholics.length; i++){
      if(alcoholics[i]['IsDropout'] != 0) continue;
      if(alcoholics[i]['UserId'].substr(0, 5) != 'rehab') continue;
      $("#patient-name-column").append("<div class='patient-name-div' onclick='toPatientDetail(this.innerHTML)'>" + alcoholics[i].UserId + "</div>");
      patient_name.push(alcoholics[i].UserId);
   }
   for(var i = 0; i < alcoholics.length; i++){
      if(alcoholics[i]['IsDropout'] == 0) continue;
      if(alcoholics[i]['UserId'].substr(0, 5) != 'rehab') continue;
      $("#patient-name-column").append("<div class='patient-name-div' onclick='toPatientDetail(this.innerHTML)'>" + alcoholics[i].UserId + "</div>");
      patient_name.push(alcoholics[i].UserId);
   }
   for(var i = 0; i < alcoholics.length; i++){
      if(alcoholics[i]['IsDropout'] != 0) continue;
      if(alcoholics[i]['UserId'].substr(0, 5) == 'rehab') continue;
      $("#patient-name-column").append("<div class='patient-name-div' onclick='toPatientDetail(this.innerHTML)'>" + alcoholics[i].UserId + "</div>");
      patient_name.push(alcoholics[i].UserId);
   }
   return patient_name;
}

// generate patient data table by current date and patient
function fillPatientData(date_array, patient_array){

   for(var i = 0; i < patient_array.length; i++){
      var data_row = document.createElement("div");
      data_row.setAttribute("style", "float: left; margin-bottom: 5px; border-bottom: solid 1px #c2b5b5;");
      
      for(var j = 0; j < date_array.length; j++){
         var one_day = document.createElement("div");
         one_day.setAttribute("class", "div-content");
         one_day.setAttribute("style", "height: 135px;");

         for(var k = 1 ; k <=3 ; k++){
            if(detections_valid[patient_array[i]] !== undefined && 
               detections_valid[patient_array[i]][date_array[j]] !== undefined && 
               detections_valid[patient_array[i]][date_array[j]][k] !== undefined ){

               var brac_value = detections_valid[patient_array[i]][date_array[j]][k].Brac;
               var data = document.createElement("div");
               if(brac_value >= <?php echo $RELAPSE_THRESHOLD ?>)       data.setAttribute("class", "one-data-over");
               //else if(brac_value > 0.4)  data.setAttribute("class", "one-data-over2");
               //else if(brac_value > 0.25) data.setAttribute("class", "one-data-over3");
               //else if(brac_value > 0.09) data.setAttribute("class", "one-data-under");
               else if(brac_value >= <?php echo $LAPSE_THRESHOLD ?>) data.setAttribute("class", "one-data-under");
               else                       data.setAttribute("class", "one-data-under3");
               

               var time = document.createElement("div");
               time.setAttribute("id", "small_time");
               time.innerHTML = detections_valid[patient_array[i]][date_array[j]][k].Time.substr(0, 5);
               data.appendChild(time);

               var brac = document.createElement("div");
               brac.setAttribute("class", "underStandard");
               if(detections_valid[patient_array[i]][date_array[j]][k].Result =="0")
               brac.innerHTML = "NEG";
               else
               brac.innerHTML = "POS";
               data.appendChild(brac);
                //Type
                var t_time = detections_valid[patient_array[i]][date_array[j]][k].Time;;
	     if(detections_valid[patient_array[i]][date_array[j]][k].IsFilled != "0"){ 
		if(!ques[patient_array[i]]||
		   !ques[patient_array[i]][date_array[j]]||
		   !ques[patient_array[i]][date_array[j]][t_time]
		){
		 var emotion = document.createElement("div")
                 emotion.setAttribute("class", "emotion_img");
                 emotion.innerHTML ='<font color="red">Failed</font>';
                 data.appendChild(emotion);
		}	
              else if(ques[patient_array[i]][date_array[j]][t_time].Type > "0"){
                  var emotion = document.createElement("img");
                  var level = parseInt(ques[patient_array[i]][date_array[j]][t_time].Type);
                  emotion.src = "img/ques_" + level.toString() + ".png";
                  emotion.setAttribute("class", "emotion_img");
                  data.appendChild(emotion);
               }
             }  
               else{
                var emotion = document.createElement("div")
                 emotion.setAttribute("class", "emotion_img");
                  emotion.innerHTML ="-";
                  data.appendChild(emotion);
               }
              
            //item  
	     if(detections_valid[patient_array[i]][date_array[j]][k].IsFilled != "0"){ 
		if(!ques[patient_array[i]]||
		   !ques[patient_array[i]][date_array[j]]||
		   !ques[patient_array[i]][date_array[j]][t_time]
		){
		 var emotion = document.createElement("div")
                 emotion.setAttribute("class", "emotion_img");
                 emotion.innerHTML ='<font color="red">Failed</font>';
                 data.appendChild(emotion);
		}	
              else  if(ques[patient_array[i]][date_array[j]][t_time].Type > "0"  ){
                  var desire = document.createElement("div");
                  desire.setAttribute("class", "desire_img");
                  desire.innerHTML = ques[patient_array[i]][date_array[j]][t_time].Items;
                  
                  data.appendChild(desire);
               }
            }
               else{
              var desire = document.createElement("div") ;
                 desire.setAttribute("class", "desire_img");
                  desire.innerHTML = "-"; 
                  data.appendChild(desire);
               }
             
               one_day.appendChild(data);
            }
            else{
               var no_data = document.createElement("div");
               no_data.setAttribute("class", "div-no-data");
               no_data.innerHTML = "-";
               one_day.appendChild(no_data);
            }
         }

         data_row.appendChild(one_day);
      }

      $("#patient-data").append(data_row);
   }
}

function dateToLeft(){
   // ui reset
   $("#rarrow").removeClass("disabled");

   cur_day.setDate(cur_day.getDate()-7);

   //$("#date-row").effect("drop", {}, 500, tmp);

   document.getElementById("date-row").innerHTML = "<ul></ul>";
   document.getElementById("patient-name-column").innerHTML = "";
   document.getElementById("patient-data").innerHTML = "";

   var cur_date_array = fillDateRow(7, cur_day);
   var cur_patient_array = fillPatientNames();
   fillPatientData(cur_date_array, cur_patient_array);
   //$("#date-row").effect("slide", {"direction": "right", "mode": "show", "distance": 100}, 0);
  

}

function dateToRight(){
   if(cur_day.getTime() == today.getTime())
      return;

   cur_day.setDate(cur_day.getDate()+7);
   // ui reset
   if(cur_day.getTime() == today.getTime())
      $("#rarrow").addClass("disabled");
   document.getElementById("date-row").innerHTML = "<ul></ul>";
   document.getElementById("patient-name-column").innerHTML = "";
   document.getElementById("patient-data").innerHTML = "";

   var cur_date_array = fillDateRow(7, cur_day);
   var cur_patient_array = fillPatientNames();
   fillPatientData(cur_date_array, cur_patient_array);

}

// javascript main code


// ui initialize
$("#record").addClass("active");

var cur_day = new Date();
var today = new Date();
today.setTime(cur_day.getTime());
var cur_date_array = fillDateRow(7, cur_day);
var cur_patient_array = fillPatientNames();
fillPatientData(cur_date_array, cur_patient_array);

for(var index in alcoholics)
   $("#patient-list").append("<div class='patient-button' onclick='searchPatientDetail(this.innerHTML);'>"
                              + alcoholics[index]["UserId"] + "</div>");   

$("#patient-name-input").autocomplete({source: alcoholic_names});
</script>

</body>

</html>
