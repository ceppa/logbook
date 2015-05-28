<?

	require_once("include/util.php");

	if(isset($_GET["subop"]))
		$subop=$_GET["subop"];
	else
		$subop="pilots";
	$subops=array("pilots"=>"piloti","instructors"=>"istruttori","operators"=>"operatori");
	logged_header("Gestione anagrafica",$subops[$subop]);
	display_admin_submenu($subops,$subop);

	$conn=opendb();
	$query="SELECT * FROM $subop ORDER BY surname,name";
	$result=do_query($query,$conn);
	$rows=result_to_array($result,true);
	closedb($conn);

	$columns=array
			(
				"name"=>array("title"=>"name","align"=>"left"),
				"surname"=>array("title"=>"surname","align"=>"left")
			);

?>
<div id="content">
<?
	drawTableEdit($rows,$columns,0,"$subop")
?>
</div>
