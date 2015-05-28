<?php
require_once("include/datetime.php");
require_once("config.php");
require_once("auth.php");

if(!isset($is_logged))
	$is_logged=0;
if(!isset($_SESSION["livello"]))
	$_SESSION["livello"]="";
if(!isset($_GET["op"]))
	$_GET["op"]="activities";
if(!isset($_REQUEST["op"]))
	$_REQUEST["op"]="activities";
if(!isset($ore_lav))
	$ore_lav=0;
if(!isset($_POST["user"]))
	$_POST["user"]="";


$report=($is_logged && substr($_REQUEST["op"],0,1)=="_");
if(isset($_GET["message"]))
	$message=$_GET["message"];

if(($is_logged)&&strlen($_SESSION["livello"]))
{
	if(isset($_REQUEST["op"]))
		$op=$_REQUEST["op"];
	else
		$op="activities";


	if(isset($_REQUEST["performAction"]))
		include("include/performAction.php");

	if(!$report)
		do_header($is_logged,$expired,$_SESSION["livello"]);
	switch($op)
	{
		case "add_activities":
		case "edit_activities":
			require_once("include/activities.php");
			break;
		case "activities";
			require_once("include/activities_list.php");
			break;
		case "add_pilots":
		case "edit_pilots":
			$title="Piloti";
			$subtitle=($op=="add_pilots"?"nuovo":"modifica");
			$table="pilots";
			require_once("include/anagrafica.php");
			break;
		case "add_operators":
		case "edit_operators":
			$title="Operatori";
			$subtitle=($op=="add_operators"?"nuovo":"modifica");
			$table="operators";
			require_once("include/anagrafica.php");
			break;
		case "add_instructors":
		case "edit_instructors":
			$title="Istruttori";
			$subtitle=($op=="add_instructors"?"nuovo":"modifica");
			$table="instructors";
			require_once("include/anagrafica.php");
			break;
		case "anagrafica":
			require_once("include/anagrafica_list.php");
			break;
		case "edit_user":
		case "add_user":
			require_once("include/users.php");
			break;
		case "adm_list_users":
			require_once("include/users_list.php");
			break;
		case "reports":
			require_once("include/reports.php");
			break;
		case "_print_summary":
			require_once("include/print_summary.php");
			break;
		default:
			echo "Se finisci qui c'&egrave; qualche problema";
			break;
	}
}
else
{
	do_header($is_logged,$expired,$_SESSION["livello"],$_GET["op"],$ore_lav);

	?>
	<div id="content">
		<table border="0" cellspacing="0" cellpadding="0" style="margin-left:auto;margin-right:auto;">
			<tr>
				<td style="text-align:center;height:200px;vertical-align:middle">
	<?
	if(!$expired)
	{
		?>
		<form method="post" action="<?=$self?>">
		<table class="login_form">
			<tr>
				<td class="right">Utente:</td>
				<td class="left">
					<input type="hidden" name="private" value="1" />
					<input type="text" class="input" size="21" name="user"
						id="user" value="<?=$_POST["user"]?>" />
				</td>
			</tr>
			<tr>
				<td class="right">Password:</td>
				<td class="left">
					<input type="password" class="input" size="21" value="<?=$_POST["password"]?>" 
						name="password" title="protetta tramite hash casuale quando clicchi \'Entra\'" />
				</td>
			</tr>
			<tr>
				<td colspan="2" align="center">
					<input type="submit" class="button" name="login" value="Entra"
						title="su alcuni sistemi DEVI cliccare qui, premere invio non funziona"
						onclick="password.value = hex_md5('<?=$random_string?>' + hex_md5(password.value))" />
				</td>
			</tr>
		</table>
		</form>
		<br/>
		<div style="text-align:center">
		<a href="forgotten.php">dimenticato la password? clicca qui</a>
		</div>
		<script type="text/javascript">
			document.getElementById("user").focus();
		</script>
		<?
	}
	else
	{
		?>
		<b>modifica la password</b>
		<br/>
		<br/>
		<form name="passform" method='post' onsubmit="return false" action="<?=$self?>">
			<input type="hidden" name="id" value="<?=$_SESSION["user_id"]?>" />
			<table border="0" cellspacing="0" cellpadding="0">
				<tr>
					<td>password:</td>
					<td>
						<input type="password" class=input size="21" 
							id="newpass" name="newpass" />
					</td>
				</tr>
				<tr>
					<td>ripeti:</td>
					<td>
						<input type="password" class=input size="21" name="newpass2" />
					</td>
				</tr>
				<tr>
					<td colspan="2" align="center">
						<input type="button" class="button" name="chpwd" value="accetta"
							onclick="if(newpass.value==newpass2.value)
								{
									newpass.value = hex_md5(newpass.value);
									newpass2.value=hex_md5(newpass2.value);
									submit();
								}
								else
								{
									showMessage('le password non coincidono');
									newpass.focus();
								}"
								 />
					</td>
				</tr>
			</table>
		</form>
		<script type="text/javascript">
			document.getElementById("newpass").focus();
		</script>
		<?
	}
?>
		</td>
	</tr>
</table>
</div>
<?
}

