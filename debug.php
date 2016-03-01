<?php

function log_write($msg){
   $fp = fopen('debug_log.txt', 'a');
   fwrite($fp, Date("Y-m-d H:i:s")."\t".$msg."\n");
   fclose($fp);
}

?>
