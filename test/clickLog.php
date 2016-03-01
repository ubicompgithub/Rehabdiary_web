<?php
	
	$uid = $_POST['uid'];
	
	$uploadDest = '../patients_clicklog/' . $uid;
	if (!file_exists($uploadDest)) {
		if (!mkdir($uploadDest, 0777, true)) {
			die("Failed to create directory: " . $uploadDest);
		}
	}
	$len = count($_FILES['file']['name']);
	if ($len > 0) {
		for ($i=0; $i < $len; $i++) {
			$tmpName = $_FILES['file']['tmp_name'][$i];
			if (is_uploaded_file($tmpName)) {
				$fname = basename($_FILES['file']['name'][$i]);
				if (!move_uploaded_file($tmpName, $uploadDest . "/" . $fname)) {
					die("Fail to move the clicklog");
				}
			} else {
				die("No upload clicklog exists");
			}
		}
	}else{
		die("no upload clicklog");
	}

	//parse here

	echo 'upload success';
?>
