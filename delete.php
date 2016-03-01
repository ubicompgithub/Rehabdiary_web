


<?php
   require_once('check_session.php');
   check_session_with_target('saliva.php');
   include_once('connect_db.php');
   $conn = connect_to_db();

   $ID_all = $_POST["Id_delete"];
   
  // $ID_initail = $_POST["Id_initial"];
  // $ID_final = $_POST["Id_final"];
//   $ID ="CT_".$ID;    
//   $today = date("Y-m-d");
   $ID_array=explode(",",$ID_all);
   $NumofID=count($ID_array);
   $success_time=0;
   for($i=0;$i<$NumofID;$i++){

   
           if((int)$ID_array[$i]<10){
           $ID_t="CT_0000";
           }elseif((int)$ID_array[$i]<100){
           $ID_t="CT_000";    
           }elseif((int)$ID_array[$i]<1000){
           $ID_t="CT_00";    
           }elseif((int)$ID_array[$i]<10000){
           $ID_t="CT_0";    
           }else{
           $ID_t="CT_";    
           } 

   $ID =$ID_t.(int)$ID_array[$i];    
   echo $ID_array[$i];
   $query = "DELETE FROM Saliva WHERE CassetteId = '$ID'";
   $success = mysql_query($query);
   if($success)
       $success_time++;
}
//   $sql = "INSERT INTO CopingSkill (UserId,Date,Time,Timestamp,Timeslot,SkillType,
  //  SkillSelect,Recreation,Score,Week) VALUES ('$uid','$date','$time',$timestamp,$time_slot,$skill_type,$skill_select,'$recreation',$score, $week)";


  
   mysql_close($conn);
  if($success_time==$NumofID)
   header('Location:saliva.php');

?>
