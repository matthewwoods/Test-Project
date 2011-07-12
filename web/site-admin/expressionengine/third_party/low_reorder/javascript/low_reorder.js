/**
* Low Reorder custom JS file
*
* @package		low-reorder-ee2_addon
* @author		Lodewijk Schutte ~ Low <low@loweblog.com>
* @link			http://loweblog.com/software/low-reorder/
* @copyright	Copyright (c) 2010, Low
*/

$(function(){

	// Category list slider on settings page
	$('#category_options').change(function(e){
		var method = ($(this).val() == 'some') ? 'slideDown' : 'slideUp';
		$('#reorder-category-container')[method](150);
	});

	// Category select drop down
	$('#reorder-category-select select').change(function(e){
		var url = $('#reorder-category-select input[name=url]').val();
		var val = $(this).val();
		location.href = url+val;
	});

	// Sortable magic
	$('#low-reorder').sortable({
		axis: 'y',
		containment: $('#mainContent'),
		items: 'li',
		opacity: 0.85
	});

	if (typeof LOW != 'undefined') {

		var createEntriesTag = function() {
			// Default parameters
			var params = {
				'channel' : LOW.Reorder.settings.channel,
				'orderby' : LOW.Reorder.settings.field
			};

			// Check categories
			if ($('#category_options').val() == 'some') {
				var cats = [];
				$('input[name^=categories]:checked').each(function(){
					var val = $(this).val();
					if (val == '0') return;
					cats.push(val);
				});
				if (cats.length) {
					params['category'] = cats.join('|');
				}
			}

			// Check statuses
			var statuses = [];
			$('input[name^=statuses]:checked').each(function(){
				statuses.push(LOW.Reorder.settings.statuses[$(this).val()]);
			});
			if (statuses.length) {
				var stat = statuses.join('|');
				if (stat != 'open') params['status'] = stat;
			}

			// Check expired
			if ($('input[name=show_expired]:checked').length) {
				params['show_expired'] = 'yes';
			}

			// Check future
			if ($('input[name=show_future]:checked').length) {
				params['show_future_entries'] = 'yes';
			}

			// Check sort orde
			params['sort'] = $('input[name=sort_order]:checked').val();

			// Build tag
			var tag = '{exp:channel:entries';
			for (var i in params) {
				tag += ' ' + i + '="' + params[i] + '"';
			}
			tag += '}';

			// put tag text in target
			$('#tag-target').text(tag);
		}

		// Create tag on load and re-create on form change
		$('#reorder-settings input, #reorder-settings select').change(createEntriesTag);
		createEntriesTag();

	} // end if (typeof LOW)

});
