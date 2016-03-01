

<?php
   require_once('check_session.php');
   check_session_with_target('saliva.php');

   $ID = $_POST["Id_reset"];
  // $ID_initail = $_POST["Id_initial"];
  // $ID_final = $_POST["Id_final"];
//   $ID ="CT_".$ID;    
   $today = date("Y-m-d");



           if((int)$ID<10){
           $ID_t="CT_0000";
           }elseif((int)$ID<100){
           $ID_t="CT_000";    
           }elseif((int)$ID<1000){
           $ID_t="CT_00";    
           }elseif((int)$ID<10000){
           $ID_t="CT_0";    
           }else{
           $ID_t="CT_";    
           } 

   $ID =$ID_t.(int)$ID;    

 
   $query = "UPDATE Saliva SET IsUsed='0', CreateDate='$today',Date='',Time='',Timestamp='',Week='-1',UserId=null WHERE CassetteId = '$ID'";

//   $sql = "INSERT INTO CopingSkill (UserId,Date,Time,Timestamp,Timeslot,SkillType,
  //  SkillSelect,Recreation,Score,Week) VALUES ('$uid','$date','$time',$timestamp,$time_slot,$skill_type,$skill_select,'$recreation',$score, $week)";


   include_once('connect_db.php');
   $conn = connect_to_db();
   $success = mysql_query($query);
  
   mysql_close($conn);
  if($success)
   header('Location:saliva.php');

?>
