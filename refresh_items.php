<?php header("Content-Type:text/html; charset=utf-8");
//Initialize database reference instance
	$mysql_host = "localhost";
	$mysql_user = //비공개;
	$mysql_password = //비공개;
	$mysql_db = "emergency";

	$conn = mysqli_connect($mysql_host, $mysql_user, $mysql_password, $mysql_db);

//	mysql_query("SET NAMES utf8");

	if(!conn)
		die("connection failed! : ".mysqli_connect_error());

//Initialize distinction number
	const DISTINCTION_AED = 100;

//delete all tuple on database
	$deleteAllAED_fromdb = "DELETE FROM itemspot WHERE distinction = ".DISTINCTION_AED.";";
	if(mysqli_query($conn, $deleteAllAED_fromdb))
		echo "delete all AED query is worked.<br/>\n";
	else
		die("delete all AED query is failed : ".mysqli_error($conn));

//Initialize insert query
	$insert_todb = "INSERT INTO itemspot (itemNo, distinction, buildaddress, latitude, longitude, detailedplace, managertel) VALUES ";

//Get xml data from data.go.kr
	for($pageNo = 1; $pageNo <= 50; $pageNo++){
		$baseurl = "http://apis.data.go.kr/B552657/AEDInfoInqireService/getAedFullDown?serviceKey=umN1erBYF8rRqS%2FerTeB3QVy%2BkOxHZWHCcXJUlAyD0Kmw5TSP%2FtYsxF94YyrxpW%2FdttFhURE0otNiO4rjphtzg%3D%3D&pageNo=".$pageNo."&numOfRows=1000";

		$rawXmlContents = file_get_contents($baseurl);
		$xmlObject = simplexml_load_string($rawXmlContents);
		$items_aed = $xmlObject->body->items;

//make insert query
		foreach($items_aed->item as $aedinfo){
			/*echo $aedinfo->buildAddress;*/
			$insert_todb = $insert_todb." (".sprintf("%d, %d, \"%s\", %.11f, %.11f, \"%s\", \"%s\"),\n"
				, $aedinfo->rnum
				, DISTINCTION_AED
				, str_replace("\"", "\"\"", $aedinfo->buildAddress)
				, $aedinfo->wgs84Lat
				, $aedinfo->wgs84Lon
				, str_replace("\'", "\'\'", str_replace("\"", "\"\"", $aedinfo->buildPlace))
				, $aedinfo->managerTel);
		}
	}
	$insert_todb = substr($insert_todb, 0, -2).";";

//insert all item parsed from xml data
	if(mysqli_query($conn, $insert_todb))
		echo "input all AED query is worked.<br/>\n";
	else
		die("input all AED query is failed : ".mysqli_error($conn));

	echo "AED work is finished.\n";
?>
