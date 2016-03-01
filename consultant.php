<?php

   // check if the user has logged in
   require_once('check_session.php');
   check_session_with_target('consultant.php');

?>

<html>
<head>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<script src="js/jquery-1.10.0.min.js"></script>
<script src="js/jquery-ui_1.10.1.js"></script>
<script src="js/utility.js"></script>
<link rel="stylesheet" type="text/css" href="css/jquery-ui_1.10.1.css">
<link rel="stylesheet" type="text/css" href="css/index.css">
<link rel="stylesheet" href="css/consultant.css">
</head>

<body onload="ShowTime()">

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
      if(substr($UserId, 0, 6) == "rehab_" && $alcoholic['IsDropout'] == 0 )
         $MonthPoints[] = $alcoholic["MonthPoints"];
   }
   rsort($MonthPoints);
   foreach($alcoholics as $UserId => $alcoholic){
      if(substr($UserId, 0, 6) == "rehab_" && $alcoholic['IsDropout'] == 0 )
         $alcoholics[$UserId]["MonthRank"] = array_search($alcoholic["MonthPoints"], $MonthPoints) + 1;
      else
         $alcoholics[$UserId]["MonthRank"] = 999;
   }

   // determin Ranking (Week)
   $WeekPoints = array();
   foreach($alcoholics as $UserId => $alcoholic){
      if(substr($UserId, 0, 6) == "rehab_" && $alcoholic['IsDropout'] == 0 )
         $WeekPoints[] = $alcoholic["WeekPoints"];
   }
   rsort($WeekPoints);
   foreach($alcoholics as $UserId => $alcoholic){
      if(substr($UserId, 0, 6) == "rehab_" && $alcoholic['IsDropout'] == 0 )
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
   //pass data to client
   var alcoholics = <?php echo json_encode($alcoholics) ?>;
   var salivas = <?php echo json_encode($salivas) ?>;
   var error = "<?php echo $error ?>";

</script>


<div id="information" >

<div>  
  <h1 align="center" style="line-height:70px" >病患名單總覽</h1>
  <p id="title" style="line-height:10px"></p>
</div> 
</div>
<div id="sub_content" >
  <p></p>
</div>
<div class="main">

<div id="patient_table">
</div>
</div>
<div id="footer">
  <p></p>
</div>



<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript">

  $("#consult").addClass("active");
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
            //other_array.push(alcoholics_array[i]);
           patient_array.push(alcoholics_array[i]);
      }
      draw_table("patient_table", patient_array);
    
   
   });


   //make Title
function ShowTime(){
　var NowDate=new Date();
　var h=NowDate.getHours();
　var m=NowDate.getMinutes();
　var s=NowDate.getSeconds();　
　document.getElementById('title').innerHTML =NowDate.toLocaleString();
　setTimeout('ShowTime()',1000);
}

function think_click(caller){
      toPatientThink(caller.name);
}
function recent_click(caller){
      toPatientRecent(caller.name);
}
function analysis_click(caller){
      toPatientAnalysis(caller.name);
}
function draw_table(table_name, patient_array){

   var patient_data = new google.visualization.DataTable();
   patient_data.addColumn('string', 'UserId');
   patient_data.addColumn('string', 'Join Date');
   patient_data.addColumn('string', 'Week');
   patient_data.addColumn('string', '近期狀況');
   patient_data.addColumn('string', '反思狀況');
   patient_data.addColumn('string', '狀況分析');
  /* patient_data.addColumn('number', 'Ranking (Week)');
   patient_data.addColumn('string', 'start / restart');
   patient_data.addColumn('string', 'App Ver');
   patient_data.addColumn('string', 'WifiCheck');
   patient_data.addColumn('string', 'DId');
   patient_data.addColumn('string', 'Action');
*/
   var i = 0; var cell_style = {style: 'text-align: center'};
   for(var index in patient_array){
      var j = 0;
      var guy = patient_array[index];
      patient_data.addRows(1);
      patient_data.setCell(i, j++, guy['UserId'], null, cell_style);
      patient_data.setCell(i, j++, guy['JoinDate'].substr(5).replace("-","/"), null, cell_style);
      patient_data.setCell(i, j++, guy['Week'], null, cell_style);
      patient_data.setCell(i, j++, '<button class="btn btn-mini btn-primary" onclick="recent_click(this);" name="' + guy['UserId'] + '">點此進入</button>',
                               null, {style: 'text-align: center; width: 100px;'});
      patient_data.setCell(i, j++, '<button class="btn btn-mini btn-warning" onclick="think_click(this);" name="' + guy['UserId'] + '">點此進入</button>',
                               null, {style: 'text-align: center; width: 100px;'});
      patient_data.setCell(i, j++, '<button class="btn btn-mini btn-success" onclick="analysis_click(this);" name="' + guy['UserId'] + '">點此進入</button>',
                               null, {style: 'text-align: center; width: 100px;'});
  /*    patient_data.setCell(i, j++, guy['CurPoints'], guy['CurPoints'].toString() + " (" + (Math.floor(guy['CurPoints']/20)).toString() + ")", cell_style);
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
    */  i++;
   }
   var options={allowHtml: true, sortColumn: 0, sort: "event"};
   var table = new google.visualization.Table(document.getElementById(table_name));
   table.draw(patient_data,options);
 
   google.visualization.events.addListener(table,'sort',function(event){
   		if(event.column <3){
		options.sortColumn=event.column;
		options.sortAscending= event.ascending;
		patient_data.sort([{column: event.column, desc: !event.ascending}]);
		table.draw(patient_data,options);
		}
   });

}

function changeTab(id){
   $("#tab li:not(#" + id + "-li)").removeClass('active');
   $("#" + id + "-li").addClass('active');

   $(".tab:not(#" + id + "_table)").removeClass('in').fadeOut();
   setTimeout(function(){$("#" + id + "_table").fadeIn();}, 100);
}
</script>

</body>
</html>
