<?
	require_once("include/mysql.php");
	require_once("include/util.php");

	logged_header($title,$subtitle);
	$edit=(substr($op,0,4)=="edit");
	list($foo,$subop)=explode("_",$op);

	if($edit)
	{
		$id=$_GET["id"];
		$conn=opendb();
		$query="SELECT * FROM $table WHERE id=$id";
		$result=do_query($query,$conn);
		$rows=result_to_array($result,true);
		$valori=$rows[$id];
		closedb($conn);
	}
	$readonly=false;
?>
	<form action="<?=$self?>" 
			id="edit_form" method="post" 
			onsubmit="return check_post_anagrafica(this)">

	<div class="centra">
		<input type="hidden" 
				value="anagrafica" 
				name="performAction"
				id="performAction" />
			<?
			if($edit)
			{?>
		<input type="hidden" 
			value="<?=$id?>" 
			name="id_anagrafica" />
			<?}?>
		<table class="plot">
		<?
		$type=array("type"=>"input","maxlength"=>"30","size"=>"10");
		$value=array("name"=>$valori["name"]);
		tableRow($readonly,$type,"Name",$value);

		$type=array("type"=>"input","maxlength"=>"30","size"=>"10");
		$value=array("surname"=>$valori["surname"]);
		tableRow($readonly,$type,"Surname",$value);
?>
			<tr class="row_attivo">
				<td colspan="2" style="text-align:center">
<?
		if(!$readonly)
		{?>
					<input type="submit" class="button" name="<?=$op?>" value="confirm" />&nbsp;
<?		}?>
					<input type="button" class="button" onclick="javascript:redirect('<?=$self?>&amp;op=anagrafica&subop=<?=$subop?>');" value="cancel" />
				</td>
			</tr>
		</table>
	</div>
	</form>
	<script type="text/javascript">
//<![CDATA[
		function check_post_anagrafica(form)
		{
			var out=true;

			if(trim(form.name.value).length==0)
			{
				showMessage("manca il nome");
				return false;
			}
			if(trim(form.surname.value).length==0)
			{
				showMessage("manca il cognome");
				return false;
			}
			return out;
		}

//]]>
	</script>
	<?
?>
