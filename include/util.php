<?
function drawTable($rows,$columns)
{
?>
	<table class="center">
		<tr class="header">
<?
	foreach($columns as $array)
	{
		$text=$array["title"];
	?>
			<td><?=$text?></td>
	<?}?>
		</tr>
<?
	foreach($rows as $row)
	{?>
		<tr class="row">
	<?
		foreach($columns as $field=>$array)
		{
			if(strlen($array["align"]))
				$align=$array["align"];
			else
				$align="left";
		?>
			<td style="text-align:<?=$align?>"><?=$row[$field]?></td>
		<?}?>
		</tr>
<?	}?>
		</tr>
	</table>
<?
}


function drawTableEdit($rows,$columns,$readonly,$module_name)
{
	global $self;

	$localself=$self;
	foreach($_GET as $k=>$v)
		if(($k!="op")&&($k!="time"))
			$localself.="&amp;$k=$v";

	if($readonly)
	{
		$ro_field=$readonly["ro_field"];
		$readonly=$readonly["readonly"];
	}
	?>


	<table class="plot">
	<?if(!$readonly)
		{?>
		<tr class="footer">
			<td colspan="<?=(count($columns))?>">
				<a href="<?=$localself?>&amp;op=add_<?=$module_name?>">
					<img src="img/b_add.png" alt="Nuovo" 
						style="vertical-align:middle" title="Nuovo" />
					&nbsp;New insertion
				</a>
			</td>
		</tr>
		<tr class="header" >
			<?foreach($columns as $array)
			{
				$text=$array["title"];
			?>
				<td><?=$text?></td>
			<?}?>
		</tr>
	<?}
	foreach($rows as $id=>$row)
	{
		if(($readonly)||(strlen($row[$ro_field])))
			$edit_link="";
		else
			$edit_link="redirect('$localself&amp;op=edit_$module_name&amp;id=$id')";

		if(isset($row["active"]))
			$attivo=(int)$row["active"];
		else
			$attivo=1;
		$row_class=($attivo?"row_attivo":"row_inattivo");
		?>
		<tr class="<?=$row_class?>" onmouseover="this.className='high'"
				onmouseout="this.className='<?=$row_class?>'">
		<?
		foreach($columns as $field=>$array)
		{
			if(strlen($array["align"]))
				$align=$array["align"];
			else
				$align="left";
		?>
			<td  onclick="<?=$edit_link?>" 
					style="text-align:<?=$align?>">
				<?=$row[$field]?>
			</td>
		<?}?>
		</tr>
		<?
	}
	if(!$readonly)
	{?>
		<tr class="footer">
			<td colspan="<?=(count($columns))?>">
				<a href="<?=$localself?>&amp;op=add_<?=$module_name?>">
					<img src="img/b_add.png" alt="Nuovo" 
						style="vertical-align:middle" title="Nuovo" />
					&nbsp;New insertion
				</a>
			</td>
		</tr>
	<?}?>
	</table>
	<?
}

function tableRow($readonly,$type,$title,$value)
{
	//$readonly si sa
	//$value è array(varname=>varvalue)
	//$array è eventuale array di valori per select

	$locked_row=($readonly?" class='locked'":"");
	$check_locked=($readonly?" onclick='this.blur();return false;'":"");
	$input_locked=($readonly?" onfocus='this.blur()' onclick='this.blur()'":"");

	$varname=key($value);
	$varvalue=$value[$varname];
?>
	<tr <?=$locked_row?> id="row_<?=$varname?>">
		<td class="right"><?=$title?></td>
		<td class="left">
			<?
		switch($type["type"])
		{
			case "select":
				$values=$type["values"];
				if($readonly)
				{?>
					<input type="hidden" 
						name="<?=$varname?>"
						id="<?=$varname?>"
						value="<?=$varvalue?>" />
						<b><?=$values[$varvalue]?></b>
				<?}
				else
				{?>
					<select id="<?=$varname?>"
						name="<?=$varname?>">
					<?
						foreach($values as $id=>$value)
						{
							$selected=(strlen($varvalue)&&($varvalue==$id));
						?>
						<option value="<?=$id?>"<?=($selected?" selected='selected'":"")?>>
							<?=$value?>
						</option>
						<?}?>
					</select>
				<?}
				break;
			case "textarea":
				$cols=$type["cols"];
				$rows=$type["rows"];
				?>
					<textarea name="<?=$varname?>" id="<?=$varname?>" cols="<?=$cols?>" rows="<?=$rows?>"
						class="input"<?=$input_locked?>><?=$varvalue?></textarea>
				<?
				break;
			case "input":
				$maxlength=$type["maxlength"];
				$size=$type["size"];
				?>
					<input type="text" 
						name="<?=$varname?>" 
						id="<?=$varname?>" 
						size="<?=$size?>" 
						maxlength="<?=$maxlength?>" 
						value="<?=$varvalue?>" 
						<?=$input_locked?> />
				<?
				break;
			case "date":
			?>
					<input type="text" 
						name="<?=$varname?>" 
						id="<?=$varname?>" 
						size="12" 
						value="<?=$varvalue?>" 
						onclick='showCalendar("", this,this, "dd/mm/yyyy","it",1,0)'
						onchange="" 
						readonly="readonly" />
			<?
				if(!$readonly)
				{?>
					<img src="img/calendar.png" 
						onmouseover="style.cursor='pointer'" 
						alt="calendar"
						style="height:25px;vertical-align:middle;"
						onclick='showCalendar("", this,document.getElementById("<?=$varname?>"), "dd/mm/yyyy","it",1,0)' />
			<?	}?>
				<?
				break;
			case "time":
			?>
					<input type="text" 
						name="<?=$varname?>" 
						id="<?=$varname?>" 
						size="5" 
						maxlength="5" 
						value="<?=$varvalue?>" 
						class="input_mask mask_time_short"
						<?=$input_locked?> />
				<?
				break;
			case "multicheck":
				$values=$type["values"];
				$check_all=$type["check_all"];
				$check_locked=($readonly?" onclick='this.blur();return false;'":"");

				if(($check_all)&&(!$check_locked))
				{?>
					<input type="checkbox" onchange="check_all(this,'<?=$varname?>')">All<br/>

				<?}
				$varvalue=(int)$varvalue;
				foreach($values as $id=>$value)
				{
					$checked=($varvalue&(1<<$id)?" checked='checked'":"");
					$value=str_replace("&","&amp;",$value);
					$k=str_replace(" ","",$value);
					?>
					<input type="checkbox"
						name="<?=$varname;?>_<?=$k;?>"
						value="<?=$id;?>"<?=$check_locked?><?=$checked?> /><?=$value;?><br/>
					<?
				}
				break;
			default:
				break;
		}?>
		</td>
	</tr>
<?
}
?>
