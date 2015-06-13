<!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <title></title>
</head>
<body>
<?php
$to      = 'vitineth@gmail.com';
$subject = '[Registration]: Account registration on Cheltenham Hackspace Gatherings';
$message = wordwrap('You have submitted an application to become a member on the Cheltenham Hackspace Gatherings system. To
complete your registration please follow the link below. {{LINK}}', 70, "\r\n");
$headers = 'From: gatherings@cheltenhamhackspace.org' . "\r\n" .
    'Reply-To: gatherings@cheltenhamhackspace.org' . "\r\n" .
    'X-Mailer: PHP/' . phpversion();

mail($to, $subject, $message, $headers);
?>
</body>
</html>