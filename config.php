<?
	date_default_timezone_set("Europe/Rome");
	ini_set('include_path',get_include_path().PATH_SEPARATOR.'/home/hightecs/php');
	ini_set('error_reporting',E_ALL & ~E_NOTICE & E_DEPRECATED);
	$siteName="ASTA logbook";
	ini_set ('session.name', '$siteName');

	$siteAddress="http://www.hightecservice.biz/logbook/index.php";
	session_start();
?>
