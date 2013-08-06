/*
	Quick javascript to keep the clickable title outside of the menu.js
*/

/*
	Variables
*/
var $headerTitle;

var baseTitleColor;
var newTitleColor;

function setupTitle()
{
	//Setup for the title
	$headerTitle = $('#title');

	baseTitleColor = $headerTitle.css('color');

	if(baseTitleColor[0] != '#')
	{
		baseTitleColor = rgb2hex(baseTitleColor);
	}
	newTitleColor = '#127347';

	$headerTitle.bind('mouseover', onMouseTitle);
	$headerTitle.bind('mouseout', offMouseTitle);
	$headerTitle.bind('click', titleClick);
}

function onMouseTitle()
{
	$(this).css('color', newTitleColor);

    $(this).css('transition', 'color 1s, margin 1s');
}
function offMouseTitle()
{
	$(this).css('color', baseTitleColor);
}

function titleClick()
{
	window.location = 'index.php';
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