<?php
require_once "../inc/common.php";
require_once "../inc/header.php";
?>
<h4>Log viewer</h4>
<p>This fetches up to 1000 historical log entries from UnrealIRCd (requires 6.1.1-git+) and then follows the logs 'live'.</p>
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

<!-- View log entry -->
	<div class="modal" id="view_log_entry" tabindex="-1" role="dialog" aria-labelledby="confirmModalCenterTitle" aria-hidden="true">
	<div class="modal-dialog modal-xl" role="document">
		<form method="post">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="view_log_entry_title">View log entry</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span></button>		
				</div>
				<div class="modal-body">
					<ul class="nav nav-tabs" role="tablist">
						<li class="nav-item" role="presentation"><a class="nav-link active" href="#event_pane" aria-controls="event_pane" role="tab" data-toggle="tab">Log entry</a></li>
						<li class="nav-item" role="presentation"><a class="nav-link" href="#json_pane" aria-controls="json_pane" role="tab" data-toggle="tab">JSON</a></li>
					</ul>
					
					<div class="tab-content">
						<div class="tab-pane show active" id="event_pane">
							<table class="table-sm table-responsive caption-top table-hover">
								<tbody>
									<tr><td>Time</td><td id="view_log_entry_time"></td></tr>
									<tr><td>Level</td><td id="view_log_entry_level"></td></tr>
									<tr><td>Subsystem</td><td id="view_log_entry_subsystem"></td></tr>
									<tr><td>Event</td><td id="view_log_entry_event"></td></tr>
									<tr><td>Message</td><td id="view_log_entry_message" class="tdwrap"></td></tr>
								</tbody>
							</table>
						</div>
						<div class="tab-pane" id="json_pane">
							<p class="card-text tdwrap" id="view_log_entry_json"></p>
						</div>
					</div>
				</div>
								
				<div class="modal-footer">
				<!-- do we want a button at all? -->
				</div>
			</div>
		</form>
	</div>
	</div>


<script src="../js/json-formatter.umd.js"></script>
<script>
/* JSONFormatter turns every string that starts with "http" into a link.
 * Log entries contain text from IRC users (eg a realname), so never do that.
 */
Object.defineProperty(JSONFormatter.prototype, 'isUrl', { get: function () { return false; }, configurable: true });

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

/* All log data can contain text from ordinary IRC users (quit reasons,
 * realnames, failed OPER names, ...), so every render function below
 * escapes its output, also for the 'filter' type (DataTables uses
 * innerHTML on filter data that contains a '&').
 */
function log_colorizer(data, type, row)
{
	if (type == 'display')
	{
		var color = level2color(row['Level']);
		return '<span style="color: '+color+'">' + escapeHTML(data) + '</span>';
	}
	if (type == 'filter')
		return escapeHTML(data);
	return data;
}

function log_timestamp(data, type, row)
{
	if (type == 'display')
	{
		return escapeHTML(moment.utc(data).local().format('HH:mm:ss'));
	}
	if (type == 'filter')
		return escapeHTML(data);
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
	var esc = escapeHTML;

	if (type == 'filter')
		return esc(data);
	if (type != 'display')
		return data;
	data = (data === null || data === undefined) ? '' : ('' + data);

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
		/* cut first, then escape (cutting escaped text could break an entity) */
		data = '<span class="ellipsis" style="color: '+color+'" title="' +
		    esc(data) +
		    '">' +
		    esc(shortened) +
		    '&#8230;</span>';
	} else {
		// otherwise just like log_colorizer...
		data = '<span style="color: '+color+'">' + esc(data) + '</span>';
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
			{ 'data': 'Time', 'responsivePriority': 1, 'render': log_timestamp, 'className':'virtuallink' },
			{ 'data': 'Level', 'responsivePriority': 3, 'render': log_colorizer },
			{ 'data': 'Subsystem', 'responsivePriority': 4, 'render': log_colorizer },
			{ 'data': 'Event', 'responsivePriority': 5, 'render': log_colorizer },
			//{ 'data': 'Message', 'responsivePriority': 2, 'render': DataTable.render.ellipsis(100, false) },
			{ 'data': 'Message', 'responsivePriority': 2, 'render': log_text },
			{ 'data': 'Raw', 'visible': false, 'searchable': true, 'render': DataTable.render.text() },
		],
		'pageLength':100,
		'order':[[0,'desc']],
		'language':{
			searchPlaceholder: "Nick, IP, anything...",
		}
	};

	/* Only show filter pane on desktop */
	if (window.innerWidth > 800)
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

	StartLogStream(BASE_URL + 'api/log.php');

	$('#data_list').on( 'click', 'td', function () {
		view_log_entry(this);
	} );
} );

function view_log_entry(e)
{
	var data = data_list_table.row(e).data();
	/* Build the elements and set the values as text, never as HTML */
	$('#view_log_entry_time').empty().append($('<code>').text(data['Time']));
	$('#view_log_entry_level').empty().append($('<code>').text(data['Level']));
	$('#view_log_entry_subsystem').empty().append($('<code>').text(data['Subsystem']));
	$('#view_log_entry_event').empty().append($('<code>').text(data['Event']));
	$('#view_log_entry_message').empty().append($('<pre class="tdwrap">').text(data['Message']));
	var j = JSON.parse(data['Raw']);
	j = new JSONFormatter(j, 99);
	$('#view_log_entry_json').empty().append(j.render());
	$('#view_log_entry').modal('show');
}
</script>

<?php require_once '../inc/footer.php'; ?>
