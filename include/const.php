<?
	$version = '0.0.1';
	$linesPerPage=20;
	$mesi=array
		(
			"gennaio",
			"febbraio",
			"marzo",
			"aprile",
			"maggio",
			"giugno",
			"luglio",
			"agosto",
			"settembre",
			"ottobre",
			"novembre",
			"dicembre"
		);
	$giorniSettimana=array
		(
			"domenica",
			"luned&iacute;",
			"marted&iacute;",
			"mercoled&iacute;",
			"gioved&iacute;",
			"venerd&iacute;",
			"sabato"
		);

	if(isset($_SESSION["pass"]))
		$login_password = $_SESSION["pass"];
	$check_ip = true;
	$do_time_out = false;
	$session_time = 0.5;
	$luser_tries = 1;
	$big_luser = 10;

	$livelli=array(0=>"Guest",1=>"User",2=>"SuperUser",3=>"Admin");
	$self=$_SERVER["PHP_SELF"]."?time=".time();
	$bgbeige=array(1,1,0.8);
	$bggiallino=array(1,1,0.9);
	$bgceleste=array(0.8,1,1);
	$bgverdolino=array(0.9,1,0.8);
	$bgrosa=array(1,0.95,0.75);
	$bgrosetto=array(1,0.8,0.8);
	$bgrosso=array(1,0.5,0.5);
?>
