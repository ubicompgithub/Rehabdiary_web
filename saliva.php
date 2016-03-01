<?php

   // check if the user has logged in
   require_once('check_session.php');
   check_session_with_target('saliva.php');

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

  ga('create', 'UA-41411079-1', '140.112.30.171');
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
   $query_saliva = "SELECT * FROM `Saliva`";
   $result_saliva = mysql_query($query_saliva);
   $salivas = array();
   while($row = mysql_fetch_assoc($result_saliva)){
      $salivas[$row["CassetteId"]] = $row;
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

?>
<script language="javascript" type="text/javascript">
   //pass data to client
   var alcoholics = <?php echo json_encode($alcoholics) ?>;
   var salivas = <?php echo json_encode($salivas) ?>;
/*
   var blocks = <?php echo json_encode($blocks)?>;
   var emotion_diy = <?php echo json_encode($emotionDIYs)?>;
   var emotion_manage = <?php echo json_encode($emotionManages)?>;
   var questionnaire = <?php echo json_encode($questionnaires)?>;
*/
</script>

<div class="container">

   <div style="width: 900px; margin: 0px auto; position: relative;">
      <h3>Salivas</h3>
      <div id= usenumbers></div>
      <div id="used_table"> 
      </div>
      <div id="table_block">
      <h3>Input Number only</h3>
         <div id="insert_one">
         <h5>insert one</h5>
         <form id="insertform" name="insertform" method="post" action="insert.php">
         Id: <input type="text" size="10" name="Id" id="textfield">
         <input type="submit" name="button" id="button" value="add">
         </form>
         </div>
         <div id="insert_lots">
         <h5>insert lots</h5>
         <form id="insertform" name="insertform" method="post" action="insert.php">
         Id: <input type="text" size="10" name="Id_initial" id="textfield"> ~
         <input type="text" size="10" name="Id_final" id="textfield">  
         <input type="submit" name="button" id="button" value="add">
         </form>
         </div>
         </div>
         <div id="reset_ID">
         <h5>reset certain ID</h5>
         <form id="resetform" name="resetform" method="post" action="reset.php">
         Id: <input type="text" size="10" name="Id_reset" id="textfield">
         <input type="submit" name="button" id="button" value="reset">
         </form>
         </div>
         <div id="delete_ID">
         <h5>delete certain ID</h5>
         <form id="delform" name="delform" method="post" action="delete.php">
         Id: <input type="text" size="10" name="Id_delete" id="textfield">
         <input type="submit" name="button" id="button" value="delete">
         </form>
         </div>
         <div id='unusednum'></div>
         <div id="unused_table">
         </div>

      </div>
   </div>

</body>

<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript">

   // global variables
   var salivas_table;
   var unsalivas_table;
   var date_array;
  
   // ui initialize
   $("#used_table").addClass("active");

   // load google api
   google.load("visualization", "1", {packages:["table"]});
   google.setOnLoadCallback(function(){drawtable();});
// functions


// draw main tablei
function drawtable(){
    draw_used_table();
    draw_unused_table();
    
    }
function draw_used_table(){

      // 
      salivas_data = new google.visualization.DataTable();
      salivas_data.addColumn('string', 'CassetteId');
     
      salivas_data.addColumn('string', 'Creat Date');
      salivas_data.addColumn('string', 'Used Time');
      salivas_data.addColumn('string', 'Used Week');
      salivas_data.addColumn('string', 'UserId');

      var i = 0;
      for(var id in salivas){
          if(salivas[id]['IsUsed']==1){
         salivas_data.addRows(1);
         salivas_data.setCell(i, 0, id, null, getStyle(150));
         
         salivas_data.setCell(i, 1, salivas[id]['CreateDate'], null, getStyle(0));
         salivas_data.setCell(i, 2, salivas[id]['Date']+' '+salivas[id]['Time'], null, getStyle(0));
         salivas_data.setCell(i, 3, salivas[id]['Week'], null, getStyle(0));
         salivas_data.setCell(i, 4, salivas[id]['UserId'], null, getStyle(0));

         i++;
          }
      }

      var chart = new google.visualization.Table(document.getElementById('used_table'));
      chart.draw(salivas_data, {allowHtml: true, page: 'enable', pageSize: 10, width: '900px', sortColumn: 0, sortAscending: false});
    
      document.getElementById('usenumbers').innerHTML='<h3>Used Salivas: '+salivas_data.getNumberOfRows()+'<h3>';
   }
function draw_unused_table(){

      // 
      unsalivas_data = new google.visualization.DataTable();
      unsalivas_data.addColumn('string', 'CassetteId');
     
      unsalivas_data.addColumn('string', 'Creat Date');
      unsalivas_data.addColumn('string', 'Used Time');
      unsalivas_data.addColumn('string', 'Used Week');
      unsalivas_data.addColumn('string', 'UserId');

      var i = 0;
      for(var id in salivas){
          if(salivas[id]['IsUsed']== 0){
         unsalivas_data.addRows(1);
         unsalivas_data.setCell(i, 0, id, null, getStyle(150));
         
         unsalivas_data.setCell(i, 1, salivas[id]['CreateDate'], null, getStyle(0));
         unsalivas_data.setCell(i, 2, salivas[id]['Date']+' '+salivas[id]['Time'], null, getStyle(0));
         unsalivas_data.setCell(i, 3, salivas[id]['Week'], null, getStyle(0));
         unsalivas_data.setCell(i, 4, salivas[id]['UserId'], null, getStyle(0));

         i++;
          }
      }

      var chart = new google.visualization.Table(document.getElementById('unused_table'));
      chart.draw(unsalivas_data, {allowHtml: true, page: 'enable', pageSize: 10, width: '900px', sortColumn: 0, sortAscending: false});
    

      document.getElementById('unusednum').innerHTML='<h3>Unused Salivas: '+unsalivas_data.getNumberOfRows()+'<h3>';
      if(unsalivas_data.getNumberOfRows() == 0){$("#unused_table").text("No Record!");}
   }

   
/*
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
      if(_tested[name] == false && name.substr(0,7) === "rehab_"){
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
*/
</script>

</html>
