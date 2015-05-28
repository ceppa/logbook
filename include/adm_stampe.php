<?
	require_once("include/mysql.php");
	require_once("include/util.php");

	$columns=array
			(
				"campo1"=>array("title"=>"CAMPO1","align"=>"center"),
				"campo2"=>array("title"=>"CAMPO2","align"=>"center"),
				"campo3"=>array("title"=>"CAMPO3","align"=>"center")
			);
	$rows=array
			(
				array("campo1"=>"gino","campo2"=>"latino","campo3"=>"mah"),
				array("campo1"=>"pippo","campo2"=>"pluto","campo3"=>"paperino")
			);

?>
<div id="content">
<?
	drawTableEdit($rows,$columns,0,"edit_user","add_user")
?>
</div>
