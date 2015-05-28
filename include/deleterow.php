<?
	$id=$_POST["id"];
	require_once("mysql.php");
	$conn=opendb();
	$query="delete from activities where id=$id";
	$result=do_query($query,$conn)
		or die("$query<br>".((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
	closedb($conn);
?>
