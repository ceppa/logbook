<?
	$edit=(substr($op,0,4)=="edit");
	$title=($edit?"Modifify activity":"New activity");
	$giorno=$_GET["giorno"];
	$subtitle=$giorno;
	$device_id=$_GET["subop"];
	$livello=$_SESSION["livello"];
	$rms_id=0;

	if($edit && (!strlen($_GET["id"])))
		die("invalid parameters");
	list($d,$m,$y)=explode(".",$giorno);
	if(date("d.m.Y",mktime(0,0,0,$m,$d,$y))!=$giorno)
		die("invalid parameters");
	$giorno_sql=sprintf("%s-%s-%s",$y,$m,$d);

	$conn=opendb();

	if($edit)
	{
		$id=$_GET["id"];
		$activities_id=$id;
		$query="SELECT activities.* 
				FROM activities 
				WHERE id='$id'";
		$result=do_query($query,$conn);
		$rows=result_to_array($result,true);
		if(count($rows)==0)
		{
			closedb($conn);
			die("invalid parameters");
		}
		$valori=$rows[$id];
		$valori["from"]=int_to_hour($valori["from"]);
		$valori["to"]=int_to_hour($valori["to"]);

		$query="SELECT rms.* 
				FROM rms
				WHERE activities_id='$id'";
		$result=do_query($query,$conn);
		$events=array();
		$events=result_to_array($result,true);
/*		foreach($rows as $k=>$v)
			$events[$k]=array("text"=>$v["text"],"closed_during_mission;
*/

		$query="SELECT maint_rms.* 
				FROM activities LEFT JOIN maint_rms 
					ON activities.maint_id=maint_rms.maint_id
				WHERE activities.maint_id>0 AND activities.id='$id'";
		$result=do_query($query,$conn);
		$maint_rmss=array();

		$rows=result_to_array($result,true);
		foreach($rows as $k=>$v)
			$maint_rmss[$k]=$v["rms_id"];
	}
	else
	{
		if(isset($_GET["rms_id"]))
			list($device_id,$rms_id)=explode("_",$_GET["rms_id"]);
		$query="SELECT max(`to`) AS last_to FROM activities 
			WHERE date='$giorno_sql' AND devices_id='$device_id'";
		$result=do_query($query,$conn);
		$rows=result_to_array($result,false);
		if(strlen($rows[0]["last_to"]))
		{
			$valori["from"]=int_to_hour($rows[0]["last_to"]);
			$valori["to"]=int_to_hour($rows[0]["last_to"]+60);
		}
		else
		{
			$valori["from"]="8:00";
			$valori["to"]="9:00";
		}
	}

//	if($livello<3)
		$query="SELECT devices.id,devices.name,devices.mob,devices.sites_id
			FROM devices LEFT JOIN utenti ON ((1<<(devices.sites_id-1)) & utenti.sites)>0
				AND utenti.id='".$_SESSION["user_id"]."'";
/*	else
		$query="SELECT id,name,mob,sites_id FROM devices WHERE id='$device_id'";*/
	$result=do_query($query,$conn);
	$rows=result_to_array($result,true);
	$device=$rows[$device_id]["name"];
	$is_mob=$rows[$device_id]["mob"];
	$sites_id=$rows[$device_id]["sites_id"];

	$devices=array();
	foreach($rows as $id_row=>$row)
	{
		if(($valori["activities_types_id"]!=1) || (!$edit) || (!$row["mob"]))
			$devices[$id_row]=$row["name"];
	}

	$subtitle.=" - ".$device;

	$query="SELECT rms.id,CONCAT(sites.code,LPAD(rms.number,5,'0'),' - ',rms.text) AS `text`
			FROM rms LEFT JOIN activities
				ON activities.id=rms.activities_id
			LEFT JOIN devices ON activities.devices_id=devices.id
			LEFT JOIN sites ON devices.sites_id=sites.id
			WHERE rms.statuses_id!=0 AND date<='$giorno_sql'";


	if($is_mob)
