<?php

use PHPMailer\PHPMailer\PHPMailer;

use PHPMailer\PHPMailer\SMTP;

require 'PHPMailer/PHPMailer.php';

require 'PHPMailer/SMTP.php';

require_once 'functions.php';

require_once 'dbsetlocal.php';

$DB = new DBWORKER($dbu['host'], $dbu['db'], 'utf8', $dbu['user'], $dbu['pass']);

$MailingList = $DB -> prep('SELECT `users`.`id`, `users`.`data1`, `users`.`TimeStamp`, `users`.`SendEmail` FROM `users` 

	WHERE `users`.`prid` = 4 AND `users`.`TimeStamp` < DATE_SUB(CURRENT_TIMESTAMP(), INTERVAL 1 WEEK)') -> fetchAll(PDO :: FETCH_ASSOC);
	
foreach ($MailingList as $address) {
	
	$mail = new PHPMailer();

	$mail -> isSMTP();     
				 
	$mail -> Host = 'smtp.jino.ru';  

	$mail -> SMTPAuth = true;      
	   
	$mail -> Username = 'info@hotresearch.ru'; 
		  
	$mail -> Password = 'sdmn_aksjdnwe45';   

	$mail -> SMTPSecure = 'ssl';  
		   
	$mail -> Port = 465;   

	$mail -> CharSet = 'windows-1251';
			   
	$mail -> setFrom('info@hotresearch.ru', 'Администратор');   

	$mail -> addAddress($address['data1'], 'Вася Петров'); 
	 
	$mail -> Subject = 'Тест 11';

	$mail -> msgHTML("<html><body>
					<h1>Здравствуйте!</h1>
					<p>Это тестовое письмо 11.</p>
					</html></body>");
	 
	if ($mail -> send()) $DB -> prep('UPDATE `users` SET `users`.`TimeStamp` = CURRENT_TIMESTAMP(), `users`.`SendEmail` = ' . ($address['SendEmail'] + 1) . ' WHERE `users`.`id` = ' . $address['id']);
	
}

?>