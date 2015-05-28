<?
	require_once("include/mysql.php");
	require_once("include/const.php");

	if(!isset($_SESSION["pass"]))
		$_SESSION["pass"]="";
	if(!isset($message))
		$message="";

	function make_key() 
	{
		$random_string = '';
		for($i=0;$i<32;$i++) 
			$random_string .= chr(rand(97,122));
		return $random_string;
	}

	// watching the clock?
	$time = '';
	$time_out = false;
	if($do_time_out == true)
	{
		// I like to work with 1/100th of a sec
		$real_session_time = $session_time * 6000;
		//	timestamp..
		$now = explode(' ',microtime());
		$time = $now[1].substr($now[0],2,2);
		settype($time, "double");

		// time-out (do this before login events)
		if(isset($_SESSION['login_at']))
		{
			if($_SESSION['login_at'] < ($time - $real_session_time))
			{
				$message = 'sessione scaduta!';
				$time_out = true;
			}
		}
	}

	if ((isset($_POST['logout'])) or ($time_out == true)) 
	{
		session_unset();
		session_destroy();
?>
		<script type="text/javascript">
			window.location='index.php';
		</script>
<?
		die();
	}

	// already created a random key for this user?..

	if (isset($_SESSION['key']))
		$random_string = $_SESSION['key'];
	else
		$random_string = $_SESSION['key'] = make_key();

	// check their IP address..
	if ((isset($_SESSION['remote_addr'])) &&
			($_SERVER['REMOTE_ADDR'] == $_SESSION['remote_addr'])) 
		$address_is_good = true;
	else
		$_SESSION['remote_addr'] = $_SERVER['REMOTE_ADDR'];

	// check their user agent..
	if ((isset($_SESSION['agent'])) && 
			($_SERVER['HTTP_USER_AGENT'] == $_SESSION['agent']))
		$agent_is_good = true;
	else
		$_SESSION['agent'] = $_SERVER['HTTP_USER_AGENT'];

	// we simply concatenate the password and random key to create a unique session md5 hash
	// hmac functions are not available on most web servers, but this is near as dammit.

	// admin login
	$expired=false;
	if(isset($_POST['login']))
	{
		$conn=opendb();
		$query="SELECT utenti.*,GROUP_CONCAT(sites.name) AS site_name
				FROM utenti LEFT JOIN sites ON utenti.sites & (1<<(sites.id-1))>0
				WHERE login='".$_POST["user"]."' AND attivo=1
				GROUP BY utenti.id";
		$result=do_query($query,$conn);
		$login=result_to_array($result,false);
		closedb($conn);

		if(count($login))
		{
			$row=$login[0];

			$combined_hash = md5($random_string.$row['pass']);
			// u da man!

			if ($_POST['password'] == $combined_hash)
			{
				if($row['expired']==1)
				{
					$message = 'password scaduta';
					$expired=true;
					$is_logged = false;
					$_SESSION['user_id']=$row['id'];
				}
				else
 				{
					$site_name=($row["livello"]<3?$row["site_name"]:"Admin");
					$_SESSION['login_at'] = $time;
					$_SESSION['session_pass'] = md5($combined_hash);
					$_SESSION['pass']=$row['pass'];
					$_SESSION['user_id']=$row['id'];
					$_SESSION['livello']=$row['livello'];
					$_SESSION['nome']=$row['nome'];
					$_SESSION['cognome']=$row['cognome'];
					$_SESSION['sites']=$row['sites'];
					$_SESSION['site_name']=$site_name;
					$is_logged = true;
					if(!isset($_POST["private"]))
						die();
				}
			}
		 	else
			{
				@$_SESSION['count']++;
				$message = 'password incorretta!';
			}
		}
		else
		{
			$message = 'utente sconosciuto';
			@$_SESSION['count']++;
		}
	}
	elseif(isset($_POST['id']))
	{
		$id=$_POST['id'];
		$conn=opendb();
		$query="UPDATE utenti SET expired=0,pass='".$_POST["newpass"]."' WHERE id='$id'";
		do_query($query,$conn);

		$query="SELECT utenti.*,sites.name AS site_name FROM utenti 
				LEFT JOIN sites ON utenti.sites & (1<<(sites.id-1))>0
				WHERE utenti.id='".$_POST["id"]."'";
		$result=do_query($query,$conn);
		$login=result_to_array($result,false);
		closedb($conn);

		$row=$login[0];
		$combined_hash = md5($random_string.$row['pass']);

		$site_name=($row["livello"]<3?$row["site_name"]:"Admin");
		$_SESSION['login_at'] = $time;
		$_SESSION['session_pass'] = md5($combined_hash);
		$_SESSION['pass']=$row['pass'];
		$_SESSION['user_id']=$row['id'];
		$_SESSION['livello']=$row['livello'];
		$_SESSION['nome']=$row['nome'];
		$_SESSION['cognome']=$row['cognome'];
		$_SESSION['sites']=$row['sites'];
		$_SESSION['site_name']=$site_name;
		$is_logged = true;
	}
	elseif(isset($_POST["showLogin"]))
	{
?>
	<form id="loginform" name="loginform" onsubmit="return false" 
			style="text-align:center;padding:0px">
		<table class="AcqStile4">
			<tr class="middle">
				<td class="AcqStile3" style="text-align:right;padding:0px 0px;">
					user
				</td>
				<td style="text-align:left;padding-left:5px;">
					<input name="user" type="text" id="user" size="20" />
				</td>
			</tr>
			<tr class="middle">
				<td height="50" class="AcqStile3" style="text-align:right;padding:0px;">
					password 
				</td>
				<td style="white-space:nowrap;text-align:left;padding-left:5px;" align="left">
					<input name="password" type="password" id="password" size="12" />
					<input name="login" type="image" 
						id="invia" 
						title="invia" 
						src="immagini/invia.png" 
						alt="invia" 
						onclick="loginClick($('loginform'),$('loginForm'),'<?=$random_string?>')"
						align="top" width="60" />
				</td>
			</tr>
		</table>
		<div align="left" onmouseover="style.cursor='pointer'" 
			style="font-size:10px;color:#666666;"
				onclick="showForgotten($('loginForm'))">
				Hai dimenticato la password?
		</div>
	</form>

<?
		die();
	}
	elseif(isset($_POST["showExpired"]))
	{
?>
	<div id="errorbox" >error</div>
	<form name="loginform" 
			method="post" 
			action="private/index.php"
			onsubmit="return changePasswordClick()"
			style="text-align:center;padding:0px">
		<input type="hidden" name="id" value="<?=$_SESSION["user_id"]?>" />
		<table class="AcqStile4">
			<tr class="middle">
				<td class="AcqStile3" style="text-align:right;padding:0px 0px;">
					nuova password
				</td>
				<td style="text-align:left;padding-left:5px;">
					<input name="password1" type="password" id="password1" size="12" />
				</td>
			</tr>
			<tr class="middle">
				<td height="50" class="AcqStile3" style="text-align:right;padding:0px;">
					ripeti password 
				</td>
				<td style="white-space:nowrap;text-align:left;padding-left:5px;" align="left">
					<input name="password2" type="password" id="password2" size="12" />
				</td>
			</tr>
			<tr>
				<td colspan="2" style="text-align:center">
					<input name="login" type="image" 
						id="invia" 
						title="invia" 
						src="immagini/invia.png" 
						alt="invia" 
						align="top" width="60" />
				</td>
			</tr>
		</table>
	</form>
<?
		die();
	}
	elseif(isset($_POST["showForgotten"]))
	{
?>
		<form id="forgottenform" name="forgottenform" method="post" onsubmit="return false" >
			<table class="AcqStile4">
				<tr class="middle">
					<td class="AcqStile3" style="text-align:right">Utente:</td>
					<td style="text-align:left">
						<input type="text" size="21" name="user">
					</td>
				</tr>
				<tr>
					<td class="AcqStile3" style="text-align:right">Indirizzo email:</td>
					<td style="text-align:left">
						<input type="text" class="input" size="25" name="email">
					</td>
				</tr>
				<tr class="middle">
					<td colspan="2" align="center">
						<input name="send" type="image" 
							title="invia" 
							src="immagini/invia.png" 
							alt="invia" 
							onclick="forgottenClick($('forgottenform'),$('loginForm'))"
							align="top" width="60" />
					</td>
				</tr>
			</table>
		</form>
		<div align="left" onmouseover="style.cursor='pointer'" 
				onclick="showLogin($('loginForm'))">
			<span class="stilea AcqStile14 AcqStile3">
				indietro
			</span>
		</div>

		<script type="text/javascript">
			forgottenform.user.focus();
		</script>
<?
		die();
	}

	// already logged in..
	$combined_hash = md5($random_string.$_SESSION["pass"]);
	if (@$_SESSION['session_pass'] == md5($combined_hash))
	{
		if((($address_is_good == true) and ($agent_is_good == true)))
			$is_logged = true;
		else
			$message = 'chi sei!?!';
	}
/*	if(!isset($_POST["private"]))
		echo $message;*/
?>
