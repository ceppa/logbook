<?
	$dbname="hightecs_logbook";
	$myhost="localhost";
	$myuser="hightecs_envy";
	$mypass="minair";

	function opendb()
	{
		global $dbname,$myhost,$myuser,$mypass;
		$conn=($GLOBALS["___mysqli_ston"] = mysqli_connect($myhost, $myuser, $mypass))
			or die("connecting: ".((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
		((bool)mysqli_query($GLOBALS["___mysqli_ston"], "USE " . $dbname)) 
			or die("selecting db: ".((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
		return $conn;
	}

	function closedb($conn)
	{
		((is_null($___mysqli_res = mysqli_close($conn))) ? false : $___mysqli_res);
	}

	function do_query($query,$conn)
	{
		$result=mysqli_query($GLOBALS["___mysqli_ston"], $query)
			or die("$query<br>".((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
		return $result;
	}

	function result_to_array($result,$useid=true)
	{
		$out=array();
		while($row=mysqli_fetch_assoc($result))
		{
			if(isset($row["id"])&&$useid)
			{
				$id=$row["id"];
				unset($row["id"]);
				$out[$id]=$row;
			}
			else
				$out[]=$row;
		}
		((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);
		return $out;
	}
?>