<?
	$conn=opendb();

	$query="SELECT * 
			FROM activities_types";
	$result=do_query($query,$conn);
	$activity_types=result_to_array($result,true);

	$query="SELECT * 
			FROM devices";
	$result=do_query($query,$conn);
	$devices=result_to_array($result,true);

	logged_header("Reports","");
?>
<div id="content">
	<div class="centra">
		<form id="summary_form" method="post" <?=$text?>
			action="<?=$self?>" onsubmit="return canSubmit(this)">
			<div style="display:inline">
			<input type="hidden" name="op" />
			<input type="hidden" name="xls" value="0" />
			<table style="display:inline">
				<tr class="header">
					<td colspan="2">Activity Summary Report</td>
				</tr>
				<tr>
					<td class="right" style="padding:3px 5px;border-left:1px solid #ccc">From</td>
					<td class="left" style="padding:3px 5px;border-right:1px solid #ccc">
						<input type="text" 
							name="logbook_da" 
							id="logbook_da" 
							size="12" 
							value="01/01/2004" 
							readonly="readonly" 
							style="vertical-align:middle;"/>
						<img src="img/calendar.png" 
							onmouseover="style.cursor='pointer'" 
							alt="calendar"
							style="height:25px;vertical-align:middle;"
							onclick='showCalendar("", this,document.getElementById("logbook_da"), "dd/mm/yyyy","it",1,0)' />
					</td>
				</tr>
				<tr>
					<td class="right" style="padding:3px 5px;border-left:1px solid #ccc">To</td>
					<td class="left" style="padding:3px 5px;border-right:1px solid #ccc">
						<input type="text" 
							name="logbook_a" 
							id="logbook_a" 
							size="12" 
							value="<?=date("d/m/Y");?>" 
							readonly="readonly" 
							style="vertical-align:middle;"/>
						<img src="img/calendar.png" 
							onmouseover="style.cursor='pointer'" 
							alt="calendar"
							style="height:25px;vertical-align:middle;"
							onclick='showCalendar("", this,document.getElementById("logbook_a"), "dd/mm/yyyy","it",1,0)' />
					</td>
				</tr>
				<tr>
					<td class="right" style="padding:3px 5px;border-left:1px solid #ccc;border-top:1px solid #ccc">Activity Type</td>
					<td class="left" style="padding:3px 5px;border-right:1px solid #ccc;border-top:1px solid #ccc">
					<?
						foreach($activity_types as $id=>$act)
						{?>
							<input type="checkbox" name="activity_type[]" value="<?=$id?>" checked="checked" />
							<?=$act["name"]?>
							<br/>
						<?}
					?>
					</td>
				</tr>
				<tr>
					<td class="right" style="padding:3px 5px;border-left:1px solid #ccc;border-top:1px solid #ccc">Devices</td>
					<td class="left" style="padding:3px 5px;border-right:1px solid #ccc;border-top:1px solid #ccc">
					<?
						foreach($devices as $id=>$dev)
						{?>
							<input type="checkbox" name="devices[]" value="<?=$id?>" checked="checked" />
							<?=$dev["name"]?>
							<br/>
						<?}
					?>
					</td>
				</tr>

				<tr class="header">
					<td colspan="2" class="centra">
<!--						<input type="submit" class="button" name="stampaActivity" value="stampa"
							onclick="document.getElementById('summary_form').target='_blank';
								document.getElementById('summary_form').op.value='_print_summary';
								document.getElementById('summary_form').xls.value=0"
							onmouseover="style.cursor='pointer'" />-->
						<input type="submit" class="button" name="esportaActivity" value="esporta"
							onmouseover="style.cursor='pointer'" 
							onclick="document.getElementById('summary_form').target='_blank';
								document.getElementById('summary_form').op.value='_print_summary';
								document.getElementById('summary_form').xls.value=1" />
					</td>
				</tr>
			</table>
			</div>
		</form>
		&nbsp;&nbsp;&nbsp;
</div>
	<?
?>
