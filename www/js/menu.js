/*
	This javascript handles interation with the menu buttons
*/


/*
	Variables
*/
var baseButtonColor;
var newButtonColor;

var $chartDispButton;
var $listManButton;

var user;

function setupMenu()
{
	setupVariables();

	setupButtonEvents();
}

function setupVariables()
{
	var page = window.location.href;
	user = page.substring(page.indexOf("?user=") + 6);

	$chartDispButton = $('#chartDisplayButton');
	$listManButton = $('#listManagementButton');

	baseButtonColor = $chartDispButton.css('background-color');

	//If the base color is not in a hex format, it's in RGB, so convert it to hex
	if(baseButtonColor[0] != '#')
	{
		baseButtonColor = rgb2hex(baseButtonColor);
	}

	newButtonColor = '#69D8A5';
}

function setupButtonEvents()
{
	$chartDispButton.bind('mouseover', onMouseButton);
	$chartDispButton.bind('mouseout', offMouseButton);
	$chartDispButton.bind('click', chartClick);

	$listManButton.bind('mouseover', onMouseButton);
	$listManButton.bind('mouseout', offMouseButton);	
	$listManButton.bind('click', listClick);	
}

/*
	Event Functions
*/
function onMouseButton()
{
	$(this).css('background-color', newButtonColor);

    $(this).css('transition', 'background-color 1s, margin 1s');
}
function offMouseButton()
{
	$(this).css('background-color', baseButtonColor);
}

function chartClick()
{
	window.location = 'charts.php?user=' + user;
}
function listClick()
{
	window.location = 'lists.php?user=' + user;
}

/*
	Helper Functions
*/

function rgb2hex(rgb) {
    rgb = rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
    function hex(x) {
        return ("0" + parseInt(x).toString(16)).slice(-2);
    }
    return "#" + hex(rgb[1]) + hex(rgb[2]) + hex(rgb[3]);
}