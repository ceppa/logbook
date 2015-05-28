<?
	logged_header("Users management","list");
?>
<div id="content">
<?
	$conn=opendb();

	$cols=array
		(
			"login"=>"login",
			"nome"=>"name",
			"cognome"=>"surname",
			"livello"=>"level",
			"expired"=>"expired",
			"attivo"=>"active"
		);
	$colspan=count($cols)+2;
	$livello=$_SESSION["livello"];
	if($livello==3)
	{
		$query="SELECT utenti.*,GROUP_CONCAT(sites.name) AS site FROM utenti 
				LEFT JOIN sites ON utenti.sites & (1<< (sites.id-1))>0
				WHERE eliminato=0 
				GROUP BY utenti.id
			ORDER BY cognome";
		$cols["site"]="sites";
		$colspan++;
	}
	else
		$query="SELECT * FROM utenti 
				WHERE eliminato=0 
				AND livello<='".$_SESSION["livello"]."' 
				AND (sites & ".$_SESSION["sites"].")>0 
			ORDER BY cognome";
	$result=do_query($query,$conn);
	$rows=result_to_array($result,true);
	closedb($conn);

	foreach($rows as $id=>$row)
	{
		$rows[$id]["livello"]=$livelli[$row["livello"]];
		$rows[$id]["expired"]=($row["expired"]==1?"si":"no");
		$rows[$id]["attivo"]=($row["attivo"]==1?"si":"no");
	}

	?>

	<table class="plot">
		<tr class="footer">
			<td colspan="<?=$colspan?>">
				<a href="<?=$self?>&amp;op=add_user">
					<img src="img/b_add.png" alt="New" 
						style="vertical-align:middle" title="New" />
					&nbsp;New user
				</a>
			</td>
		</tr>
		<tr class="header" >
			<td colspan="2">&nbsp;</td>
	<?
		foreach($cols as $id=>$title)
		{?>
			<td><?=$title?></td>
	<?	}?>
		</tr>
	<?
	foreach($rows as $id=>$row)
	{
		$edit_link="redirect('$self&amp;op=edit_user&amp;user_to_edit=$id')";
		$del_link="MsgOkCancel('Elimino utente ".$row["login"]."?','$self&amp;performAction=1&amp;user_to_del=$id');";
		$reset_link="MsgOkCancel('Resetto la password di ".$row["nome"]." ".$row["cognome"]."?','$self&amp;performAction=1&amp;user_to_reset=$id');";
		$row_class=(($row["attivo"]=='si')?"row_attivo":"row_inattivo");
		?>
		<tr class="<?=$row_class?>" onmouseover="this.className='high'"
				onmouseout="this.className='<?=$row_class?>'">
			<td>
				<img src="img/b_drop.png" alt="Elimina" title="Elimina"
					onclick="<?=$del_link?>" />
			</td>
			<td>
				<img src="img/b_reset.png" alt="Resetta password"
					title="Resetta password" onclick="<?=$reset_link?>" />
			</td>
		<?
		foreach($cols as $id=>$foo)
		{?>
			<td onclick="<?=$edit_link?>"><?=$row[$id]?></td>
		<?}?>
		</tr>
		<?
	}?>
		<tr class="footer">
			<td colspan="<?=$colspan?>">
				<a href="<?=$self?>&amp;op=add_user">
					<img src="img/b_add.png" alt="New" 
						style="vertical-align:middle" title="New" />
					&nbsp;New user
				</a>
			</td>
		</tr>
	</table>
</div>
	<?
?>
