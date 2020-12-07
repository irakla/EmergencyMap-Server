<?php header("Content-Type:text/html; charset=utf-8");
//Initialize database reference instance
	$mysql_host = "localhost";
	$mysql_user = "dev_jyt";
	$mysql_password = "123456";
	$mysql_db = "emergency";

	$conn = mysqli_connect($mysql_host, $mysql_user, $mysql_password, $mysql_db);

//	mysql_query("SET NAMES utf8");

	if(!conn)
		die("connection failed! : ".mysqli_connect_error());

//Initialize distinction number
	const DISTINCTION_PHARMACY = 400;

//delete all tuple on database
	$deleteAllPharmacies_fromdb = "DELETE FROM itemspot WHERE distinction = ".DISTINCTION_PHARMACY.";";
	if(mysqli_query($conn, $deleteAllPharmacies_fromdb))
		echo "delete all pharmacies query is worked.<br/>\n";
	else
		die("delete all pharmacies query is failed : ".mysqli_error($conn));

//Initialize insert query
	$insert_todb = "INSERT INTO itemspot (itemNo, distinction, buildaddress, latitude, longitude, detailedplace, managertel) VALUES ";

//Get xml data from data.go.kr
	for($pageNo = 1; $pageNo <= 40; $pageNo++){
		$baseurl = "http://apis.data.go.kr/B552657/ErmctInsttInfoInqireService/getParmacyFullDown?serviceKey=umN1erBYF8rRqS%2FerTeB3QVy%2BkOxHZWHCcXJUlAyD0Kmw5TSP%2FtYsxF94YyrxpW%2FdttFhURE0otNiO4rjphtzg%3D%3D&pageNo=".$pageNo."&numOfRows=1000";
		
		$rawXmlContents = file_get_contents($baseurl);
		$xmlObject = simplexml_load_string($rawXmlContents);
		$items_pharmacy = $xmlObject->body->items;

//make insert query
		foreach($items_pharmacy->item as $spotinfo){
			
			$insert_todb = $insert_todb." (".sprintf("%d, %d, \"%s\", %.11f, %.11f, \"%s\", \"%s\"),\n"
				, $spotinfo->rnum
				, DISTINCTION_PHARMACY
				, str_replace("\"", "\"\"", $spotinfo->dutyAddr)
				, $spotinfo->wgs84Lat
				, $spotinfo->wgs84Lon
				, str_replace("\'", "\'\'", str_replace("\"", "\"\"", $spotinfo->dutyName))." "
					."(".str_replace("\'", "\'\'", str_replace("\"", "\"\"", $spotinfo->dutyMapimg)).")"
				, $spotinfo->dutyTel1);		
		}
	}
	$insert_todb = substr($insert_todb, 0, -2).";";



//insert all item parsed from xml data
	if(mysqli_query($conn, $insert_todb))
		echo "input all pharmacies query is worked.<br/>\n";
	else
		die("input all pharmacies query is failed : ".mysqli_error($conn));

	echo "Pharmacies work is finished.\n";
?>