//		$query.=" AND ((1<<(devices.sites_id-1)) & ".$_SESSION["sites"].")>0";
		$query.=" AND (rms.sites_id='$sites_id')";
	else
		$query.=" AND devices.id='$device_id'";

	$query.=" UNION SELECT rms.id,CONCAT(sites.code,LPAD(rms.number,5,'0'),' - ',rms.text) AS `text`
				FROM activities LEFT JOIN maint_rms ON activities.maint_id=maint_rms.maint_id
				LEFT JOIN rms ON maint_rms.rms_id=rms.id
				LEFT JOIN devices ON activities.devices_id=devices.id
				LEFT JOIN sites ON devices.sites_id=sites.id
				WHERE activities.id='$id'";

	$result=do_query($query,$conn);
	$array=result_to_array($result);
	$rms=array();
	foreach($array as $k=>$v)
		$rms[$k]=$v["text"];

	$query="SELECT * FROM systems WHERE mob='$is_mob' ORDER BY line_order";
	$result=do_query($query,$conn);
	$array=result_to_array($result);
	$systems=array();
	foreach($array as $k=>$v)
		$systems[$k]=$v["name"];

	$query="SELECT * FROM statuses";
	$result=do_query($query,$conn);
	$array=result_to_array($result);
	$statuses=array();
	foreach($array as $k=>$v)
		$statuses[$k]=$v["name"];

	$query="SELECT * FROM activities_types";
	$result=do_query($query,$conn);
	$array=result_to_array($result);
	$activities_types=array();
	foreach($array as $k=>$v)
		$activities_types[$k]=$v["name"];
	if($is_mob)
		unset($activities_types[1]);

	$query="SELECT * FROM pilots WHERE active=1 ORDER BY surname,name";
	$result=do_query($query,$conn);
	$array=result_to_array($result);
	foreach($array as $k=>$v)
		$pilots[$k]=$v["surname"]." ".$v["name"];

	$query="SELECT * FROM operators WHERE active=1 ORDER BY surname,name";
	$result=do_query($query,$conn);
	$array=result_to_array($result);
	$operators=array();
	foreach($array as $k=>$v)
		$operators[$k]=$v["surname"]." ".$v["name"];

	$query="SELECT * FROM instructors WHERE active=1 ORDER BY surname,name";
	$result=do_query($query,$conn);
	$array=result_to_array($result);
	$instructors=array(0=>"SELF");
	foreach($array as $k=>$v)
		$instructors[$k]=$v["surname"]." ".$v["name"];

	$query="SELECT * FROM mission_types";
	$result=do_query($query,$conn);
	$array=result_to_array($result);
	$mission_types=array();
	foreach($array as $k=>$v)
		$mission_types[$k]=$v["name"];

	$query="SELECT * FROM maintenances WHERE devices_id='$device_id'";
	$result=do_query($query,$conn);
	$array=result_to_array($result);
	$maintenances=array();
	foreach($array as $k=>$v)
		$maintenances[$k]=$v["description"];

/*	$query="SELECT rms.*,sites.code 
			FROM rms LEFT JOIN activities ON rms.activities_id=activities.id
			LEFT JOIN devices ON activities.devices_id=devices.id
			LEFT JOIN sites ON devices.sites_id=sites.id
			WHERE devices_id='$device_id' AND rms.statuses_id!=0";

	$result=do_query($query,$conn);
	$array=result_to_array($result);
	$rmss=array();
	foreach($array as $k=>$v)
		$rmss[$k]=sprintf("%s%04d - %s",$v["code"],$v["number"],substr($v["text"],0,30));
*/
	closedb($conn);

	logged_header($title,$subtitle);
	require_once("include/util.php");