if(!$report)
{
	?>
		<script type="text/javascript">
			var tim = setTimeout('document.getElementById("message").style.display="none";', 3000);
			function showHide(div)
			{
				var obj=document.getElementById(div);
				if(obj.style.display=='none')
					obj.style.display='';
				else
					obj.style.display='none';
			}

		</script>
		</body>
	</html>
	<?
}


function display_admin_nav($livello)
{
	global $self,$op;
	$ops=array
		(
			"0_activities"=>"activities",
			"0_anagrafica"=>"crew",
			"0_reports"=>"reports",
			"1_adm_list_users"=>"users"
		);
	?>
	<div id="admin_nav">
		<table class="admin_nav">
		<tr style="height:20px">
	<?
	foreach($ops as $k=>$v)
	{
		$minlevel=(int)substr($k,0,1);
		$k=substr($k,2);
		if($livello>=$minlevel)
		{
			$bg=($op==$k?"#ccc":"#eee")
	?>
			<td style="background-color:<?=$bg?>;width:50px;text-align:center;
					padding:0px 5px;vertical-align:middle;border-right:1px solid #222;"
					onmouseover="style.cursor='pointer';style.backgroundColor='#ddd';"
					onmouseout="style.backgroundColor='<?=$bg?>'"
					onclick="redirect('<?=$self?>&amp;op=<?=$k?>');">
				<?=$v?>
			</td>
		<?}
	}?>
		</tr>
		</table>
		</div>
	<?
}

function display_admin_submenu($subops,$subop)
{
	global $self,$op;

	$localself=$self;
	foreach($_GET as $k=>$v)
		if(($k!="op")&&($k!="subop")&&($k!="time"))
			$localself.="&amp;$k=$v";
	?>
	<div id="admin_submenu">
		<table class="admin_nav">
		<tr style="height:20px">
	<?
	foreach($subops as $k=>$v)
	{
		$bg=($subop==$k?"#ccc":"#eee")
	?>
			<td style="background-color:<?=$bg?>;width:50px;text-align:center;
					padding:0px 5px;vertical-align:middle;border-left:1px solid #222;" 
					onmouseover="style.cursor='pointer';style.backgroundColor='#ddd';"
					onmouseout="style.backgroundColor='<?=$bg?>'"
					onclick="redirect('<?=$localself?>&amp;op=<?=$op?>&amp;subop=<?=$k?>');">
				<?=$v?>
			</td>
	<?}?>
		</tr>
		</table>
	</div>
	<?
}


