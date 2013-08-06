/*
	This JS runs the list management page (lists.php)
*/

$(document).ready()
{
	setupMenu(); //From menu.js

	setupTitle(); //From title.js

	styleGrid();
}

function styleGrid()
{
	$('.table').flexigrid({
	dataType:'json',
	colModel:[
		{display:'Site Name', name:'siteName', width:'800', sortable:true}
		],
	buttons : [
		{name: 'Add', bclass: 'add', onpress : onAddPress},
		{name: 'Delete', bclass: 'delete', onpress : onDeletePress},
		{separator: true}
		],
	showTableToggleBtn: true
	});

	$('.saveButton').click(saveChanges);
}

/*
	Event Functions
*/

function onAddPress()
{
	var $tableObject;

	//Traverse the HTML to get the table object next to the add button
	var $flexigrid = $(this).parent().parent().parent();
	$tableObject = $flexigrid.children('.bDiv').children('table');

	var newEntry = prompt('New Entry', '');

	if(newEntry == null)
	{
		return;
	}

	newEntry = formatEntry(newEntry);

	$tableObject.append('<tr><td><div>' + newEntry + '</div></td><tr>');
}

function onDeletePress()
{
	var $tableObject;

	//Traverse the HTML to get the table object next to the add button
	var $flexigrid = $(this).parent().parent().parent();
	$tableObject = $flexigrid.children('.bDiv').children('table');

	var toDelete = prompt('Name of Entry to Delete', '');

	//Don't continue if nothing was inputted
	if(toDelete == null)
	{
		return;
	}

	toDelete = formatEntry(toDelete);

	var found = false;

	//Find each div, and if it's html contents equals our toDelete, remove it
	$tableObject.find($('div')).each(function(){
		if($(this).html() == toDelete)
		{
			$(this).parent().parent().remove();
			found = true;
		}
	});

	if(!found)
	{
		alert('Entry not found!');
	}
}

function saveChanges()
{
	whiteData = tableToArray($('#white'));
	greenData = tableToArray($('#green'));
	grayData = tableToArray($('#gray'));
	blackData = tableToArray($('#black'));

	var toPost = [
		{
			'table':'whitelist',
			'data': whiteData
		},
		{
			'table':'greenlist',
			'data': greenData
		},
		{
			'table':'graylist',
			'data': grayData
		},
		{
			'table':'blacklist',
			'data': blackData
		}
	];

	$.ajax({
		type: 'POST',
		url: 'postToDatabase.php',
		data: JSON.stringify(toPost),
		cache: false,
		success: function(){alert('Lists Saved Successfully!');}
	});
}


/*
	Helper Functions
*/

//Check if a string contains a top level domain
function containsTLD(entry)
{
	if(entry.indexOf('.com') != -1 ||
		entry.indexOf('.org') != -1 ||
		entry.indexOf('.edu') != -1 ||
		entry.indexOf('.gov') != -1 ||
		entry.indexOf('.info') != -1 ||
		entry.indexOf('.net') != -1 ||
		entry.indexOf('.co') != -1 ||
		entry.indexOf('.uk') != -1 ||
		entry.indexOf('.tv') != -1 ||
		entry.indexOf('.us') != -1 ||
		entry.indexOf('.me') != -1 ||
		entry.indexOf('.tel') != -1 ||
		entry.indexOf('.ru') != -1 )
	{
		return true;
	}
	else
	{
		return false;
	}
}

//Format a string to be a perfect URL
function formatEntry(entry)
{
	//Add www if it isn't there
	if(entry.indexOf('www.') == -1)
	{
		entry = 'www.' + entry;
	}

	//If the entry doesn't have an http or https at the beginning, add it
	if(entry.indexOf('http://') == -1 && entry.indexOf('https://') == -1)
	{
		entry = 'http://' + entry;
	}

	//If the entry doesn't have a top level domain add .com
	if(!containsTLD(entry))
	{
		entry = entry + '.com';
	}

	//remove whitespace
	entry = entry.replace(/\s+/g, "");

	//make sure it's all lower case
	entry = entry.toLowerCase();

	return entry;
}

//Returns a given table as an array that can be serialized to JSON
function tableToArray(table)
{
	var $table = table;
	var data = new Array();

	//Add each cell to the data array
	$table.find('div').each(function(){
		data.push($(this).html());
	});

	return data;
}