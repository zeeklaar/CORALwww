
/*
	Variables
*/

var $pieChart;

var $siteList;
var $databaseList;

var $categoryDropdown;
var $siteDropdown;

//Site Data Arrays based on color category
var colorsArray;
var redArray;
var greenArray;
var blueArray;
var pinkArray;
var orangeArray;
var dateArray;

//Category colors
var red = '#e00707'; //Game
var green = '#4ac925'; //Instructional
var blue = '#0715cd'; //Assessment
var pink = '#b536da'; //Reference
var orange = '#f2a400'; //No Category


/*
	Helper Functions
*/


function timeToMS(timeString)
{
	var time = timeString;
	var timeLength = time.length; //the length of the string

	//These are all strings
	var ms = time.substring(timeLength - 3);
	var seconds = time.substring(timeLength - 6, timeLength - 4);
	var minutes = time.substring(timeLength - 9, timeLength - 7);
	var hours = time.substring(0, timeLength - 10);

	//Convert strings to ints
	ms = parseInt(ms);
	seconds = parseInt(seconds);
	minutes = parseInt(minutes);
	hours = parseInt(hours);

	//Convert the whole time to milliseconds
	var totalMS = ms + (seconds * 1000) + (minutes * 60 * 1000) + (hours * 60 * 60 * 1000);

	return totalMS;
}

function msToMinutes(ms)
{
	var minutes = ms / 1000 / 60;
	return minutes;
}


function gatherData()
{
	$siteList = $.parseJSON(timeDataString);
	$databaseList = $.parseJSON(categoryDataString);

	//Create arrays by category
	redArray = [0];
	greenArray = [0];
	blueArray = [0];
	pinkArray = [0];
	orangeArray = [0];

	dateArray = new Array(); //This will just store the date values

	var dateIndex = 0;

	//Populate the data array by category
	for(var i =0; i < $siteList.length; i++)
	{
		var $site = $siteList[i];
		
		var $pageList = $site['pages'];


		for(var j = 0; j < $pageList.length; j++)
		{
			$page = $pageList[j];

			$name = $page['title'];
			$time = $page['time'];
			$timeInMinutes = msToMinutes(timeToMS($time));
			$date = $page['date'];

			//Add the date of the page to the array only if it isn't in there already
			if(($.inArray($date, dateArray)) == -1){
				dateArray[dateIndex] = $date;
				dateIndex++;
			}

			var $dataSiteName;
			var $dataSiteColor;

			//compare the name to the categoryDataString, and get its category
			for(var k = 0; k < $databaseList.length; k++)
			{
				$databaseSite = $databaseList[k];

				if($name.indexOf($databaseSite[0]) >= 0){
					$dataSiteName = $databaseSite[0];
					$dataSiteColor = $databaseSite[1];

					break;
				}
			}

		//Then add it to the corresponding array

			//Get corresponding date array index
			var relativeDateIndex = dateArray.indexOf($date);

			if($name.indexOf($dataSiteName) >= 0)
			{

				if($dataSiteColor == 'Red')
				{
					if(redArray[relativeDateIndex] == undefined){redArray[relativeDateIndex] = 0;}

					redArray[relativeDateIndex] += $timeInMinutes;
				}
				else if($dataSiteColor == 'Blue')
				{
					if(blueArray[relativeDateIndex] == undefined){blueArray[relativeDateIndex] = 0;}

					blueArray[relativeDateIndex] += $timeInMinutes;
				}
				else if($dataSiteColor == 'Green')
				{
					if(greenArray[relativeDateIndex] == undefined){greenArray[relativeDateIndex] = 0;}

					greenArray[relativeDateIndex] += $timeInMinutes;
				}
				else if($dataSiteColor == 'Pink')
				{
					if(pinkArray[relativeDateIndex] == undefined){pinkArray[relativeDateIndex] = 0;}

					pinkArray[relativeDateIndex] += $timeInMinutes;
				}
			}
			else
			{
				if(orangeArray[relativeDateIndex] == undefined){orangeArray[relativeDateIndex] = 0;}

				orangeArray[relativeDateIndex] += $timeInMinutes;
			}
		}
	}

	//Initialize the master array
	colorsArray = new Array(redArray, greenArray, blueArray, pinkArray, orangeArray);


	//Need to fix the arrays; find the largest one and push the remainding blank values to the smaller arrays
	var largestSize = 0;
	for(var i = 0 ; i < colorsArray.length; i++)
	{
		if(colorsArray[i].size > largestSize)
		{
			largestSize = colorsArray[i].size;
		}
	}

	for(var i = 0; i < colorsArray.length; i++)
	{
		if(colorsArray[i].size < largestSize)
		{
			var difference = largestSize - colorsArray[i].size;

			for(var j = 0; j < largestSize; j++)
			{
				colorsArray[i].push(0);
			}
		}
	}

}


