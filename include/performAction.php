<?

	if($_POST["performAction"]=="activities")
	{
		$activities_id=$_POST["id_activities"];
		$giorno=$_POST["giorno"];
		list($d,$m,$y)=explode(".",$giorno);
		$giorno_sql=sprintf("%s-%s-%s",$y,$m,$d);
		$device_id=$_POST["subop"];
		$subop=$device_id;
		$from=hour_to_int($_POST["from"]);
		$to=hour_to_int($_POST["to"]);
		$activities_types_id=$_POST["activities_types_id"];

		$sim_flight=(($activities_types_id==1)||($activities_types_id==5)||($activities_types_id==6));
		$pilots_id=($sim_flight?$_POST["pilots_id"]:0);
		$operators_id=($sim_flight?$_POST["operators_id"]:0);
		$instructors_id=($sim_flight?$_POST["instructors_id"]:0);
		$mission_types_id=($sim_flight?$_POST["mission_types_id"]:0);
		$maintenances_id=($activities_types_id==2?$_POST["maintenances_id"]:0);
		$fault_description=($activities_types_id==3?$_POST["fault_description"]:"");
		$fault_solution=($activities_types_id==3?$_POST["fault_solution"]:"");
		$parts_removed=($activities_types_id==3?$_POST["parts_removed"]:"");
		$parts_replaced=($activities_types_id==3?$_POST["parts_replaced"]:"");
		$systems_involved=0;
		$rmss_toadd=array();
		$rmss_toedit=array();
		$maint_id=0;

		if($activities_types_id==3)
		{
			$rms_ids=array();

			foreach($_POST as $k=>$v)
			{
				if(substr($k,0,17)=="systems_involved_")
					$systems_involved+=(1<<$v);
				elseif(substr($k,0,6)=="rms_id")
					$rms_ids[]=$v;
			}
		}
		elseif($sim_flight)
		{
			foreach($_POST as $k=>$v)
			{
				if(substr($k,0,10)=="rms_title_")
				{
					list($foo,$temp_rms_id)=explode("_",substr($k,10));
					if(strlen($temp_rms_id))
						$rmss_toedit[$temp_rms_id]=$v;
					elseif(strlen(trim($v)))
						$rmss_toadd[]=$v;
				}
			}
		}
		$subsystems=($activities_types_id==3?$_POST["subsystems"]:"");
		$qea_risen_number=($activities_types_id==3?$_POST["qea_risen_number"]:0);
		$qea_title=($activities_types_id==3?$_POST["qea_title"]:"");
		$statuses_id=($activities_types_id==3?$_POST["statuses_id"]:"");
		$update_description=($activities_types_id==4?$_POST["update_description"]:"");
		$test_description=($activities_types_id==5?$_POST["test_description"]:"");
		$training_description=($activities_types_id==1?$_POST["training_description"]:"");

		$edit=(strlen($activities_id)>0);

		$conn=opendb();
		do_query("SET autocommit=0",$conn);
		do_query("LOCK TABLES activities WRITE,rms WRITE, devices WRITE, maint_rms WRITE",$conn);

		if($edit)
		{
			$query="SELECT * FROM maint_rms WHERE maint_id
				IN (SELECT maint_id FROM activities WHERE activities.id='$activities_id')";
			$result=do_query($query,$conn);
			$rows=result_to_array($result,true);
			foreach($rows as $row)
				fixStatus($row["rms_id"],$activities_id,$conn);
			$query="DELETE FROM maint_rms WHERE maint_id
				IN (SELECT maint_id FROM activities WHERE activities.id='$activities_id')";
			do_query($query,$conn);
			$query="UPDATE activities SET
						users_id='".$_SESSION["user_id"]."',
						devices_id='$device_id',
						date='$giorno_sql',
						`from`='$from',
						`to`='$to',
						activities_types_id='$activities_types_id',
						pilots_id='$pilots_id',
						operators_id='$operators_id',
						instructors_id='$instructors_id',
						mission_types_id='$mission_types_id',
						maintenances_id='$maintenances_id',
						fault_description='$fault_description',
						fault_solution='$fault_solution',
						parts_removed='$parts_removed',
						parts_replaced='$parts_replaced',
						systems_involved='$systems_involved',
						subsystems='$subsystems',
						qea_risen_number='$subsystems',
						qea_title='$qea_title',
						update_description='$update_description',
						test_description='$test_description',
						training_description='$training_description',
						maint_id='$maint_id',
						statuses_id='$statuses_id'
					WHERE id='$activities_id'";
			$message="Modifica effettuata";
		}
		else
		{
			$query="INSERT INTO activities
					(
						users_id,
						devices_id,
						date,
						`from`,
						`to`,
						activities_types_id,
						pilots_id,
						operators_id,
						instructors_id,
						mission_types_id,
						maintenances_id,
						fault_description,
						fault_solution,
						parts_removed,
						parts_replaced,
						systems_involved,
						subsystems,
						qea_risen_number,
						qea_title,
						update_description,
						test_description,
						training_description,
						maint_id,
						statuses_id
					)
					VALUES
					(
						'".$_SESSION["user_id"]."',
						'$device_id',
						'$giorno_sql',
						'$from',
						'$to',
						'$activities_types_id',
						'$pilots_id',
						'$operators_id',
						'$instructors_id',
						'$mission_types_id',
						'$maintenances_id',
						'$fault_description',
						'$fault_solution',
						'$parts_removed',
						'$parts_replaced',
						'$systems_involved',
						'$subsystems',
						'$subsystems',
						'$qea_title',
						'$update_description',
						'$test_description',
						'$training_description',
						'$maint_id',
						'$statuses_id'
					)";
			$message="Inserimento effettuato";
		}
		do_query($query,$conn);
		if(!$edit)
			$activities_id=((is_null($___mysqli_res = mysqli_insert_id($conn))) ? false : $___mysqli_res);

		$query="SELECT sites_id FROM devices
				WHERE id='$device_id'";
		$result=do_query($query,$conn);
		$sites_array=result_to_array($result,false);
		$sites_id=(int)$sites_array[0]["sites_id"];

		$query="SELECT number FROM rms LEFT JOIN activities ON
					rms.activities_id=activities.id
					LEFT JOIN devices ON activities.devices_id=devices.id
				WHERE devices.sites_id='$sites_id'
				ORDER BY number DESC
				LIMIT 0,1";

		$result=do_query($query,$conn);
		$number_array=result_to_array($result,false);
		$number=(int)$number_array[0]["number"];

		foreach($rmss_toadd as $text)
		{
			$number++;
			$query="INSERT INTO rms (activities_id,number,text,sites_id)
					VALUES ('$activities_id','$number','$text','$sites_id')";
			do_query($query,$conn);
		}
		foreach($rmss_toedit as $temp_rms_id=>$text)
		{
			if(strlen(trim($text)))
			{
				$query="UPDATE rms SET text='$text'
					WHERE id='$temp_rms_id'";
				do_query($query,$conn);
			}
			else
			{
				$query="SELECT activities.* FROM activities
						LEFT JOIN maint_rms ON activities.maint_id=maint_rms.maint_id
						WHERE maint_rms.rms_id='$temp_rms_id'";
				$result=do_query($query,$conn);
				$rows=result_to_array($result);
				if(!count($rows))
				{
					$query="DELETE FROM rms WHERE id='$temp_rms_id'";
					do_query($query,$conn);
				}
			}
		}
		if($activities_types_id==3)
		{
			$query="SELECT max(maint_id) AS last_maint_id FROM maint_rms";
			$result=do_query($query,$conn);
			$rows=result_to_array($result,false);
			if(strlen($rows[0]["last_maint_id"]))
				$maint_id=$rows[0]["last_maint_id"]+1;
			else
				$maint_id=1;

			$query="UPDATE activities SET maint_id='$maint_id'
						WHERE id='$activities_id'";
			do_query($query,$conn);


			foreach($rms_ids as $temp_rms_id)
			{
				$query="INSERT INTO maint_rms (maint_id,rms_id)
						VALUES ('$maint_id','$temp_rms_id')";
				do_query($query,$conn);


				$query="SELECT activities.id
						FROM activities LEFT JOIN maint_rms ON activities.maint_id=maint_rms.maint_id
						WHERE maint_rms.rms_id='$temp_rms_id'
						ORDER BY date DESC, `to` DESC
						LIMIT 0,1";
				$result=do_query($query,$conn);
				$rows=result_to_array($result,true);
				if(isset($rows[$activities_id])?1:0)
				{
					$query="UPDATE rms SET statuses_id='$statuses_id' WHERE id='$temp_rms_id'";
					do_query($query,$conn);
				}


			}
		}

		do_query("UNLOCK TABLES",$conn);
		do_query("SET autocommit=1",$conn);

		closedb($conn);
		$op="activities";
		$message="Modifica effettuata";
		header("Location: $self&op=$op&subop=$subop&giorno=$giorno&message=$message");

	}
	elseif($_POST["performAction"]=="anagrafica")
	{
		$flipped=array_flip($_POST);
		list($edit,$table)=explode("_",$flipped["confirm"]);
		$name=str_replace("'","\'",$_POST["name"]);
		$surname=str_replace("'","\'",$_POST["surname"]);
		if($edit=="edit")
		{
			$id=$_POST["id_anagrafica"];
			$query="UPDATE $table SET
				name='$name',
				surname='$surname'
				WHERE id='$id'";
		}
		else
			$query="INSERT INTO $table(name,surname)
				VALUES ('$name','$surname')";
		$conn=opendb();
		do_query($query,$conn);
		closedb($conn);
		$op="anagrafica";
		$subop=$table;
		$message=($edit=="edit"?"Modifica effettuata":"Inserimento effettuato");
		header("Location: $self&op=$op&subop=$subop&message=$message");
	}
	elseif(isset($_POST["edit_user"])&&($_SESSION["livello"]>0))
	{
		$expired=(isset($_POST["expired"])?1:0);
		$attivo=(isset($_POST["attivo"])?1:0);
		$sites=0;
		if(is_array($_POST["sites"]))
			foreach($_POST["sites"] as $site)
				$sites+=$site;

		$conn=opendb();
		$query="UPDATE utenti SET login='".$_POST["utente"]."',
					nome='".$_POST["nome"]."',
					cognome='".$_POST["cognome"]."',
					email='".$_POST["email"]."',
					livello=".$_POST["livello"].",
					sites=".$sites.",
					expired=$expired,
					attivo=$attivo
				WHERE id='".$_POST["id_admin_users"]."'";
		do_query($query,$conn);
		closedb($conn);
		$op="adm_list_users";
		$message="Modifica effettuata";
		header("Location: $self&op=$op&message=$message");
	}
	elseif(isset($_POST["add_user"])&&($_SESSION["livello"]>0))
	{
		include("include/pwgenerator.php");
		$sites=(int)$_POST["sites"];
		$pass=randomPass();
		$conn=opendb();
		$query="INSERT INTO utenti(login,pass,nome,cognome,email,sites,
					livello,expired)
				VALUES('".$_POST["utente"]."', md5('$pass'),
					'".$_POST["nome"]."',
					'".$_POST["cognome"]."',
					'".$_POST["email"]."',
					'".$sites."',
					".$_POST["livello"].", 1)";
		do_query($query,$conn);
		closedb($conn);
		require_once("include/mail.php");

		$from = "System Administrator <noreply@hightecservice.biz>";
		$to = $_POST["nome"]." ".$_POST["cognome"]." <".$_POST["email"].">";
		$subject = "registratione utente";

		$mailtext=file_get_contents("template/mailTemplateNewUser.html");
		$mailtext=str_replace("{username}",$_POST["utente"],$mailtext);
		$mailtext=str_replace("{password}",$pass,$mailtext);
		$mailtext=str_replace("{name}",$_POST["nome"],$mailtext);
		$mailtext=str_replace("{surname}",$_POST["cognome"],$mailtext);
		$mailtext=str_replace("{siteAddress}",$siteAddress,$mailtext);
		emailHtml($from, $subject, $mailtext, $to);

		$message="Utente inserito";
		$op="adm_list_users";
		header("Location: $self&op=$op&message=$message");
	}
	elseif(isset($_GET["user_to_del"])&&($_SESSION["livello"]>0))
	{
		$conn=opendb();
		$query="UPDATE utenti SET eliminato=1,attivo=0 WHERE id='".$_GET["user_to_del"]."'";
		do_query($query,$conn);
		closedb($conn);
		$message="Utente eliminato";
		$op="adm_list_users";
		header("Location: $self&op=$op&message=$message");
	}
	elseif(isset($_GET["user_to_reset"])&&($_SESSION["livello"]>0))
	{
		include("include/pwgenerator.php");
		$pass=randomPass();
		$conn=opendb();
		$id=$_GET["user_to_reset"];
		$query="UPDATE utenti SET pass=md5('$pass'),expired=1 WHERE id='$id'";
		do_query($query,$conn);
		$query="SELECT * FROM utenti WHERE id='$id'";
		$result=do_query($query,$conn);
		$rows=result_to_array($result,true);
		$row=$rows[$id];
		closedb($conn);

		require_once("include/mail.php");
		$from = "System Administrator <noreply@hightecservice.biz>";
		$to = $row["nome"]." ".$row["cognome"]." <".$row["email"].">";
		$subject = "nuova password";

		$mailtext=file_get_contents("template/mailTemplateNewPass.html");
		$mailtext=str_replace("{username}",$row["login"],$mailtext);
		$mailtext=str_replace("{password}",$pass,$mailtext);
		$mailtext=str_replace("{name}",$row["nome"],$mailtext);
		$mailtext=str_replace("{surname}",$row["cognome"],$mailtext);
		$mailtext=str_replace("{siteAddress}",$siteAddress,$mailtext);
		emailHtml($from, $subject, $mailtext, $to);

		$message="Password resettata";
		$op="adm_list_users";
		header("Location: $self&op=$op&message=$message");
	}


function fixStatus($rms_id,$activities_id,$conn)
{
	$query="SELECT statuses_id FROM activities
				LEFT JOIN maint_rms ON activities.maint_id=maint_rms.maint_id
				WHERE maint_rms.rms_id='$rms_id'
				AND activities.id!='$activities_id'
			ORDER BY activities.date DESC, `to` DESC
			LIMIT 0,1";

	$result=do_query($query,$conn);
	$rows=result_to_array($result,false);
	if(!count($rows))
		$new_status=1;
	else
		$new_status=$rows[0]["statuses_id"];

	$query="UPDATE rms SET statuses_id='$new_status'
			WHERE id='$rms_id'";

	do_query($query,$conn);
}
?>
