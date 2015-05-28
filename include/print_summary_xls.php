<?
	$file=file_get_contents("template/summary.xml");
	$i1=strpos($file,"<!--ROW-->");
	$i2=strpos($file,"<!--ENDROW-->",$i1);
	$block=substr($file,$i1,$i2-$i1);
	
	$head=substr($file,0,$i1);
	$tail=substr($file,$i2);

	$out=$head;
	foreach($rows as $row)
	{
		foreach($row as $id=>$item)
			$row[$id]=str_replace("&","&amp;",$item);
		$curblock=$block;
		$curblock=str_replace("{system}",$row["name"],$curblock);
		$curblock=str_replace("{date}",my_date_format($row["date"],"d/m/Y"),$curblock);
		$curblock=str_replace("{from}",$row["from"],$curblock);
		$curblock=str_replace("{to}",$row["to"],$curblock);
		$curblock=str_replace("{time}",$row["durata"],$curblock);
		$curblock=str_replace("{activity}",$row["activity"],$curblock);
		$curblock=str_replace("{activity_type}",$row["activity_type"],$curblock);
		$curblock=str_replace("{pilot}",$row["pilot"],$curblock);
		$curblock=str_replace("{operator}",$row["operator"],$curblock);
		$curblock=str_replace("{instructor}",$row["instructor"],$curblock);
		$curblock=str_replace("{rms}",$row["rms"],$curblock);
		$curblock=str_replace("{fault_description}",$row["fault_description"],$curblock);
		$curblock=str_replace("{fault_solution}",$row["fault_solution"],$curblock);
		$curblock=str_replace("{systems_involved}",$row["systems_involved"],$curblock);
		$curblock=str_replace("{status}",$row["status"],$curblock);
		$curblock=str_replace("{qanda}",$row["qea_risen_number"],$curblock);
		$curblock=str_replace("{note}",$row["note"],$curblock);
		$out.=$curblock;
	}
	$out.=$tail;
	$filename=sprintf("summary.xml");
	header("Content-type: application/octet-stream");
	header("Content-Disposition: attachment; filename=$filename;");
	header("Content-Type: application/ms-excel");
	header("Pragma: no-cache");
	header("Expires: 0");
	echo $out;
?>