?>

	<div id="content">
		<form action="<?=$self?>" 
			id="edit_form" method="post" 
			onsubmit="return check_post_activities(this)">
		<div class="centra">
			<input type="hidden" 
					value="activities" 
					name="performAction"
					id="performAction" />
			<input type="hidden"
					name="giorno"
					value="<?=$giorno?>" />
			<input type="hidden"
					name="subop"
					value="<?=$device_id?>" />
				<?
				if($edit)
				{?>
			<input type="hidden" 
				value="<?=$id?>" 
				name="id_activities" />
				<?}?>
		<table class="plot">
		<?

		$type=array("type"=>"select","values"=>$devices);
		$value=array("device_id"=>$device_id);
		tableRow(count($events),$type,"Device",$value);

		$type=array("type"=>"time");
		$value=array("from"=>$valori["from"]);
		tableRow($readonly,$type,"Time start",$value);

		$type=array("type"=>"time");
		$value=array("to"=>$valori["to"]);
		tableRow($readonly,$type,"Time end",$value);

		$type=array("type"=>"select","values"=>$activities_types);
		$value=array("activities_types_id"=>$valori["activities_types_id"]);
		tableRow(($edit?1:0),$type,"Activity",$value);

		$type=array("type"=>"select","values"=>$mission_types);
		$value=array("mission_types_id"=>$valori["mission_types_id"]);
		tableRow($readonly,$type,"Mission type",$value);

		$type=array("type"=>"select","values"=>$pilots);
		$value=array("pilots_id"=>$valori["pilots_id"]);
		tableRow($readonly,$type,"Pilot",$value);

		$type=array("type"=>"select","values"=>$operators);
		$value=array("operators_id"=>$valori["operators_id"]);
		tableRow($readonly,$type,"Operator",$value);

		$type=array("type"=>"select","values"=>$instructors);
		$value=array("instructors_id"=>$valori["instructors_id"]);
		tableRow($readonly,$type,"Instructor",$value);

		?>
		<tr id="row_rms">
			<td class="right" style="vertical-align:top">
				RMS Title<br />
				<input type="button" value="new" onclick="rms_add('',0,'')" />
				<input type="button" id="rms_del_button" 
					style="display:none"
					value="del" onclick="rms_del()" />
			</td>
			<td class="left" id="rms_cell">
			</td>
		</tr>
		<?
		$type=array("type"=>"select","values"=>$maintenances);
		$value=array("maintenances_id"=>$valori["maintenances_id"]);
		tableRow($readonly,$type,"Scheduled maintenance",$value);

