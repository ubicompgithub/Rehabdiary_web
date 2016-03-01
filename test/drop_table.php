<?php

include_once('../connect_db.php');
$conn = connect_to_db();

$query = "DROP TABLE IF EXISTS Patient, TestResult, NoteAdd, TestDetail, Saliva, ExchangeHistory, QuestionTest, CopingSkill";


if(mysql_query($query, $conn))
    echo "Drop Table success\n";
else
    echo "Error Drop Table\n";

mysql_close($conn);


?>
