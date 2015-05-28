<?
	if(isset($_REQUEST["giorno"]))
		$giorno=$_REQUEST["giorno"];
	else
		$giorno=date("d.m.Y");
	list($dd,$mm,$yy)=explode(".",$giorno);
	$giorno_sql=sprintf("%s-%s-%s",$yy,$mm,$dd);
	$oggi_time=mktime(0,0,0,$mm,$dd,$yy);
	$prec=date("d.m.Y",strtotime("-1 days",$oggi_time));
	$succ=date("d.m.Y",strtotime("+1 days",$oggi_time));
	$title="EFA SIMULATOR - DAILY ACTIVITIES LOGBOOK - OPERATIONS & MAINTENANCE";

	$query="SELECT * FROM devices";
	if($_SESSION["livello"]<3)
		$query.=" WHERE ((1<< (sites_id-1)) & ".$_SESSION["sites"].")>0";

	$conn=opendb();
	$result=do_query($query,$conn);
	$devices=result_to_array($result,true);
	$devices_keys=array_keys($devices);

	$subops=array();
	foreach($devices as $id=>$array)
		$subops[$id]=$array["name"];

	if(isset($_REQUEST["subop"]))
		$device_id=$_REQUEST["subop"];
	else
		$device_id=$devices_keys[0];
	$_GET["subop"]=$device_id;
	$_GET["giorno"]=$giorno;

