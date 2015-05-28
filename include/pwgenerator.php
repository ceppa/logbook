<?
	function randomPass()
	{
		$out="";
		for($i=0;$i<6;$i++)
			$out.=chr(rand(97,122));
		for($i=0;$i<2;$i++)
			$out.=chr(rand(48,57));
		return $out;
	}
?>
