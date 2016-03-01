<?php
//   require_once('check_session.php');
//   check_session_with_target('gcm_control.php');
?>

<html>
    <head>
        <title>GCM Sender</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    </head>
    <body>
        <form name="" method="post" action="GCM/send.php">
			<?
			//echo "<input type="text" name="uid"/>";
			include_once('connect_db.php');
			$dbhandle = connect_to_db();
			$sql = "SELECT * FROM Alcoholic";
			$result = mysql_query($sql);
			while($row=mysql_fetch_array($result)){
				$uid = $row['UserId'];
				if($row['GCM_Id']===NULL);
				else
					echo "<input type='checkbox' name='uid[]' value='$uid'/><label>$uid</label><br/>";
			}

			mysql_close($dbhandle);
			?>
			<TEXTAREA rows="15" cols="24" name="message" maxlength="120"/></TEXTAREA>
			<input type="submit" value="Send"/>
		</form>
    </body>
</html>
