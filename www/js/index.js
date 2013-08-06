$(document).ready()
{
	setupTitle(); //From title.js

	styleGrid();
}

function styleGrid()
{
	$('#studentTable').flexigrid({
	dataType:'json',
	colModel:[
		{display:'Student ID', name:'id', width:'200', sortable:true},
		{display:'Student Name', name:'name', width:'200', sortable:true},
		{display:'Online', name:'online', width:'200', sortable:true},
		{display:'View Link', name:'viewLink', width:'200', sortable:false},
		],
	height: '500'
	});
}
