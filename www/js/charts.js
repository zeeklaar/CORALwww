/*
	This JS runs the chart display page (index.php)
*/

var $dropdowns;

$(document).ready()
{
	setupMenu(); //From menu.js

	setupTitle(); //From title.js

	generateVisuals(); //From generateVisuals.js
}