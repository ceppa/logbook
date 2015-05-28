<?
	$xls=$_POST["xls"];
	$da=date_to_sql($_POST["logbook_da"]);
	$a=date_to_sql($_POST["logbook_a"]);

	$devstring="";
	$devwhere="";
	if(is_array($_POST["devices"]))
	{
		foreach($_POST["devices"] as $dev)
			$devstring.="$dev,";
		$devstring=rtrim($devstring,",");
		if(strlen($devstring))
			$devwhere=" AND activities.devices_id in ($devstring)";
	}
	$actstring="";
	$actwhere="";
	if(is_array($_POST["activity_type"]))
	{
		foreach($_POST["activity_type"] as $act)
			$actstring.="$act,";
		$actstring=rtrim($actstring,",");
		if(strlen($actstring))
			$actwhere=" AND activities.activities_types_id in ($actstring)";
	}

	$query="
	SELECT	devices.name,
			activities.date,
			activities.activities_types_id,
			CONCAT(activities.from div 60,':',LPAD(activities.from mod 60,2,'0')) AS `from`,
			CONCAT(activities.to div 60,':',LPAD(activities.to mod 60,2,'0')) AS `to`,
			activities.to-activities.from AS durata,
			activities_types.name AS activity, 
			CAST(group_concat(concat(sites.code,LPAD(rms.number,5,'0'),
				' - ',REPLACE(rms.text,',','{'),' - ',statuses.name)) AS CHAR(1000)) as rms,
			CAST(group_concat(concat(sites.code,LPAD(rms_maint.number,5,'0'),
				' - ',REPLACE(rms_maint.text,',','{'),' - ',statuses_maint.name)) AS CHAR(1000)) as rms_maint,
			CAST(group_concat(statuses.name) AS CHAR(1000)) as status,
			CAST(group_concat(statuses_maint.name) AS CHAR(1000)) as status_maint,
			activities.fault_description,
			activities.fault_solution,
			CAST(GROUP_CONCAT( DISTINCT systems.name) AS CHAR(1000)) AS systems_involved,
			activities.qea_risen_number,
			CONCAT(pilots.surname,' ',pilots.name) as pilot,
			CONCAT(operators.surname,' ',operators.name) as operator,
			CONCAT(instructors.surname,' ',instructors.name) as instructor,
			mission_types.name as activity_type,
			activities.test_description,
			CASE activities_types_id 
				WHEN 1 THEN training_description 
				WHEN 5 THEN test_description 
				ELSE '' END as note

		FROM activities LEFT JOIN utenti ON activities.users_id=utenti.id 
			LEFT JOIN activities_types ON activities.activities_types_id=activities_types.id 
			LEFT JOIN mission_types ON activities.mission_types_id=mission_types.id 
			LEFT JOIN pilots ON activities.pilots_id=pilots.id 
			LEFT JOIN instructors ON activities.instructors_id=instructors.id 
			LEFT JOIN operators ON activities.operators_id=operators.id 
			LEFT JOIN devices ON activities.devices_id=devices.id 
			LEFT JOIN sites ON devices.sites_id=sites.id
			LEFT JOIN rms ON activities.id=rms.activities_id 
			LEFT JOIN statuses ON rms.statuses_id=statuses.id 
			LEFT JOIN maint_rms ON activities.maint_id=maint_rms.maint_id
			LEFT JOIN rms AS rms_maint ON rms_maint.id=maint_rms.rms_id
			LEFT JOIN statuses AS statuses_maint ON activities.statuses_id=statuses_maint.id 
			LEFT JOIN systems ON activities.systems_involved & (1 << CAST(systems.id AS UNSIGNED)) >0
		WHERE activities.date BETWEEN '$da' and '$a'
		$devwhere $actwhere
		GROUP BY activities.id
		ORDER BY activities.devices_id,activities.date,activities.from,activities.to,activities.activities_types_id
		";

	$conn=opendb();
	$result=do_query($query,$conn);
	$rows=result_to_array($result,false);
	foreach($rows as $id=>$row)
	{
		if($row["activities_types_id"]==3)
		{
			$rows[$id]["rms"]=$row["rms_maint"];
			$rows[$id]["status"]=$row["status_maint"];
		}
		if(!$row["qea_risen_number"])
			$rows[$id]["qea_risen_number"]="----";
	}
	closedb($conn);

	if($xls)
		require_once("include/print_summary_xls.php");
?>