/*	$query="SELECT group_concat(concat(sites.code,LPAD(rms.number,5,'0'),
				' - ',REPLACE(rms.text,',','{'),' - ',statuses.name)) as status,
			activities.*, 
			CONCAT(utenti.nome,' ',utenti.cognome) AS utente, 
			activities_types.name AS activity, 
			max(statuses.name) AS status1,
			group_concat(concat(sites.code,LPAD(rms_maint.number,5,'0'),
				' - ',REPLACE(rms_maint.text,',','{'),' - ',statuses_maint.name)) as status_maint
		FROM activities LEFT JOIN utenti ON activities.users_id=utenti.id 
			LEFT JOIN activities_types ON activities.activities_types_id=activities_types.id 
			LEFT JOIN devices ON activities.devices_id=devices.id 
			LEFT JOIN sites ON devices.sites_id=sites.id
			LEFT JOIN rms ON activities.id=rms.activities_id 
			LEFT JOIN statuses ON rms.statuses_id=statuses.id 
			LEFT JOIN maint_rms ON activities.maint_id=maint_rms.maint_id
			LEFT JOIN rms AS rms_maint ON rms_maint.id=maint_rms.rms_id
			LEFT JOIN statuses AS statuses_maint ON activities.statuses_id=statuses_maint.id 
		WHERE activities.devices_id='$device_id'
			AND activities.date='$giorno_sql'
		GROUP BY activities.id
		ORDER BY activities.from,activities.to";
	$result=do_query($query,$conn);
	$rows=result_to_array($result,true);
	foreach($rows as $id=>$row)
	{
		$rows[$id]["from"]=int_to_hour($row["from"]);
		$rows[$id]["to"]=int_to_hour($row["to"]);
		if(strlen($rows[$id]["status_maint"]))
			$rows[$id]["status"]=$rows[$id]["status_maint"];

		if(!strlen($rows[$id]["status"]))
			$rows[$id]["status"]="---";
		else
		{
			$exploded=explode(",",$rows[$id]["status"]);
			$rows[$id]["status"]="";
			foreach($exploded as $line)
				$rows[$id]["status"].=str_replace('{',',',$line)."<br>";
			$rows[$id]["status"]=rtrim($rows[$id]["status"],"<br>");
		}
	}
*/

	$query="SELECT concat(sites.code,LPAD(rms.number,5,'0')) as rms_number,
			REPLACE(rms.text,',','{') as rms_text,
			rms.id AS rms_id,rms_maint.id AS rms_maint_id,
			statuses.name as status_name,
			activities.*, 
			CONCAT(utenti.nome,' ',utenti.cognome) AS utente, 
			activities_types.name AS activity, 
			concat(sites.code,LPAD(rms_maint.number,5,'0')) as rms_maint_number,
			REPLACE(rms_maint.text,',','{') as rms_maint_text,
			statuses_maint.name as status_maint_name,
			CONCAT(pilots.surname,' ',pilots.name) as pilot_name,
			CONCAT(instructors.surname,' ',instructors.name) as instructor_name,
			CONCAT(operators.surname,' ',operators.name) as operator_name,
			mission_types.name as activity_type,
			CASE activities_types_id 
				WHEN 1 THEN training_description 
				WHEN 5 THEN test_description 
				ELSE '' END as note
		FROM activities LEFT JOIN utenti ON activities.users_id=utenti.id 
			LEFT JOIN activities_types ON activities.activities_types_id=activities_types.id 
			LEFT JOIN mission_types ON activities.mission_types_id=mission_types.id 
			LEFT JOIN devices ON activities.devices_id=devices.id 
			LEFT JOIN sites ON devices.sites_id=sites.id
			LEFT JOIN rms ON activities.id=rms.activities_id 
			LEFT JOIN statuses ON rms.statuses_id=statuses.id 
			LEFT JOIN maint_rms ON activities.maint_id=maint_rms.maint_id
			LEFT JOIN rms AS rms_maint ON rms_maint.id=maint_rms.rms_id
			LEFT JOIN statuses AS statuses_maint ON activities.statuses_id=statuses_maint.id 
			LEFT JOIN pilots ON pilots.id=activities.pilots_id
			LEFT JOIN instructors ON instructors.id=activities.instructors_id
			LEFT JOIN operators ON operators.id=activities.operators_id
		WHERE activities.devices_id='$device_id'
			AND activities.date='$giorno_sql'
		ORDER BY activities.from,activities.to,rms.id,rms_maint.id";

	$result=do_query($query,$conn);

	$array=result_to_array($result,false);
	$rows=array();
	$address="index.php?time=".time()."&giorno=$giorno&op=add_activities&rms_id=".$device_id;
	foreach($array as $foo=>$row)
	{
		$id=$row["id"];
		$rows[$id]["from"]=int_to_hour($row["from"]);
		if($_SESSION["user_id"]==1)
			$rows[$id]["from"]="<input id='delete_$id' type='button' onclick='if(event.stopPropagation){event.stopPropagation();}event.cancelBubble=true;deleterow($id)' value='d'>".$rows[$id]["from"];
		$rows[$id]["to"]=int_to_hour($row["to"]);
		$rows[$id]["activity"]=$row["activity"];
		$rows[$id]["activity_type"]=(strlen(trim($row["activity_type"]))?$row["activity_type"]:"----");
		$rows[$id]["utente"]=$row["utente"];
		$rows[$id]["pilot_name"]=$row["pilot_name"];
		$rows[$id]["operator_name"]=$row["operator_name"];
		$rows[$id]["instructor_name"]=$row["instructor_name"];
		if(strlen($row["rms_maint_number"]))
		{
			if(($row["status_name"]=="OPEN")||($row["status_name"]=="HOLD"))
				$row["rms_maint_number"]="<a href='".$address."_".$row["rms_maint_id"]."'>".$row["rms_maint_number"]."</a>";
			$rows[$id]["rms_number"].=$row["rms_maint_number"]."<br>";
			$rows[$id]["rms_text"].=$row["rms_maint_text"]."<br>";
			$rows[$id]["status_name"].=$row["status_maint_name"]."<br>";
		}
		else
		{
			if(($row["status_name"]=="OPEN")||($row["status_name"]=="HOLD"))
				$row["rms_number"]="<a href='".$address."_".$row["rms_id"]."'>".$row["rms_number"]."</a>";
			$rows[$id]["rms_number"].=$row["rms_number"]."<br>";
			$rows[$id]["rms_text"].=$row["rms_text"]."<br>";
			$rows[$id]["status_name"].=$row["status_name"]."<br>";
		}
		$rows[$id]["note"]=(strlen(trim($row["note"]))?substr($row["note"],0,15):"----");
	}

	foreach($rows as $id=>$row)
	{
		$rows[$id]["rms_number"]=rtrim($row["rms_number"],"<br>");
		$rows[$id]["rms_text"]=rtrim(str_replace('{',',',$row["rms_text"]),"<br>");
		$rows[$id]["status_name"]=rtrim($row["status_name"],"<br>");
		if(!strlen(trim($rows[$id]["rms_number"])))
		{
			$rows[$id]["rms_number"]="----";
			$rows[$id]["rms_text"]="----";
			$rows[$id]["status_name"]="----";
		}
	}

	$columns=array
			(
				"from"=>array("title"=>"From time","align"=>"left"),
				"to"=>array("title"=>"To time","align"=>"left"),
				"activity"=>array("title"=>"Activity","align"=>"left"),
				"activity_type"=>array("title"=>"Mission type","align"=>"left"),
				"pilot_name"=>array("title"=>"Pilot","align"=>"left"),
				"operator_name"=>array("title"=>"Operator","align"=>"left"),
				"instructor_name"=>array("title"=>"Instructor","align"=>"left"),
				"note"=>array("title"=>"Note","align"=>"left"),
				"rms_number"=>array("title"=>"RMS number","align"=>"left"),
				"rms_text"=>array("title"=>"Text","align"=>"left"),
				"status_name"=>array("title"=>"Status","align"=>"left"),
				"utente"=>array("title"=>"User","align"=>"left")
			);


	closedb($conn);

	$subtitle='
		<img src="img/left.png" alt="left"
			onmouseover="style.cursor=\'pointer\'" 
			onclick="redirect(\''.$self.'&subop='.$device_id.'&giorno='.$prec.'\');" />
		<input style="display:none;"
			type="text" size="12" readonly="readonly"
			id="giorno_cal"
			value="'.$giorno.'"
			onchange="redirect(\''.$self.'&subop='.$device_id.'&giorno=\'+this.value);" />
		'.$giorno.'
		<img src="img/calendar.png" onmouseover="style.cursor=\'pointer\'" alt="calendar"
			style="height:25px;vertical-align:middle;"
			onclick=\'showCalendar("", this,document.getElementById("giorno_cal"), "dd.mm.yyyy","it",1,0)\' />
		<img src="img/right.png" alt="right"
			onmouseover="style.cursor=\'pointer\'" 
			onclick="redirect(\''.$self.'&subop='.$device_id.'&giorno='.$succ.'\');" />';

	require_once("include/util.php");

	logged_header($title,$subtitle);
	display_admin_submenu($subops,$device_id);
?>
<div id="content">
<?
	drawTableEdit($rows,$columns,0,"activities");
?>
</div>
