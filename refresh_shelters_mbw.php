﻿<?php header("Content-Type:text/html; charset=utf-8");
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
	const DISTINCTION_SHELTER_MBW = 210;

//delete all tuple on database
	$deleteAllSheltersMBW_fromdb = "DELETE FROM itemspot WHERE distinction = ".DISTINCTION_SHELTER_MBW.";";
	if(mysqli_query($conn, $deleteAllSheltersMBW_fromdb))
		echo "delete all 민방위shelters query is worked.\n<br/>";
	else
		die("delete all 민방위shelters query is failed : ".mysqli_error($conn));

//Initialize insert query
	$insert_todb = "INSERT INTO itemspot (itemNo, distinction, buildaddress, latitude, longitude, detailedplace, managertel) VALUES ";

//Get xml data from data.go.kr
	for($pageNo = 1; $pageNo <= 50; $pageNo++){
		$baseurl = "http://apis.data.go.kr/1741000/CivilDefenseShelter2/getCivilDefenseShelterList?serviceKey=umN1erBYF8rRqS%2FerTeB3QVy%2BkOxHZWHCcXJUlAyD0Kmw5TSP%2FtYsxF94YyrxpW%2FdttFhURE0otNiO4rjphtzg%3D%3D&pageNo=".$pageNo."&numOfRows=1000";

		$rawXmlContents = file_get_contents($baseurl);
		$xmlObject = simplexml_load_string($rawXmlContents);
		$items_shelter = $xmlObject;

//make insert query
		foreach($items_shelter->row as $shelterinfo){
			
			$insert_todb = $insert_todb." (".sprintf("%d, %d, \"%s\", %.11f, %.11f, \"%s\", \"%s\"),\n"
				, $shelterinfo->sn
				, DISTINCTION_SHELTER_MBW
				, str_replace("\"", "\"\"", $shelterinfo->sisul_addr)
				, $shelterinfo->latitude
				, $shelterinfo->longitude
				, str_replace("\'", "\'\'", str_replace("\"", "\"\"", $shelterinfo->facility_name))." "
					."(".$shelterinfo->sisul_scal."미터제곱)"
				, "");			
		}
	}
	$insert_todb = substr($insert_todb, 0, -2).";";

//insert all item parsed from xml data
	if(mysqli_query($conn, $insert_todb))
		echo "input all 민방위shelters query is worked.\n<br/>";
	else
		die("input all 민방위shelters query is failed : ".mysqli_error($conn));

	echo "민방위Shelter work is finished.\n";
?>
