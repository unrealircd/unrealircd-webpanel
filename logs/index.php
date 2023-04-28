<?php
require_once "../inc/common.php";
require_once "../inc/header.php";
?>
<h4>Log viewer</h4>

	<!-- The log table -->
	<form method="post">
	<table id="data_list" class="table-striped display nowrap" style="width:100%">
	<thead class="table-primary">
		<th scope="col">Time</th>
		<th scope="col">Level</th>
		<th scope="col">Subsystem</th>
		<th scope="col">Event</th>
		<th scope="col">Message</th>
	</thead>
	</table>
	</form>

<script>
let data_list_table = null;

function level2color(level)
{
	if (level == 'info')
		return 'green';
	if (level == 'warn')
		return 'orange';
	if ((level == 'error') || (level == 'fatal'))
		return 'red';
}

function log_colorizer(data, type, row)
{
	if (type == 'display')
	{
		var color = level2color(row['Level']);
		data = '<span style="color: '+color+'">' + data + '</span>';
	}
	return data;
}

function log_timestamp(data, type, row)
{
	if (type == 'display')
	{
		return moment.utc(data).local().format('HH:mm:ss');
	}
	return data;
}

function resize_check()
{
	if (window.innerWidth < 900)
	{
		data_list_table.column(1).visible(false); // level
		data_list_table.column(2).visible(false); // subsystem
		data_list_table.column(3).visible(false); // event
	} else 
	if (window.innerWidth < 1250)
	{
		data_list_table.column(1).visible(true);  // level
		data_list_table.column(2).visible(false); // subsystem
		data_list_table.column(3).visible(false); // event
	} else 
	if (window.innerWidth < 1450)
	{
		data_list_table.column(1).visible(true);  // level
		data_list_table.column(2).visible(true);  // subsystem
		data_list_table.column(3).visible(false); // event
	} else
	{
		data_list_table.column(1).visible(true);  // level
		data_list_table.column(2).visible(true);  // subsystem
		data_list_table.column(3).visible(true); // event
	}
	data_list_table.rows().invalidate('data').draw(false);
}

function log_text(data, type, row)
{
	var esc = function (t) {
	    return ('' + t)
		.replace(/&/g, '&amp;')
		.replace(/</g, '&lt;')
		.replace(/>/g, '&gt;')
		.replace(/"/g, '&quot;');
	};

	if (type != 'display')
		return data;

	var color = level2color(row['Level']);
	var cutoff;
	if (window.innerWidth < 500)
		cutoff = 35;
	else if (window.innerWidth < 1000)
		cutoff = 75;
	else if (window.innerWidth < 1750)
		cutoff = 100
	else
		cutoff = 125;

	if (data.length > cutoff)
	{
		// stolen from ellipsis
		var shortened = data.substr(0, cutoff - 1);
		data = '<span class="ellipsis" style="color: '+color+'" title="' +
		    esc(data) +
		    '">' +
		    shortened +
		    '&#8230;</span>';
	} else {
		// otherwise just like log_colorizer...
		data = '<span style="color: '+color+'">' + data + '</span>';
	}
	return data;
}

$(document).ready( function () {
	args = {
		//'responsive': true,
		'fixedHeader': {
			header: true,
			headerOffset: 53
		},
		'columns': [
			{ 'data': 'Time', 'responsivePriority': 1, 'render': log_timestamp },
			{ 'data': 'Level', 'responsivePriority': 3, 'className':'virtuallink', 'render': log_colorizer },
			{ 'data': 'Subsystem', 'responsivePriority': 4, 'render': log_colorizer },
			{ 'data': 'Event', 'responsivePriority': 5, 'render': log_colorizer },
			//{ 'data': 'Message', 'responsivePriority': 2, 'render': DataTable.render.ellipsis(100, false) },
			{ 'data': 'Message', 'responsivePriority': 2, 'render': log_text },
		],
		'pageLength':100,
		'order':[[0,'desc']],
	};

	/* Only show filter pane on desktop */
	if (window.innerWidth > 8000)
	{
		args['dom'] = 'Pfrtip';
		args['searchPanes'] = {
			'initCollapsed': 'true',
			'columns': [1,2,3],
			'dtOpts': {
				select: { style: 'multi'},
				order: [[ 1, "desc" ]]
			},
		}
	}

	data_list_table = $('#data_list').DataTable(args);

	resize_check();
	window.addEventListener('resize', resize_check);

	StartLogStream('<?php echo get_config('base_url'); ?>api/log.php');
} );
</script>

<?php require_once '../inc/footer.php'; ?>
