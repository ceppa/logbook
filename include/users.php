<?
	$subop=($op=="add_user"?"new":"modify");
	logged_header("Users management",$subop);
?>
<div id="content">
<?
	if($op=="edit_user")
	{
		$conn=opendb();
		$query="SELECT utenti.*,SUM(1<<(sites.id-1)) AS sites
			FROM utenti 
			LEFT JOIN sites ON utenti.sites & (1<<(sites.id-1))>0
			WHERE utenti.id=".$_GET["user_to_edit"]."
			GROUP BY utenti.id";
		$result=do_query($query,$conn);
		$rows=result_to_array($result,true);
		$valori=$rows[$_GET["user_to_edit"]];
		$valori["id"]=$_GET["user_to_edit"];
		closedb($conn);
	}
	if(!isset($valori["livello"]))
		$valori["livello"]=0;

	$conn=opendb();

	$utenti="";
	$query="SELECT login FROM utenti
			WHERE utenti.id<>'".$_GET["user_to_edit"]."'";
	$result=do_query($query,$conn);
	while($row=mysqli_fetch_assoc($result))
		$utenti.='"'.$row["login"].'":"1",';
	$utenti=rtrim($utenti,",");
	((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);

	$query="SELECT id,name FROM sites ORDER BY Name";
	$result=do_query($query,$conn);
	$sites_array=result_to_array($result,true);
	closedb($conn);

	?>
	<form action="<?=$self?>" id="edit_form" method="post"
			onsubmit="return check_post_user(this)">

	<div class="centra">
		<input type="hidden" value="1" name="performAction" />
			<?
			if($op=="edit_user")
			{?>
		<input type="hidden" value="<?=$valori["id"]?>" name="id_admin_users" />
			<?}
			if($_SESSION["livello"]<3)
			{?>
		<input type="hidden" value="<?=$_SESSION["sites"]?>" name="sites" />
			<?}?>
		<table class="plot">
			<tr>
				<td class="right">login</td>
				<td class="left">
					<input type="text" id="to_focus" name="utente" size="15" value="<?=$valori["login"]?>" />
				</td>
			</tr>
			<tr>
				<td class="right">name</td>
				<td class="left">
					<input type="text" name="nome" size="15" value="<?=$valori["nome"]?>" />
				</td>
			</tr>
			<tr>
				<td class="right">surname</td>
				<td class="left">
					<input type="text" name="cognome" size="15" value="<?=$valori["cognome"]?>" />
				</td>
			</tr>
			<tr>
				<td class="right">email</td>
				<td class="left">
					<input type="text" id="email" name="email" size="30" value="<?=$valori["email"]?>" />
				</td>
			</tr>
			<tr>
				<td class="right">level</td>
				<td class="left">
					<select class="input" name="livello">
					<?
					foreach($livelli as $liv_id=>$liv_text)
					{
						if($liv_id<=$_SESSION["livello"])
						{?>
						<option value="<?=$liv_id?>"<?=($liv_id==$valori["livello"]?" selected='selected'":"")?>>
							<?=$liv_text?>
						</option>
						<?}
					}?>
					</select>
				</td>
			</tr>
			<?
				if($_SESSION["livello"]==3)
				{?>
			<tr>
				<td class="right">sites</td>
				<td class="left">
					<?
					foreach($sites_array as $id=>$array)
					{?>
						<input type="checkbox" name="sites[]" 
							value="<?=(1<<($id-1))?>"
							<?=(((1<<($id-1))& $valori["sites"])>0 ? 
							" checked='checked'":"")?> />
							<?=$array["name"]?><br />
					<?}?>
				</td>
			</tr>
			<?
				}
				if($op=="edit_user")
				{?>
			<tr>
				<td class="right">expired</td>
				<td class="left">
					<input type="checkbox" class="check" name="expired"<?=(($valori["expired"]==1)?" checked='checked'":"")?> />
				</td>
			</tr>
			<tr>
				<td class="right">active</td>
				<td class="left">
					<input type="checkbox" class="check" name="attivo"<?=(($valori["attivo"]==1)?" checked='checked'":"")?> />
				</td>
			</tr>
				<?}?>
			<tr class="row_attivo">
				<td colspan="2" style="text-align:center">
					<input type="submit" class="button" name="<?=$op?>" value="confirm" />&nbsp;
					<input type="button" class="button" onclick="javascript:redirect('<?=$self?>&amp;op=adm_list_users');" value="cancel" />
				</td>
			</tr>
		</table>
	</div>
	</form>
</div>
	<script type="text/javascript">
//<![CDATA[
		document.getElementById("to_focus").focus();
		var utenti={<?=$utenti?>};
		function check_post_user(form)
		{
			var out=true;

			var inputs=form.getElementsByTagName("input");
			var checkson=-1;
			for(var i=0;i<inputs.length;i++)
				if(inputs[i].name=="sites[]")
				{
					if(checkson==-1)
						checkson=0;
					if(inputs[i].checked)
						checkson++;
				}
			if(checkson==0)
			{
				showMessage("seleziona almeno un sito");
				return false;
			}

			if(trim(form.utente.value).length==0)
			{
				showMessage("utente non valido");
				return false;
			}
			if(utenti[trim(form.utente.value)]!=null)
			{
				showMessage("Utente gia' presente");
				return false;
			}

			if((form.email.value.indexOf(".") <= 2)
				|| (form.email.value.indexOf("@") <= 0))
			{
				showMessage("email non valida");
				return false;
			}
			return out;
		}
//]]>
	</script>
	<?
?>
<