function logged_header($titolo1,$titolo2)
{
	global $giorniSettimana,$mesi,$self;
	$height=28;
	$width="33%";

	if($_SESSION["livello"]>0)
	{
		$query="SELECT * FROM devices";
		if($_SESSION["livello"]<3)
			$query.=" WHERE (1 << (sites_id-1)) & ".(int)$_SESSION["sites"]." >0";
		$conn=opendb();
		$result=do_query($query,$conn);
		$devices=result_to_array($result,true);
		$where="";
		foreach($devices as $id=>$row)
			$where.="$id,";
		$where=rtrim($where,",");
	
		$query="
			SELECT activities.devices_id,rms.id as rms_id,
				REPLACE(rms.text,',','{') AS text,statuses.name AS status,
				devices.name AS device,
				concat(sites.code,LPAD(rms.number,5,'0')) AS number
			FROM rms LEFT JOIN activities ON rms.activities_id=activities.id
				LEFT JOIN statuses ON rms.statuses_id=statuses.id
				LEFT JOIN devices ON activities.devices_id=devices.id 
				LEFT JOIN sites ON devices.sites_id=sites.id
			WHERE rms.statuses_id!=0 AND devices.id IN ($where)
			ORDER BY devices_id";
		$result=do_query($query,$conn);
		$rows=result_to_array($result,false);
		$n=count($rows);
		if($n)
		{
			$alert="<img src='img/alert_red.png' alt='alert_red.png'
				onclick=\"showHide('alertDiv')\" 
				onmouseover=\"style.cursor='pointer'\"/>";
			$giorno=$_GET["giorno"];
			if(!strlen($giorno))
				$giorno=date("d.m.Y");
			$address="index.php?time=".time()."&op=add_activities&giorno=$giorno";
			$block="";
			foreach($rows as $row)
			{
				$devices_id=$row["devices_id"];
				$rms_id=$row["rms_id"];
				$block.="<a href='$address&rms_id=".$devices_id."_".$rms_id."'>\n";
				$block.=$row["device"]." - ".$row["number"]." - ".substr($row["text"],0,28)." - ".$row["status"]."</a><br>\n";
			}
		}
		closedb($conn);
	}
	?>
	<div id="header">
		<form action="<?=$self?>" method="post" style="margin: 0px;">
			<table class="tab_header">
				<tr>
					<td rowspan="2" style="padding-left:10px;width:<?=$width?>;text-align:left">
						<?=sprintf("%s %s",$_SESSION["nome"],$_SESSION["cognome"]);?><br />
						<?=$_SESSION["site_name"]?>
					</td>
					<td style="width:<?=$width?>;text-align:center;height:<?=$height?>px;vertical-align:middle;
							margin:0px; padding:0px;white-space: nowrap;font-size:120%">
							<?=$titolo1?>
					</td>
					<td style="width:<?=$width?>;height:<?=$height?>px;text-align:right; margin:0px; padding:0px; vertical-align: top;white-space: nowrap;">
						<?=$alert?>
						<div id="alertDiv" 
							style="background-color:white;
									text-align:left;
									height:200px;
									width:auto;
									display:none;
									font-size:10px;
									font-weight:normal;
									position:absolute;
									right:0px;
									top:81px;
									overflow-y: auto;
									overflow-x: auto;
									padding:5px 20px 5px 5px;
									border:1px solid #ccc">
							<?=$block?>
						</div>

						<input type="submit" class="button" value="Exit" name="logout" />
					</td>
				</tr>
				<tr>
					<td>
						<?=$titolo2?>
					</td>
					<td style="color:#c00;text-align:right">
					</td>
				</tr>
			</table>
		</form>
	</div>
	<?
	display_admin_nav($_SESSION["livello"]);
}

function do_header($is_logged,$expired,$level)
{
	global $message,$version,$siteName;
	$ie=strstr($_SERVER["HTTP_USER_AGENT"],"MSIE");
	if($ie)
		echo '﻿<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">';
	else
		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">';
	?>
	<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="it">
	<head>
	<link rel="icon" href="favicon.png" />
	<title><?=$siteName?></title>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<meta name="description" content="envysoft secure authentication" />
	<meta name="keywords" content="php,javascript,authentication,md5,hashing,php,javascript,authenticating,auth,AUTH,secure,secure login,security,php and javascript secure authentication,combat session fixation!" />
	<script type="text/javascript" src="include/prototype.js"></script>
	<script type="text/javascript" src="md5.js"></script>
	<script type="text/javascript" src="include/datetime.js"></script>
	<script type="text/javascript" src="include/util.js"></script>
	<script type="text/javascript" src="include/html-form-input-mask.js"></script>
	<script type="text/javascript" src="include/autocomplete.js"></script>
	<link rel="stylesheet" type="text/css" href="autocomplete.css" />
	<link rel="stylesheet" href="style.css" title="envysheet" type="text/css" />
	<script type="text/javascript" src="include/cal.js"></script>
	</head>
	<body>
	<div id="messageContainer" style="position:<?=($ie?"absolute":"fixed")?>;text-align:<?=($is_logged?"right":"center")?>;">
		<span id="message">
			<?=$message?>
		</span>
	</div>
	<?
}

?>
