<?php
   include_once("PHPMailer/class.phpmailer.php");

   // $to is an email-array
   function send_email($to, $subject, $body, $html = true){
      $mail= new PHPMailer();
      $mail->IsSMTP();
      $mail->SMTPAuth = true;
      $mail->SMTPSecure = "ssl";
      $mail->Host = "smtp.gmail.com";
      $mail->Port = 465;
      $mail->CharSet = "utf8";

      $mail->Username = "ubicomplab.ntu";
      $mail->Password = "alcoholdetection";

      $mail->From = "ubicomplab.ntu@gmail.com";
      $mail->FromName = "NTU Ubicomp";
      $mail->Subject = $subject;
      $mail->Body = $body;
      $mail->IsHTML($html);
      foreach($to as $address)
         $mail->AddAddress($address);

      if(!$mail->Send()) {
         echo "Mailer Error: " . $mail->ErrorInfo . "\n";
         return false;
      }
      else {
         return true;
      }
   }
?>

