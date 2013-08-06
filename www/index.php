<?php 
	//User info for database
	$host = "127.0.0.1";
	$user = "CORALUser";
	$pass = "coralpassword";
	$databaseName = "coralusers";

	//Connect to database (Using MySQL for queries because MySQL requires unavailable native drivers)
	mysql_connect($host, $user, $pass) or
		die("Connection Failed: " .  mysql_error());

	mysql_select_db($databaseName);


	$userResponse = mysql_query("SELECT * FROM users" );
	$users = fetch_all($userResponse);

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
		<link type="text/css" rel="stylesheet" href="css/index.css"></link>
		<link type="text/css" rel="stylesheet" href="css/nav.css"></link>

		<title>CORAL Display Test</title>
	</head>
	<body>
		<h1>
			<div id="title">
				CORAL Display Test
			</div>

		</h1>

		<div id="tableBlock">
			<div class="tableTitle">
				Students:
			</div>

			<table id="studentTable">
				<tbody>
					<?php
						//Use PHP to add SQL entries to the table
						for($i= 0; $i < count($users); $i++)
						{
							$id = $users[$i][0];
							$name = $users[$i][1];
							$data = $users[$i][2];

							$id = trim($id);
							$name = trim($name);
							$data = trim($data);

							//TODO: Detect if the user is online
							$online = "false";

							echo "<tr><td>$id</td><td>$name</td><td>$online</td><td><a href='/charts.php?user=$id'>View</a></td></tr>";
						}
					?>
				</tbody>
			</table>
		</div>

	</body>

	<!--Custom Javascripts-->
	<script type="text/javascript" src="js/title.js"></script>
	<script type="text/javascript" src="js/index.js"></script>
	
</html>
