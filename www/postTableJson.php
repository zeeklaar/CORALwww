<?php
/*
	This script will return a set of json with the 
	data properly filtered
*/

	if(!(isset($_GET["user"]) ))
	{
		echo "Missing User!";
		die();
	}

	$username = $_GET["user"];

	$categoryFilter = $_GET["categoryFilter"];
	$siteFilter = $_GET["siteFilter"];

	/*
		Query the MySQL Databases to get the relevant data on this user
	*/

	//MySQL user / login info
	$host = "localhost";
	$user = "CORALUser";
	$pass = "coralpassword";
	$siteDatabase = "coralsitedatabase";
	$userDatabase = "coralusers";
	$tableName = "sites";

	//Connect to database (Using MySQLi for queries because MySQL is deprectaed in PHP 5.5.0)
	mysql_connect($host, $user, $pass) or
		die("Could not connect: " . mysql_error());

	//select user database to get data to graph
	mysql_select_db($userDatabase);

	//Query for the data
	$userRes = mysql_query("SELECT data FROM users WHERE username='$username'");

	$data = fetch_all($userRes);
	$timeDataString = $data[0][0];
	$timeData = json_decode($timeDataString, true); //Json Array used later

	if(!$userRes)
	{
		die("Unexpected siteResponse from database: " . mysql_error());
	}

	//Once we're done with that database, move onto the site database and get that data
	mysql_select_db($siteDatabase);

	//Query
	$siteRes = mysql_query("SELECT * FROM $tableName");

	if(!$siteRes){
		die("Unexpected siteResponse from database: " . mysql_error());
	}

	$categoryData = fetch_all($siteRes);


	/*
		Actually use gathered data to decided what to compare and return
	*/

	//The array of rows
	$tableRows = array();
	//The array we will end up building
	$tableData;
	//The string we will return
	$tableDataString;

	for($i = 0; $i < sizeof($timeData); $i++)
	{
		$pageArray = $timeData[$i]["pages"]; //Get the array of pages

		for($j = 0; $j < sizeof($pageArray); $j++)
		{
			$page = $pageArray[$j];

			$title = $page["title"];
			$time = $page["time"];
			$date = $page["date"];
			$category = "Other";
			$restriction = "None";

			//Find the page's restriction level and category
			for($k = 0; $k < sizeOf($categoryData); $k++)
			{
				if(strstr($page["title"], $categoryData[$k][0]))
				{
					$category = $categoryData[$k][2];
					$restriction = $categoryData[$k][3];
					break;
				}
			}

			$okay = true; //If this is true by the end of the filters, then it can be added

			//See if this entry makes it past the filters
			if($categoryFilter != "select")
			{
				//If the category is not equal to the filter, then it's no good
				if($categoryFilter != strtolower($category))
				{
					$okay = false;
				}
			}

			if($siteFilter != "select")
			{
				//If the title string doesn't contain the site filter, then it's no good
				if(!strstr($title, $siteFilter))
				{
					$okay = false;
				}
			}

			//If we're good by this point, we can add it to the datastructure
			if($okay)
			{
				$rowData = array(
					0 => $title,
					1 => $time,
					2 => $date,
					3 => $category,
					4 => $restriction
				);

				$row = array(
					"cell" => $rowData
				);
				$tableRows[] = $row;
			}
		}
	}

	//At this point we've collected all the rows
	//Time to wrap it in a little bit more data
	$tableData = array(
		"total" => sizeof($tableRows),
		"page" => 1,
		"rows" => $tableRows
	);

	//Finally encode the data into a string and return it
	$tableDataString = json_encode($tableData);

	echo $tableDataString;

	return $tableDataString;









	/*
		Helper Function
	*/
	//Replaces the mysqli_fetch_all function on systems without the MySQL Native Driver (mysqlnd)
	//The MySQLND isn't available on the raspberry pi
	function fetch_all($siteResponse, $siteResulttype = MYSQLI_NUM)
    {
        for ($siteRes = array(); $tmp = mysql_fetch_array($siteResponse, $siteResulttype);) $siteRes[] = $tmp;

        return $siteRes;
    }
?>