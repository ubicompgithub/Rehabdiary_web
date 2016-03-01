
<?php
   require_once('check_session.php');
   check_session_with_target('saliva.php');

   include_once('connect_db.php');
   $conn = connect_to_db();
   $ID_all = $_POST["Id"];
   $ID_initial = $_POST["Id_initial"];
   $ID_final = $_POST["Id_final"];
   $today = date("Y-m-d");

   //echo $ID_all;
    if($ID_all!=""){

   
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
   //echo $ID;
   

   $query_check= "SELECT CassetteId FROM Saliva WHERE CassetteId ='$ID'";

   //echo $query_check;
   $chk_success = mysql_query($query_check);

   //echo $chk_success; 
   if(mysql_num_rows($chk_success)!=0){
    echo $ID;
    echo " Exists ";
   }else{
     $query ="INSERT INTO Saliva (CreateDate, CassetteId, IsUsed,Week) VALUES('$today','$ID',0,-1)";
     $success = mysql_query($query);
   //  echo $query;
     if($success){
            $success_time++;
     }
     //echo $success_time;

       }
   }

   mysql_close($conn);
  if($success_time==$NumofID)
   header('Location:saliva.php');
} else{
       $ID_ini_int=(int)$ID_initial;
       $ID_fin_int=(int)$ID_final;
    
       $query_t ="INSERT INTO Saliva (CreateDate, CassetteId, IsUsed,Week) VALUES";
       //echo $query_t;
      
       for( $j=$ID_ini_int;$j<=$ID_fin_int;$j++){
  
          
           if($j<10){
           $ID_t="CT_0000";
           }elseif($j<100){
           $ID_t="CT_000";    
           }elseif($j<1000){
           $ID_t="CT_00";    
           }elseif($j<10000){
           $ID_t="CT_0";    
           }else{
           $ID_t="CT_";    
           } 


           $ID_str=strval($j);
          // echo $ID_str;
           $ID=$ID_t.$ID_str;
         //  echo $ID;
           if($j!=$ID_fin_int){
             $query_t=$query_t."('$today','$ID',0,-1),";
           
             }else{
             $query=$query_t."('$today','$ID',0,-1)";
             
            }
       }
    
     $success = mysql_query($query);
     mysql_close($conn);
     if($success)
     header('Location:saliva.php');
} 
/*
//   $sql = "INSERT INTO CopingSkill (UserId,Date,Time,Timestamp,Timeslot,SkillType,
  //  SkillSelect,Recreation,Score,Week) VALUES ('$uid','$date','$time',$timestamp,$time_slot,$skill_type,$skill_select,'$recreation',$score, $week)";
*/
?>
