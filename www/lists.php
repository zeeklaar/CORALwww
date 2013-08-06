<?php

	//Get the user from the url
	$username = $_GET['user'];

	if($username == "")
	{
		die("No user");
	}

	//Gather all the different databases in MySQL for the different lists	

	//User info for database
	$host = "127.0.0.1";
	$user = "CORALUser";
	$pass = "coralpassword";
	$databaseName = "corallists";

	$whiteTable = "whitelist";
	$greenTable = "greenlist";
	$grayTable = "graylist";
	$blackTable = "blacklist";

	//Connect to database (Using MySQL for queries because MySQL requires unavailable native drivers)
	mysql_connect($host, $user, $pass) or
		die("Connection Failed: " .  mysql_error());

	mysql_select_db($databaseName);

	echo mysql_error();

	//Perform the queries
	$whiteResponse = mysql_query("SELECT * FROM $whiteTable" );
	$whiteRows = fetch_all($whiteResponse);

	$greenResponse = mysql_query("SELECT * FROM $greenTable");
	$greenRows = fetch_all($greenResponse);

	$grayResponse = mysql_query("SELECT * FROM $grayTable");
	$grayRows = fetch_all($grayResponse);

	$blackResponse = mysql_query("SELECT * FROM $blackTable");
	$blackRows = fetch_all($blackResponse);

	//Replaces the mysqli_fetch_all function on systems without the MySQL Native Driver (mysqlnd)
    //The MySQLND isn't available on the raspberry pi
    function fetch_all($resource, $resulttype = MYSQLI_NUM)
    {
        for ($res = array(); $tmp = mysql_fetch_array($resource, $resulttype);) $res[] = $tmp;

        return $res;
    }


?>

<html>
	<head>
		<meta content="text/html;charset=utf-8" http-equiv="Content-Type">
		<meta content="utf-8" http-equiv="encoding">

		<!--JQuery 2.0.3 Include-->
		<script type="text/javascript" src="js/jquery-2.0.3.min.js"></script>

		<!--Include Flexgrid for Table Manipulation-->
		<script type="text/javascript" src="flexigrid/js/flexigrid.js"></script>

		<link type="text/css" rel="stylesheet" href="flexigrid/css/flexigrid.css"></link>

		<!--CSS Style Sheets-->
		<link type="text/css" rel="stylesheet" href="css/main.css"></link>
		<link type="text/css" rel="stylesheet" href="css/lists.css"></link>
		<link type="text/css" rel="stylesheet" href="css/nav.css"></link>



		<title>CORAL Display Test</title>
	</head>
	<body>
		<h1>
			<div id="title">
				CORAL Display Test
			</div>

			<?php
				include "nav.php"
			?>
		</h1>

		<div id="Tables">
			<!--Whitelist table-->
			<div class="tableBlock">
				<div class="tableTitle">
					White List - No Restrictions: 
				</div>

				<table id="white" class="table">
					<tbody>
						<?php
							//Use PHP to add SQL entries to the table
							for($i= 0; $i < count($whiteRows); $i++)
							{
								$entry = $whiteRows[$i][0];
								$entry = trim($entry);
								echo "<tr><td>$entry</td></tr>";
							}
						?>
					</tbody>
				</table>
				<button type="button" class="saveButton">Save</button>
			</div>

			<!--Greenlist table-->
			<div class="tableBlock">
				<div class="tableTitle">
					Green List - Domain Restriction:
				</div>
				<table id="green" class="table">
					<tbody>
						<?php
							//Use PHP to add SQL entries to the table
							for($i= 0; $i < count($greenRows); $i++)
							{
								$entry = $greenRows[$i][0];
								$entry = trim($entry);
								echo "<tr><td>$entry</td></tr>";
							}
						?>
					</tbody>
				</table>

				<button type="button" class="saveButton">Save</button>
			</div>

			<!--Graylist table-->
			<div class="tableBlock">
				<div class="tableTitle">
					Gray List - Page Restriction:
				</div>
				<table id="gray" class="table">
					<tbody>
						<?php
							//Use PHP to add SQL entries to the table
							for($i= 0; $i < count($grayRows); $i++)
							{
								$entry = $grayRows[$i][0];
								$entry = trim($entry);
								echo "<tr><td>$entry</td></tr>";
							}
						?>
					</tbody>
				</table>

				<button type="button" class="saveButton">Save</button>
			</div>

			<!--Blacklist table-->
			<div class="tableBlock">
				<div class="tableTitle">
				Black List - Blocked:
				</div>
				<table id="black" class="table">
					<tbody>
						<?php
							//Use PHP to add SQL entries to the table
							for($i= 0; $i < count($blackRows); $i++)
							{
								$entry = $blackRows[$i][0];
								$entry = trim($entry);
								echo "<tr><td>$entry</td></tr>";
							}
						?>
					</tbody>
				</table>

				<button type="button" class="saveButton">Save</button>
			</div>
		</div>

	</body>

	<!--Custom Javascripts-->
	<script type="text/javascript" src="js/title.js"></script>
	<script type="text/javascript" src="js/menu.js"></script>
	<script type="text/javascript" src="js/lists.js"></script>
	
</html>
