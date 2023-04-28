<?php
require_once "../inc/common.php";
require_once "../inc/header.php";

if (!empty($_POST))
{
    do_log($_POST);

    /* Nothing being posted yet */

}

?>
<h4>Channels Overview</h4><br>

<!-- The channel list -->
<table id="data_list" class="table-striped display responsive nowrap" style="width:100%">
<thead class="table-primary">
	<th scope="col">Name</th>
	<th scope="col">Users</th>
	<th scope="col">Modes</th>
	<th scope="col">Topic</th>
	<th scope="col">Created</th>
</thead>
</table>

<script>
let data_list_table = null;

$(document).ready( function () {
	args = {
		'responsive': true,
		'fixedHeader': {
			header: true,
			headerOffset: 53
		},
		'ajax': {
			'url': '<?php echo get_config("base_url"); ?>api/channels.php',
			dataSrc: ''
		},
		'pageLength':100,
		'order':[[1,'desc']],
		'columns': [
			{ 'data': 'Name', 'responsivePriority': 1, 'className':'virtuallink' },
			{ 'data': 'Users', 'responsivePriority': 2 },
			{ 'data': 'Modes', 'responsivePriority': 3 },
			{ 'data': 'Topic', 'responsivePriority': 5, 'className':'tdwrap' },
			{ 'data': 'Created', 'responsivePriority': 4 },
		],
	};
	/* Hide on mobile */
	if (window.innerWidth > 8000)
	{
		args['dom'] = 'Pfrtip';
		args['searchPanes'] = {
			'initCollapsed': 'true',
			'columns': [1,3],
			'dtOpts': {
				select: { style: 'multi'},
				order: [[ 1, "desc" ]]
			},
		}
	}

	data_list_table = $('#data_list').DataTable(args);

	$('#data_list').on( 'click', 'td', function () {
		show_channel(this);
	} );
} );

function show_channel(e)
{
	/* The first column is the 'Select' column */
	// not on this page, or not yet ;)
	//if (data_list_table.cell(e).index().column == 0)
	//	return;

	/* For all the other columns we show the view screen */
	var data = data_list_table.row(e).data();
	channel = data['Name'];
	window.location = '<?php echo get_config('base_url'); ?>/channels/details.php?chan=' +
	                  encodeURIComponent(channel);
	// not working: still expands on mobile: e.stopImmediatePropagation();
	return true;
}

</script>

<?php require_once UPATH.'/inc/footer.php'; ?>