/*		$type=array("type"=>"select","values"=>$rms);
		$value=array("rms_id"=>$valori["rms_id"]);
		tableRow($readonly,$type,"RMS Number",$value);
*/
		?>
		<tr id="row_rms_id">
			<td class="right" style="vertical-align:top">
				RMS Number<br />
				<input type="button" 
					id="rms_id_add_button" 
					value="new" onclick="rms_id_add('')" />
				<input type="button" id="rms_id_del_button" 
					style="display:<?=(count($maint_rmss)>1?"inline":"none")?>"
					value="del" onclick="rms_id_del()" />
			</td>
			<td class="left" id="rms_id_cell">
			</td>
		</tr>
		<?

		$type=array("type"=>"textarea","cols"=>50,"rows"=>5);
		$value=array("fault_description"=>$valori["fault_description"]);
		tableRow($readonly,$type,"Fault description",$value);

		$type=array("type"=>"textarea","cols"=>50,"rows"=>5);
		$value=array("fault_solution"=>$valori["fault_solution"]);
		tableRow($readonly,$type,"Fault solution",$value);

		$type=array("type"=>"textarea","cols"=>50,"rows"=>5);
		$value=array("parts_removed"=>$valori["parts_removed"]);
		tableRow($readonly,$type,"Parts removed",$value);

		$type=array("type"=>"textarea","cols"=>50,"rows"=>5);
		$value=array("parts_replaced"=>$valori["parts_replaced"]);
		tableRow($readonly,$type,"Parts replaced",$value);

		$type=array("type"=>"multicheck","values"=>$systems,"check_all"=>1);
		$value=array("systems_involved"=>$valori["systems_involved"]);
		tableRow($readonly,$type,"Sistems involved",$value);

		$type=array("type"=>"textarea","cols"=>50,"rows"=>5);
		$value=array("subsystems"=>$valori["subsystems"]);
		tableRow($readonly,$type,"Subsystem",$value);

		$type=array("type"=>"input","maxlength"=>4,"size"=>4);
		$value=array("qea_risen_number"=>$valori["qea_risen_number"]);
		tableRow($readonly,$type,"Q&A risen number",$value);

		$type=array("type"=>"input","maxlength"=>50,"size"=>50);
		$value=array("qea_title"=>$valori["qea_title"]);
		tableRow($readonly,$type,"Q&A title",$value);

		$type=array("type"=>"select","values"=>$statuses);
		$value=array("statuses_id"=>$valori["statuses_id"]);
		tableRow($readonly,$type,"Status",$value);

		$type=array("type"=>"textarea","cols"=>50,"rows"=>5);
		$value=array("update_description"=>$valori["update_description"]);
		tableRow($readonly,$type,"Update description",$value);

		$type=array("type"=>"textarea","cols"=>50,"rows"=>5);
		$value=array("test_description"=>$valori["test_description"]);
		tableRow($readonly,$type,"Test description",$value);

		$type=array("type"=>"textarea","cols"=>50,"rows"=>5);
		$value=array("training_description"=>$valori["training_description"]);
		tableRow($readonly,$type,"Description",$value);

?>
			<tr class="row_attivo">
				<td colspan="2" style="text-align:center">
