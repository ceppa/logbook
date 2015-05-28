<?
	include ("config.php");
	$self=$_SERVER["PHP_SELF"];


	function randomPass()
	{
		$out="";
		for($i=0;$i<6;$i++)
			$out.=chr(rand(97,122));
		for($i=0;$i<2;$i++)
			$out.=chr(rand(48,57));
		return $out;
	}


	if(isset($_POST["send"]))
	{
		include("include/mysql.php");
				$conn=($GLOBALS["___mysqli_ston"] = mysqli_connect($myhost, $myuser, $mypass))
			or die("Connessione non riuscita".((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
		((bool)mysqli_query($conn, "USE " . $dbname));
		$query="SELECT id,nome,cognome,login,email 
					FROM utenti 
					WHERE login='".$_POST["user"]."' 
					AND email='".$_POST["email"]."'";
		$result=mysqli_query($GLOBALS["___mysqli_ston"], $query) 
			or die($query."<br>".((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
		if(mysqli_num_rows($result)==0)
		{
			$message="nessun utente corrisponde ai criteri impostati";
			if(!isset($_POST["private"]))
			{
				((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);
				((is_null($___mysqli_res = mysqli_close($conn))) ? false : $___mysqli_res);
				echo $message;
				die();
			}
		}
		else
		{
			$row=mysqli_fetch_assoc($result);
			((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);

			$pass=randomPass();
			$query="UPDATE utenti SET pass=MD5(\"$pass\"),expired=1 WHERE id=".$row["id"];
			mysqli_query($GLOBALS["___mysqli_ston"], $query) 
				or die($query."<br>".((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
			((is_null($___mysqli_res = mysqli_close($conn))) ? false : $___mysqli_res);

			require_once("include/mail.php");
			$from = "System Administrator <noreply@hightecservice.biz>";
			$to = $row["nome"]." ".$row["cognome"]." <".$_POST["email"].">";
			$subject = "invio password";

			$mailtext=file_get_contents("template/mailTemplateNewPass.html");
			$mailtext=str_replace("{username}",$row["login"],$mailtext);
			$mailtext=str_replace("{password}",$pass,$mailtext);
			$mailtext=str_replace("{name}",$row["nome"],$mailtext);
			$mailtext=str_replace("{surname}",$row["cognome"],$mailtext);
			$mailtext=str_replace("{siteAddress}",$siteAddress,$mailtext);
			emailHtml($from, $subject, $mailtext, $to);

			$message="password inviata a ".$row["email"];
			if(isset($_POST["private"]))
				header("Location: index.php?message=$message");
			die();
		}	
		((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);
		((is_null($___mysqli_res = mysqli_close($conn))) ? false : $___mysqli_res);
	
	}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
		"http://www.w3.org/TR/html4/loose.dtd">
	<html>
	<head>
	<link rel="icon" href="favicon.png">
	<meta http-equiv="content-type" content="text/html; charset=utf-8">
	<title><?=$siteName?></title>
	<meta name="description" content="envysoft secure authentication">
	<meta name="keywords"content="php,javascript,authentication,md5,hashing,php,javascript,authenticating,auth,AUTH,secure,secure login,security,php and javascript secure authentication,combat session fixation!">
	<link rel="stylesheet" href="style.css" title="envysheet" type="text/css">
	</head>
	<body class="user" onLoad="javascript:document.passform.user.focus();">
		<div id="header">
		<p class="message" style="margin-top:40px;"><?=$message?></p>
	</div>
	<div id="content">
		<table border="0" cellspacing="0" cellpadding="0" style="margin-left:auto;margin-right:auto;">
			<tr>
				<td style="text-align:center;height:200px;vertical-align:middle">
					<form name="passform" method="post" action="<?=$self?>">
					<input type="hidden" name="private" value="1">
					<table class="login_form">
						<tr>
							<td class="right">Utente:</td>
							<td class="left">
								<input type="text" class="input" size="21" name="user" value="<?=$_POST["user"]?>">
							</td>
						</tr>
						<tr>
							<td class="right">Indirizzo email:</td>
							<td class="left">
								<input type="text" class="input" size="30" name="email" value="<?=$_POST["email"]?>">
							</td>
						</tr>
						<tr>
							<td colspan="2" align="center">
								<input type="submit" class="button" name="send" value="Invia">
							</td>
						</tr>
					</table>
					</form>
				</td>
			</tr>
		</table>
		<br>
		<div style="text-align:center">
		Inserire il proprio nome utente e l'indirizzo e-mail associato<br>
		verrà inviata una mail contenente la nuova password all'indirizzo specificato<br>
		<a href="index.php">altrimenti cliccare qui per tornare alla finestra di login</a>
		</div>
	</div>
	</body>
</html>
