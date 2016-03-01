<html>
<head>
	<title>Login</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="css/bootstrap.css">
	<link rel="stylesheet" type="text/css" href="css/bootstrap-responsive.css">
	<link rel="stylesheet" type="text/css" href="css/login.css">

</head>
<body>
<?php
	#if the user has the session, redirect to index.php
	session_start();
	$CUR_USER = $_SESSION['username'];
	if ($CUR_USER){
		header('Location:index.php');
		//echo '您已經以 '.$CUR_USER.' 的身份登入,要登出嗎？<br>';
		//echo '<form name="logout" action="logout.php" method="post">';
		//echo '<input type="submit" name="logout" value="登出">';
		//echo '<input type="button" name="cancel" value="取消" onClick="parent.location=\'index.php\'">';
		//echo '</form>';
		die();
	}

?>
<?php
	$TARGET=$_GET['target'];
	if(!$TARGET)
		$TARGET='index.php';

	$ISFAIL = $_GET['condition'];
/*
	if ($ISFAIL && $ISFAIL=='fail')
		echo 'Login failed';
*/
?>
	<div class="container">
		<form id="login_form" name="login" action="login_verifier.php" method="post" class="form-signin">
			<div id=form-header>
            <img src="img/icon.png" class="icon"/>
				<h2>戒K小幫手2</h2>
			</div>
			<h2 class="form-signin-heading">Please sign in</h2>
			<input type="text" class="input-block-level" placeholder="Account" name="account" size="12" maxlength="12"><br>
			<input type="password" class="input-block-level" placeholder="Password" name="pwd" size="12" maxlength="12"><br>
			<input type="hidden" name="target" value="<?echo $TARGET;?>">
                        <?php if($ISFAIL && $ISFAIL=='fail') echo '<div class="alert alert-error">Login Failed!</div>'; ?>
			<input type="submit" name="login" value="登入" class="btn btn-primary chinese-font">
		</form>
	</div>
</body>
</html>
