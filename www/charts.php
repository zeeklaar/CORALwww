<?php 

	//Get the user from the url
	$username = $_GET['user'];

	if($username == "")
	{
		die("No user");
	}

	//Fetch data from the MySQL Database
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

	//Returns a string of the category data
	$categoryDataString = json_encode($categoryData);


	//Replaces the mysqli_fetch_all function on systems without the MySQL Native Driver (mysqlnd)
	//The MySQLND isn't available on the raspberry pi
	function fetch_all($siteResponse, $siteResulttype = MYSQLI_NUM)
    {
        for ($siteRes = array(); $tmp = mysql_fetch_array($siteResponse, $siteResulttype);) $siteRes[] = $tmp;

        return $siteRes;
    }
?>

<html>
	<head>

		<meta content="text/html;charset=utf-8" http-equiv="Content-Type">
		<meta content="utf-8" http-equiv="encoding">

		<!--JQuery 2.0.3 Include-->
		<script type="text/javascript" src="js/jquery-2.0.3.min.js"></script>

		<!--Include JQPlot for graphing-->
		<script type="text/javascript" src="jqplot/jquery.jqplot.min.js"></script>
		<script type="text/javascript" src="jqplot/plugins/jqplot.barRenderer.min.js"></script>
		<script type="text/javascript" src="jqplot/plugins/jqplot.categoryAxisRenderer.min.js"></script>
		<script type="text/javascript" src="jqplot/plugins/jqplot.canvasTextRenderer.min.js"></script>
		<script type="text/javascript" src="jqplot/plugins/jqplot.canvasAxisLabelRenderer.min.js"></script>
		<script type="text/javascript" src="jqplot/plugins/jqplot.logAxisRenderer.min.js"></script>
		<script type="text/javascript" src="jqplot/plugins/jqplot.pointLabels.min.js"></script>

		<link type="text/css" rel="stylesheet" href="jqplot/jquery.jqplot.min.css"></link>

		<!--Include Flexgrid for Table Manipulation-->
		<script type="text/javascript" src="flexigrid/js/flexigrid.js"></script>

		<link type="text/css" rel="stylesheet" href="flexigrid/css/flexigrid.css"></link>

		<!--Include JQuery.Dropdown for dropdowns-->
		<script type="text/javascript" src="dropdown/jquery.dropdown.js"></script>

		<link type="text/css" rel="stylesheet" href="dropdown/jquery.dropdown.css"></link>

		<!--CSS Style Sheets-->
		<link type="text/css" rel="stylesheet" href="css/main.css"></link>
		<link type="text/css" rel="stylesheet" href="css/charts.css"></link>
		<link type="text/css" rel="stylesheet" href="css/nav.css"></link>

		<!--This script will store the json from PHP and then pass it to the main javascript-->
		<script type="text/javascript">
			var user = '<?php echo $username; ?>'  //Pass this so that other sections will know what user to look at
			var timeDataString = '<?php echo $timeDataString; ?>';
			var categoryDataString = '<?php echo $categoryDataString; ?>';
		</script>

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

		<!--Data Display-->

		<div id="chartDisplay">
			<div id="siteChart" class="chart">
			
			</div>


			<div id="breakdown" class="chart">
				<div id="chartTitle">Detailed View</div>

				<div id="filters">
					<div id="filterTitle">Filters:</div>
					<div class="selectionDropdown">
						<select id="categoryDropdown">
							<option value="select">Select Category</option>
							<option value="game">Game</option>
							<option value="instructional">Instructional</option>
							<option value="assessment">Assessment</option>
							<option value="reference">Reference</option>
							<option value="other">Other</option>
						</select>							
					</div>
					<div class="selectionDropdown">
						<select id="siteDropdown">
							<option value="select">Select Site</option>
							<!--Use PHP to generate options for visited sites-->
							<?php
								for($i = 0; $i < sizeOf($timeData); $i++)
								{
									$option = $timeData[$i]["title"];
									$value = substr($option, 0, sizeof($option) - 4);

									echo "<option value=\"$value\">$option</option>";
								}
							?>
						</select>	
					</div>
				</div>

				<table id="table">
					<thead>
						<tr>
						</tr>
					</thead>
					<tbody>

						<!--Body will be auto-->

					</tbody>
				</table>
			</div>
		</div>

		<!--Dropdown data-->
		<div id="categoryDropdown" class="dropdown dropdown-tip">
		    <ul class="dropdown-menu">
				<li><a href="#">None</a></li>
				<li><a href="#">Game</a></li>
				<li><a href="#">Instructional</a></li>
				<li><a href="#">Assessment</a></li>
				<li><a href="#">Reference</a></li>
				<li><a href="#">Other</a></li>
			</ul>
		</div>

		<div id="siteDropdown" class="dropdown dropdown-tip">
		    <ul class="dropdown-menu">
				<li><a href="#">None</a></li>
				
				<!--Use PHP to generate links to the visited sites-->
				<?php
					$timeData = json_decode($timeDataString, true);

					for($i = 0; $i < sizeOf($timeData); $i++)
					{
						echo "<li><a href=\"#\">" . $timeData[$i]["title"] . "</a></li>";
					}
				?>
			</ul>
		</div>

	</body>

	<!--Custom Javascripts-->
	<script type="text/javascript" src="js/title.js"></script>
	<script type="text/javascript" src="js/menu.js"></script>
	<script type="text/javascript" src="js/generateVisuals.js"></script>
	<script type="text/javascript" src="js/charts.js"></script>
	
</html>
