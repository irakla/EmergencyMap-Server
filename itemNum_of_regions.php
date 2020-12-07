<?php	header('Content-Type: application/json; charset=utf8');
	$mysql_host = "localhost";
	$mysql_user = "dev_jyt";
	$mysql_password = "123456";

	const DISTINCTION_AED = 100;
	const DISTINCTION_TSUNAMI_SHELTERS = 200;
	const DISTINCTION_MBW_SHELTERS = 210;
	const DISTINCTION_EMERGENCY_ROOM = 300;
	const DISTINCTION_PHARMACY = 400;

//connection to emergency db
	$mysql_db_emergency = "emergency";
	$conn_emergency = mysqli_connect($mysql_host, $mysql_user, $mysql_password, $mysql_db_emergency);

	if(!$conn_emergency)
		die("emergency Database Connection was Failed!".mysqli_connect_error());

//conection to region db
	$mysql_db_region = "region";
	$conn_region = mysqli_connect($mysql_host, $mysql_user, $mysql_password, $mysql_db_region);

	if(!$conn_region)
		die("region Database Connection was Failed!".mysqli_connect_error());

//execute 'select all items' query
	$select_item_fromdb = "SELECT * FROM itemspot";

//execute 'select all sido' query
	$select_sido_fromdb = "SELECT name FROM sido";

//prepare result array
	$result_region_itemnum_array = array();
	$selected_sidos = mysqli_query($conn_region, $select_sido_fromdb);
	if($selected_sidos)
		while($nowSido = mysqli_fetch_assoc($selected_sidos))
			array_push($result_region_itemnum_array, [
				"NameSido" => $nowSido["name"],
				"NumItemsAED" => 0,
				"NumItemsShelters" => 0,
				"NumItemsEmergencyRooms" => 0,
				"NumItemsPharmacies" => 0,
				"CenterLatitude" => 0.0,
				"CenterLongitude" => 0.0
			]);
	else
		echo "failed in result array declaration!";

/*	//Test Output : result array(is normal?)
	foreach($result_region_itemnum_array as $nowRegion)
		echo $nowRegion["NameSido"].",".$nowRegion["NumItems"];
*/
//check All item numbers of regions
	if($selected_sidos && ($selected_items = mysqli_query($conn_emergency, $select_item_fromdb)))
		while($nowItem = mysqli_fetch_assoc($selected_items)){
			$nowLatitude = 0.0;
			$nowLongitude = 0.0;
			foreach($result_region_itemnum_array as $nowIndex => $nowRegion)
				if(strpos($nowItem["BuildAddress"], $nowRegion["NameSido"]) !== false){
					switch($nowItem["Distinction"]){
						case DISTINCTION_AED:
							$result_region_itemnum_array[$nowIndex]["NumItemsAED"]++;
							break;
						case DISTINCTION_TSUNAMI_SHELTERS:
						case DISTINCTION_MBW_SHELTERS:
							$result_region_itemnum_array[$nowIndex]["NumItemsShelters"]++;
							break;
						case DISTINCTION_EMERGENCY_ROOM:
							$result_region_itemnum_array[$nowIndex]["NumItemsEmergencyRooms"]++;
							break;
						case DISTINCTION_PHARMACY:
							$result_region_itemnum_array[$nowIndex]["NumItemsPharmacies"]++;
							break;
					}
					$result_region_itemnum_array[$nowIndex]["CenterLatitude"] += $nowItem["Latitude"];
					$result_region_itemnum_array[$nowIndex]["CenterLongitude"] += $nowItem["Longitude"];
					break;
				}
		}
	else
		echo "failed!";

//calculate centers of items in the regions
	foreach($result_region_itemnum_array as $nowIndex => $nowRegion){
		//exceptional center coordination
		if(strpos($nowRegion["NameSido"], "경기도") !== false){
			$result_region_itemnum_array[$nowIndex]["CenterLatitude"] = 37.275071;
			$result_region_itemnum_array[$nowIndex]["CenterLongitude"] = 127.009395;
			continue;
		}

		$all_item_numbers = 
			$nowRegion["NumItemsAED"] + $nowRegion["NumItemsShelters"] + $nowRegion["NumItemsEmergencyRooms"] + $nowRegion["NumItemsPharmacies"];

		$result_region_itemnum_array[$nowIndex]["CenterLatitude"] /= $all_item_numbers;
		$result_region_itemnum_array[$nowIndex]["CenterLongitude"] /= $all_item_numbers;
	}

//center coordination exception

	echo json_encode($result_region_itemnum_array, JSON_PRETTY_PRINT+JSON_UNESCAPED_UNICODE);
?>
