<?php
	file_put_contents('C:/Users/Arsen/Desktop/test.txt', ""); //Clear log

	$data = file_get_contents('php://input'); //Gets all post data as a JSON object
	$json = json_decode($data, true); //Decode the JSON string as an array

	//Connect to the mysql server
	$host = "localhost";
	$user = "CORALUser";
	$pass = "coralpassword";
	$databaseName = "corallists";

	//Connect to database (Using MySQLi for queries because MySQL is deprectaed in PHP 5.5.0)
	$mysqli = mysqli_connect($host, $user, $pass, $databaseName);

	//Handle connection errors
	if(mysqli_connect_errno())
	{
		printf("Connection Failed: %s\n", mysqli_connect_error());
		exit();
	}

	for($i = 0; $i < sizeof($json); $i++){
		$obj = $json[$i];
		$table = $obj["table"];
		$contents = $obj["data"];

		//Clear each table
		if(!mysqli_query($mysqli, "TRUNCATE TABLE $table"))
		{
			file_put_contents("/var/www/log/postlog.txt", "Error ". mysqli_error($mysqli), FILE_APPEND);
		}

		for($j = 0; $j < sizeof($contents); $j++){

			if(!mysqli_query($mysqli, "INSERT INTO $table (WebsiteURL) VALUES(\"$contents[$j]\")"))
			{
				file_put_contents("/var/www/log/postlog.txt", "Error ". mysqli_error($mysqli), FILE_APPEND);
			}else
			{
				file_put_contents("/var/www/log/postlog.txt", $table . " : " . $contents[$j] . "\n", FILE_APPEND);
			}
		}
	}
?>