function renderGraphs()
{
	//Enable jqplot plugins and start rendering the data

	$.jqplot.config.enablePlugins = true;

	$.jqplot('siteChart', [redArray, greenArray, blueArray, pinkArray,orangeArray], {
			seriesColors: [red, green, blue, pink, orange],
			title: 'Site Chart',
            seriesDefaults:{
                renderer:$.jqplot.BarRenderer,
            },
            axes: {
                xaxis: {
                	renderer: $.jqplot.CategoryAxisRenderer,
                	labelRenderer: $.jqplot.CanvasAxisLabelRenderer,
                	label: 'Date',
                    ticks: dateArray,
                },
                yaxis: {
                	labelRenderer: $.jqplot.CanvasAxisLabelRenderer,
                	label: 'Time on Page (minutes)'
                }
            },
            legend: {
                show: true,
                location: 'e',
                placement: 'outside',
                labels: ['Game', 'Instructional', 'Assessment', 'Reference', 'Other']
            } 
    });

}



//On Document Ready
function generateVisuals()
{
	gatherData();

	//Make sure we know which user is in the URL, that way we know which student to 
	//Gather data on
	var page = window.location.href;
	var user = page.substring(page.indexOf("?user=") + 6);

	//Setup the table once
	$('#table').flexigrid({
		url: 'postTableJson.php/?user=' + user + '&categoryFilter=select&siteFilter=select',
		dataType: 'json',
		colModel:[
			{display: "Site Name", name: "siteName", width: 350, sortable: true},
			{display: "Time Spent", name: "timeSpent",sortable: true},
			{display: "Date", name: "date",sortable: true},
			{display: "Category", name: "category",sortable: true},
			{display: "Restriction", name: "restriction",sortable: true}
		],
		sortname: "siteName",
		sortorder: "desc",
		useRP: false,
		showTableToggleBtn: false,
		singleSelect: true,
		height: 500
	});


	//Add events to the selection dropdowns
	$categoryDropdown = $('#categoryDropdown');
	$siteDropdown = $('#siteDropdown');

	$categoryDropdown.change(
		function()
		{
			refreshFlexigrid($(this).val(), $siteDropdown.val());
		}
	);

	$siteDropdown.change(
		function()
		{
			refreshFlexigrid($categoryDropdown.val(), $siteDropdown.val())
		}
	);

	renderGraphs();
}

function refreshFlexigrid(categoryFilter, siteFilter)
{
	console.log(categoryFilter + " - "+ siteFilter);

	$('#table').flexOptions({
		url: 'postTableJson.php/?user=' + user + '&categoryFilter=' + categoryFilter + '&siteFilter=' + siteFilter,
		dataType: 'json',
		colModel:[
			{display: "Site Name", name: "siteName", width: 350, sortable: true},
			{display: "Time Spent", name: "timeSpent",sortable: true},
			{display: "Date", name: "date",sortable: true},
			{display: "Category", name: "category",sortable: true},
			{display: "Restriction", name: "restriction",sortable: true}
		],
		sortname: "siteName",
		sortorder: "desc",
		useRP: false,
		showTableToggleBtn: false,
		singleSelect: true,
		height: 500
	});

	$('#table').flexReload();
}