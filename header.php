<style>
    body {
       padding-top: 60px; /* When using the navbar-top-fixed */
    }
    #patient_detail_form{
       margin: 0;
    }
</style>
<link href="css/bootstrap.css" rel="stylesheet">
<link href="css/bootstrap-responsive.css" rel="stylesheet">
<script src="js/bootstrap.js"></script>
<script src="js/utility.js"></script>

<div class="navbar navbar-fixed-top">
  <div class="navbar-inner">
    <div class="container">
      <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </a>
      <a class="brand" href="index.php">戒K小幫手2正式網站</a>
      <div class="nav-collapse">
        <ul class="nav">
          <li id="daily"><a href="index.php">Daily</a></li>
          <li id="record"><a href="record.php">Records</a></li>
          <li id="skip"><a href="skip.php">Skipped</a></li>
          <li id="manage"><a href="manage.php">Manage</a></li>
<!--          <li id="manage"><a href="score.php">Score</a></li> -->
          <li id="saliva"><a href="saliva.php">Saliva</a></li>
          <li id="consult"><a href="consultant.php">Consultant</a></li>
          <li id="logout"><a href="logout.php">Log out</a></li>
        </ul>
      </div><!-- /.nav-collapse -->
    </div><!-- /.container -->
  </div><!-- /.navbar-inner -->
</div><!-- /.navbar -->

<form  id="patient_detail_form" action="patient_detail.php" method="post">
   <input id="input_uid" type="hidden" name="uid" value="">
</form>
<form  id="certain_test_detail_form" action="certain_test_detail.php" method="post">
   <input id="input_t_uid" type="hidden" name="uid" value="">
   <input id="input_date" type="hidden" name="date" value="">
   <input id="input_timestamp" type="hidden" name="timestamp" value="">
</form>
<form  id="to_recent" action="recent.php" method="post">
   <input id="input_recent_uid" type="hidden" name="uid_r" value="">
</form>
<form  id="to_think" action="think.php" method="post">
   <input id="input_think_uid" type="hidden" name="uid_t" value="">
</form>
<form  id="to_analysis" action="analysis.php" method="post">
   <input id="input_analysis_uid" type="hidden" name="uid_a" value="">
</form>
<form  id="to_detail" action="analysis_detail.php" method="post">
   <input id="input_detail_uid" type="hidden" name="uid_d" value="">
   <input id="item_num" type="hidden" name="item_n" value="">
</form>
