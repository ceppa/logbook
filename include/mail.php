<?
function emailHtml($from, $subject, $message, $to) 
{
	require_once("Mail.php");

	$host = "localhost";
	$username = "";
	$password = "";

	$headers = array ('MIME-Version' => "1.0", 
					'Content-type' => "text/html; charset=iso-8859-1;", 
					'From' => $from, 
					'To' => $to, 
					'Subject' => $subject);

	$smtp = Mail::factory('smtp', array ('host' => $host, 'auth' => false));

	$mail = $smtp->send($to, $headers, $message);
	if (PEAR::isError($mail))
		return 0;
	return 1;
}
?>