<?
		if(!$readonly)
		{?>
					<input type="submit" class="button" name="<?=$op?>" id="confirm" value="confirm" />&nbsp;
<?		}?>
					<input type="button" class="button" onclick="$('performAction').value='';submit();" value="cancel" />
				</td>
			</tr>

		</table>
		</div>
		</form>
	</div>
	<script type="text/javascript">
	//<![CDATA[
		var rmsn=0;
		var rms_idn=0;

		$('device_id').onchange=function()
			{
				var location='<?=$self?>&giorno=<?=$giorno?>&subop='+this.value+'&op=<?=$op?><?=($edit?"&id=$id":"")?>';
				window.location=location;
			}

		function showSection(activities_types_id)
		{
//			$('confirm').disabled=((activities_types_id==3)&&(rms_idn==0));

			var sections=new Array()
			var allSections=["pilots_id","operators_id","instructors_id",
					"mission_types_id","rms","maintenances_id","rms_id",
					"fault_description","fault_solution","parts_removed",
					"parts_replaced","systems_involved","subsystems",
					"qea_risen_number","qea_title","statuses_id",
					"update_description","test_description","training_description"];
			for(var m in allSections)
				if(allSections[m][0])
					$("row_"+allSections[m]).style.display="none";

			sections["1"]=["pilots_id","operators_id","instructors_id","mission_types_id","rms","training_description"];
			sections["2"]=["maintenances_id"];
			sections["3"]=["rms_id","fault_description","fault_solution","parts_removed",
				"parts_replaced","systems_involved","subsystems","qea_risen_number","qea_title","statuses_id"];
			sections["4"]=["update_description"];
			sections["5"]=["pilots_id","operators_id","instructors_id","mission_types_id","rms","test_description"];
			sections["6"]=["pilots_id","operators_id","instructors_id","mission_types_id","rms"];

			for(var n in sections)
				if(sections[n][0])
					for(var m in sections[n])
						if(sections[n][m][0])
						{
							if(n==activities_types_id)
								$("row_"+sections[n][m]).style.display="table-row";

						}
		}

		function rms_add(text,closed_during_mission,id)
		{
			if(!id)
				id="";
			if((rmsn==0)||($('rms_title_'+rmsn).value.length))
			{
				rmsn++;

				var divName="div_"+rmsn;
				var newDiv=document.createElement('div');
				newDiv.setAttribute("id",divName);

				var newInput=document.createElement('input');
				var newLine=document.createElement('br');
				var inputName="rms_title_"+rmsn;
				var newlineName="newline_"+rmsn;

				newInput.setAttribute("name",inputName+"_"+id);
				newInput.setAttribute("id",inputName);
				newInput.setAttribute("value",text);
				newInput.setAttribute("size","30");
				newInput.setAttribute("maxlength","200");

				var newCheck=document.createElement('input');
				newCheck.setAttribute('type', 'checkbox');
				newCheck.setAttribute('name', "closed_"+rmsn+"_"+id);
				if(closed_during_mission)
					newCheck.setAttribute('checked','checked');

				var newText = document.createTextNode('closed during mission');
				
				newDiv.appendChild(newInput);
				newDiv.appendChild(newCheck);
				newDiv.appendChild(newText);
				newDiv.appendChild(newLine);

				$('rms_cell').appendChild(newDiv);
			

				if(rmsn><?=count($events)?>)
					$('rms_del_button').style.display='inline';
				newInput.focus();
			}
		}
		function rms_del()
		{
			if(rmsn><?=count($events)?>)
			{
				var div=$('div_'+rmsn);
				$('rms_cell').removeChild(div);
				rmsn--;
				if(rmsn><?=count($events)?>)
					$("rms_title_"+rmsn).focus();
				else
					$('rms_del_button').style.display='none';
			}
		}

		function fix_rms_id(sender)
		{
			var cell=$('rms_id_cell');
			var combos=cell.getElementsByTagName('select');
			var i,j,k;
			var busycell=new Array;

			k=0;
			do
			{
				k++;
				busycell=[];

				var ok=true;
				for(i=0;i<combos.length;i++)
				{
					if(busycell.indexOf(combos[i].value)!=-1)
						ok=false;
					else
						busycell.push(combos[i].value);
				}
				if(ok==false)
				{
					if(sender)
					{
						for(i=0;i<combos.length;i++)
							if((combos[i]!=sender)
									&&(combos[i].value==sender.value))
								break;
					}
					else
						i=combos.length-1;

					if((i<combos.length)
						&&(busycell.indexOf(combos[i].value)!=-1))
					{
						if(combos[i]!=sender)
						{
							for(j=0;j<combos[i].length;j++)
								if(busycell.indexOf(combos[i].options[j].value)==-1)
									break;
							if(j<combos[i].length)
							{
								combos[i].selectedIndex=j;
								busycell.push(combos[i].value);
							}
						}
					}
				}
			}
			while((!ok)&&(k<10))
		}

		function rms_id_add(id)
		{
			if(rms_idn>=<?=count($rms)?>)
				return;
			var o;
			var t;
			rms_idn++;
			var newLine=document.createElement('br');

			selElement = document.createElement('select');
			selElement.setAttribute('name', "rms_id_"+rms_idn);
			selElement.setAttribute('id', "rms_id_"+rms_idn);
			newLine.setAttribute('id', "rms_newline_id_"+rms_idn);
	<?
	foreach($rms as $k=>$v)
	{
			$v=str_replace("'","\'",$v);

	?>
			o = document.createElement('option');
			t = document.createTextNode('<?=$v?>');
			o.setAttribute('value','<?=$k?>');
			if(id=='<?=$k?>')
				o.setAttribute('selected','selected');
			o.appendChild(t);
			selElement.appendChild(o);
	<?
	}
	?>
			selElement.onchange=function()
				{
					fix_rms_id(this)
				};
			$('rms_id_cell').appendChild(selElement);
			$('rms_id_cell').appendChild(newLine);
//			if((rms_idn>1)&&(rms_idn><?=count($maint_rmss)?>))
			if(rms_idn>0)
			{
//				$('confirm').disabled=false;
				$('rms_id_del_button').style.display='inline';
			}
			fix_rms_id('');

		}
		function rms_id_del()
		{
//			if(rms_idn><?=count($maint_rmss)?>)
			if(rms_idn>0)
			{
				var input=$('rms_id_'+rms_idn);
				var newline=$('rms_newline_id_'+rms_idn);
				$('rms_id_cell').removeChild(input);
				$('rms_id_cell').removeChild(newline);
				rms_idn--;
//				if((rms_idn><?=count($maint_rmss)?>)&&(rms_idn>1))
				if(rms_idn>0)
				{
//					$('confirm').disabled=false;
					$("rms_id_"+rms_idn).focus();
				}
				else
				{
//					$('confirm').disabled=true;
					$('rms_id_del_button').style.display='none';
				}
			}
		}

		$('activities_types_id').onchange=function()
			{
				showSection(this.value);
			};
		$('activities_types_id').onchange();

		$('from').onblur=function()
			{
				this.value=formattaora(this);
			}
		$('to').onblur=$('from').onblur;
		$('from').focus();



		function check_post_activities(form)
		{
			var condizioni=new Array();
			condizioni["from"]=["time",0];
			condizioni["to"]=["time",0];
			condizioni["activities_types_id"]=["number",0];

			switch(Number($("activities_types_id").value))
			{
				case 1:
					condizioni["pilots_id"]=["number",0];
					condizioni["operators_id"]=["number",0];
					condizioni["mission_types_id"]=["number",0];
					break;
				case 2:
					condizioni["maintenances_id"]=["number",0];
					break;
				case 3:
					condizioni["rms_id_cell"]=["count",2];
//					condizioni["rms_id_1"]=["number",0];
					condizioni["fault_description"]=["string",""];
					condizioni["fault_solution"]=["string",""];
					break;
				case 4:
					condizioni["update_description"]=["string",""];
					break;
				case 5:
					condizioni["test_description"]=["string",""];
					break;
				default:
					break;
			}
			var out=true;
			for(var n in condizioni)
			{
				if(condizioni[n][0])
				{
					switch(condizioni[n][0])
					{
						case "time":
							if(!is_hour($(n).value))
							{
								out=false;
								$(n).style.borderColor="red";
							}
							else
								$(n).style.borderColor="";
							break;
						case "number":
							if(Number($(n).value)<=condizioni[n][1])
							{
								out=false;
								$(n).style.borderColor="red";
							}
							else
								$(n).style.borderColor="";
							break;
						case "count":
							if(Number($(n).childNodes.length)<condizioni[n][1])
							{
								out=false;
								$(n).style.borderColor="red";
							}
							else
								$(n).style.borderColor="";
							break;
							
						default:
							if(trim($(n).value)==condizioni[n][1])
							{
								out=false;
								$(n).style.borderColor="red";
							}
							else
								$(n).style.borderColor="";
							break;
					}
				}
			}
			if(!out)
				showMessage("form non validata");
//			alert(out);
			return out;
		}

<?
		if($edit)
		{
			foreach($events as $k=>$v)
			{
				$text=str_replace("'","\'",$v["text"]);

?>
				rms_add('<?=$text?>',
					<?=$v["closed_during_mission"]?>,
					'<?=$k?>');
			<?}
			foreach($maint_rmss as $k=>$v)
			{?>
				rms_id_add('<?=$v?>');
				$('activities_types_id').onchange();
			<?}
		}
		else
		{
			if($rms_id>0)
			{?>
				$('activities_types_id').value=3;
				rms_id_add(<?=$rms_id?>);
				$('activities_types_id').onchange();
			<?}?>

		<?}?>
	//]]>
	</script>
