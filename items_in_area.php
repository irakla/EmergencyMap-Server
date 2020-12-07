<?php	header('Content-Type: application/json; charset=utf8');
	$mysql_host = "localhost";
	$mysql_user = "dev_jyt";
	$mysql_password = "123456";
	$mysql_db = "emergency";

	$conn = mysqli_connect($mysql_host, $mysql_user, $mysql_password, $mysql_db);

	if(!$conn)
		die("Database Connection was Failed!".mysqli_connect_error());

	$boundary_east = $_GET['east'];
	$boundary_west = $_GET['west'];
	$boundary_south = $_GET['south'];
	$boundary_north = $_GET['north'];

	$select_fromdb = "SELECT * FROM itemspot WHERE (latitude BETWEEN ".$boundary_south." AND ".$boundary_north.")"
		." AND (longitude BETWEEN ".$boundary_west." AND ".$boundary_east.")";

	$result_items_array = array();
	if($result_items = mysqli_query($conn, $select_fromdb))
		while($row = $result_items->fetch_assoc())
			$result_items_array[] = $row;
	else
		echo "failed!";

	echo json_encode($result_items_array, JSON_PRETTY_PRINT+JSON_UNESCAPED_UNICODE);
?>
