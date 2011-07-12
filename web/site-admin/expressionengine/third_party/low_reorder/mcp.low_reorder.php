<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// Get config file
require(PATH_THIRD.'low_reorder/config.php');

/**
* Low Reorder MCP class
*
* @package		low-reorder-ee2_addon
* @author		Lodewijk Schutte ~ Low <low@loweblog.com>
* @link			http://loweblog.com/software/low-reorder/
* @copyright	Copyright (c) 2010, Low
*/
class Low_reorder_mcp {

	/**
	* Helper array for views
	*
	* @var	array
	*/
	var $data = array();

	/**
	* Default settigns
	*
	* @var	array
	*/
	var $default_settings = array(
		'channel_id'       => 0,
		'field_id'         => 0,
		'category_options' => 'all',
		'categories'       => FALSE,
		'statuses'         => FALSE,
		'show_expired'     => 'y',
		'show_future'      => 'y',
		'sort_order'       => 'asc'
	);

	// --------------------------------------------------------------------

	/**
	* PHP4 Constructor
	*
	* @see	__construct()
	*/
	function Low_reorder_mcp()
	{
		$this->__construct();
	}

	// --------------------------------------------------------------------

	/**
	* PHP 5 Constructor
	*
	* @return	void
	*/
	function __construct()
	{
		// Get global instance
		$this->EE =& get_instance();

		// module url homepage
		$this->mod_url = $this->data['mod_url'] = 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.LOW_REORDER_CLASS_NAME;
	}

	// --------------------------------------------------------------------

	/**
	* Home screen for module
	*
	* @return	string
	*/
	function index()
	{
		// Load stuff
		$this->EE->load->library('javascript');
		$this->EE->load->library('table');
		$this->EE->cp->load_package_css('low_reorder');

		// Show message if present, and hide it 2 seconds later
		if ($msg = $this->EE->session->flashdata('reorder_msg'))
		{
			$this->_ee_notice($msg);
		}

		// Set page title
		$this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('low_reorder_module_name'));

		// Initiate fields and channels array
		$fields = $channels = array();

		// --------------------------------------
		//	Get all low_reorder custom fields for this site
		// --------------------------------------

		$this->EE->db->select('field_id, field_label, group_id');
		$this->EE->db->from('channel_fields');
		$this->EE->db->where('site_id', $this->EE->config->item('site_id'));
		$this->EE->db->where('field_type', 'low_reorder');
		$this->EE->db->order_by('field_label', 'asc');
		$query = $this->EE->db->get();

		// Store custom fields and their field groups
		foreach ($query->result_array() AS $row)
		{
			$fields[$row['group_id']][] = $row;
		}

		// --------------------------------------
		//	Get all channels user is assigned to
		//	and contain low_reorder fields
		// --------------------------------------

		if (count($groups = array_keys($fields)))
		{
			// Get channels
			$this->EE->db->select('channel_id, channel_title, field_group');
			$this->EE->db->from('channels');
			$this->EE->db->where_in('channel_id', $this->EE->functions->fetch_assigned_channels());
			$this->EE->db->where_in('field_group', $groups);
			$this->EE->db->order_by('channel_title', 'asc');
			$query = $this->EE->db->get();

			// add field data to channel array
			foreach ($query->result_array() AS $row)
			{
				$row['fields'] = $fields[$row['field_group']];
				$channels[] = $row;
			}
		}
		else
		{
			$channels = array();
		}

		// Store channel data in $this->data for the view
		$this->data['channels'] = $channels;

		// Clean up
		unset($fields, $channels);

		// Compile JS
		$this->EE->javascript->compile();

		// Load view
		return $this->EE->load->view('index', $this->data, TRUE);
	}

	// --------------------------------------------------------------------

	/**
	* Display settings form
	*
	* @return	string
	*/
	function settings()
	{
		// Load more stuff
		$this->EE->load->library('api');
		$this->EE->api->instantiate('channel_categories');
		$this->EE->cp->load_package_css('low_reorder');
		$this->EE->cp->load_package_js('low_reorder');

		// Get channel and field id
		$channel_id = $this->EE->input->get('channel_id');
		$field_id = $this->EE->input->get('field_id');

		// no channel or field id -> invalid request
		if ( ! $channel_id || ! $field_id )
		{
			return $this->show_error('invalid_request');
		}

		// --------------------------------------
		//	Get Channel details
		// --------------------------------------

		$this->EE->db->select('channel_id AS id, channel_name AS short_name, channel_title AS name, cat_group AS category_groups, status_group, field_group');
		$this->EE->db->from('channels');
		$this->EE->db->where_in('channel_id', $this->EE->functions->fetch_assigned_channels());
		$this->EE->db->where('channel_id', $channel_id);
		$query = $this->EE->db->get();
		if ( $query->num_rows() )
		{
			$this->data['channel'] = $query->row_array();	
		}
		else
		{
			return $this->show_error('channel_not_found');
		}

		// --------------------------------------
		//	Get Field details
		// --------------------------------------

		$this->EE->db->select('field_id AS id, field_name AS short_name, field_label AS name');
		$this->EE->db->from('channel_fields');
		$this->EE->db->where('field_id', $field_id);
		$this->EE->db->where('group_id', $this->data['channel']['field_group']);
		$this->EE->db->where('field_type', 'low_reorder');
		$query = $this->EE->db->get();
		if ( $query->num_rows() )
		{
			$this->data['field'] = $query->row_array();
		}
		else
		{
			return $this->show_error('field_not_found');
		}

		// --------------------------------------
		//	Get Low Reorder Settings
		// --------------------------------------

		$settings = $this->_get_settings($channel_id, $field_id);

		// --------------------------------------
		//	Fetch channel statuses
		// --------------------------------------

		$this->EE->db->select('status_id, status, highlight');
		$this->EE->db->from('statuses');
		$this->EE->db->where('group_id', $this->data['channel']['status_group']);
		$this->EE->db->order_by('status_order', 'asc');
		$query = $this->EE->db->get();
		$this->data['all_statuses'] = $query->result_array();

		// --------------------------------------
		//	Fetch channel category groups
		// --------------------------------------

		$this->data['category_groups'] = empty($this->data['channel']['category_groups']) ? FALSE : explode('|', $this->data['channel']['category_groups']);

		if ($this->data['category_groups'])
		{
			// get group details from DB
			$this->EE->db->select('group_id, group_name, sort_order');
			$this->EE->db->from('category_groups');
			$this->EE->db->where_in('group_id', $this->data['category_groups']);
			$this->EE->db->order_by('group_name', 'asc');
			$query = $this->EE->db->get();
			$this->data['category_groups'] = $query->result_array();

			// Loop through groups and get the category group from API
			foreach ($this->data['category_groups'] AS $key => $row)
			{
				$this->EE->api_channel_categories->categories = array();
				$this->EE->api_channel_categories->category_tree($row['group_id'], '', $row['sort_order']);

				$row['categories'] = $this->EE->api_channel_categories->categories;
				$this->data['category_groups'][$key] = $row;
			}

		}

		// --------------------------------------
		//	Create array to help with tag generation
		// --------------------------------------

		$this->data['json'] = array(
			'channel' => $this->data['channel']['short_name'],
			'field' => $this->data['field']['short_name'],
			'statuses' => $this->_flatten_result($this->data['all_statuses'], 'status', 'status_id')
		);

		// --------------------------------------
		//	Add settings to data array
		// --------------------------------------

		$this->data['settings'] = $settings;

		// Set page title
		$this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('edit_settings').': '.$this->data['channel']['name'].', '.$this->data['field']['name']);

		// Set breadcrumb
		$this->EE->cp->set_breadcrumb(BASE.AMP.$this->mod_url, $this->EE->lang->line('low_reorder_module_name'));

		// Set right nav
		$this->EE->cp->set_right_nav(array(
			'reorder_entries' => BASE.AMP.$this->mod_url.AMP.'method=display'.AMP.'channel_id='.$channel_id.AMP.'field_id='.$field_id
		));

		// Return settings form
		return $this->EE->load->view('settings', $this->data, TRUE);
	}

	// --------------------------------------------------------------------

	/**
	* Save the New Settings; redirects to the module home page
	*
	* @return	void
	*/
	function save_settings()
	{
		// Get channel and field ids
		$channel_id = $this->EE->input->post('channel_id');
		$field_id = $this->EE->input->post('field_id');

		// no channel or field id -> invalid request
		if ( ! $channel_id || ! $field_id )
		{
			return $this->show_error('invalid_request');
		}

		// Initiate data to save
		$data = array();

		// Do what with categories?
		if ( in_array($this->EE->input->post('category_options'), array('all', 'one')) )
		{
			$data['categories'] = $this->EE->input->post('category_options');
		}
		else
		{
			$data['categories'] = $this->EE->input->post('categories') ? implode('|', $this->EE->input->post('categories')) : '';
		}

		// Check input
		$data['channel_id']   = $this->EE->input->post('channel_id');
		$data['field_id']     = $this->EE->input->post('field_id');
		$data['statuses']     = $this->EE->input->post('statuses')     ? implode('|', $this->EE->input->post('statuses')) : '';
		$data['show_expired'] = $this->EE->input->post('show_expired') ? 'y' : 'n';
		$data['show_future']  = $this->EE->input->post('show_future')  ? 'y' : 'n';
		$data['sort_order']   = $this->EE->input->post('sort_order');

		// I want to use REPLACE INTO, to replace existing settings and insert if not exists
		// That means no active record!
		$sql = "REPLACE INTO exp_low_reorder_settings (".implode(',', array_keys($data)).") VALUES ('".implode("','", $this->EE->db->escape_str(array_values($data)))."')";
		$this->EE->db->query($sql);

		$this->EE->session->set_flashdata('reorder_msg', $this->EE->lang->line('settings_saved'));
		$this->EE->functions->redirect(BASE.AMP.$this->mod_url);
		exit;
	}

	// --------------------------------------------------------------------

	/**
	* List entries for single channel/field combo
	*
	* @return	string
	*/
	function display()
	{
		// Load some libs/helpers/language files
		$this->EE->load->library('javascript');
		$this->EE->load->library('table');
		$this->EE->load->helper('form');
		$this->EE->cp->load_package_js('low_reorder');
		$this->EE->cp->load_package_css('low_reorder');

		// Show feedback msg?
		if ($msg = $this->EE->session->flashdata('reorder_msg'))
		{
			$this->_ee_notice($msg);
		}

		// Get channel and field id
		$channel_id = $this->EE->input->get('channel_id');
		$field_id = $this->EE->input->get('field_id');

		// no channel or field id -> invalid request
		if ( ! $channel_id || ! $field_id )
		{
			return $this->show_error('invalid_request');
		}

		// --------------------------------------
		//	Fetch channel/field details; return error message if not TRUE
		// --------------------------------------

		if ( ($res = $this->_get_channel_and_field($channel_id, $field_id)) !== TRUE )
		{
			return $res;
		}

		// Initiate page title
		$page_title = $this->data['channel']['name'].', '.$this->data['field']['name'];

		// --------------------------------------
		//	Get settings
		// --------------------------------------

		$settings = $this->_get_settings($channel_id, $field_id);

		// --------------------------------------
		//	Create category selection if necessary
		// --------------------------------------

		$this->data['select_category'] = FALSE;
		$this->data['selected_category'] = 0;

		// Do we have to select a category first?
		if ($settings['category_options'] == 'one')
		{
			// Yes, we need to show the select element
			$this->data['select_category'] = TRUE;

			// Is there a category selected? Override settings accordingly
			if ( ! ($cat_id = $this->EE->input->get('category')) )
			{
				$settings['categories'] = array('-1');
			}
			else
			{
				$settings['categories'] = array($cat_id);
				$this->data['selected_category'] = $cat_id;
			}

			// Load categories API
			$this->EE->load->library('api');
			$this->EE->api->instantiate('channel_categories');

			// Get category group ids from channel
			$this->EE->db->select('cat_group');
			$this->EE->db->from('channels');
			$this->EE->db->where('channel_id', $settings['channel_id']);
			$query = $this->EE->db->get();
			if ( $query->num_rows() )
			{
				$cat_groups = $query->row_array();
				$cat_groups = explode('|', $cat_groups['cat_group']);
			}

			// get group details from DB
			$this->EE->db->select('group_id, group_name, sort_order');
			$this->EE->db->from('category_groups');
			$this->EE->db->where_in('group_id', $cat_groups);
			$this->EE->db->order_by('group_name', 'asc');
			$query = $this->EE->db->get();
			$this->data['category_groups'] = $query->result_array();

			// Loop through groups and get the category group from API
			foreach ($this->data['category_groups'] AS $key => $row)
			{
				$this->EE->api_channel_categories->categories = array();
				$this->EE->api_channel_categories->category_tree($row['group_id'], '', $row['sort_order']);

				$row['categories'] = $this->EE->api_channel_categories->categories;
				$this->data['category_groups'][$key] = $row;
			}

			$this->data['url'] = BASE.AMP.$this->mod_url.AMP.'method=display'.AMP.'channel_id='.$settings['channel_id'].AMP.'field_id='.$settings['field_id'].AMP.'category=';

		}

		// --------------------------------------
		//	Get statuses
		// --------------------------------------

		if ($settings['statuses'])
		{
			$this->EE->db->select('status');
			$this->EE->db->from('statuses');
			$this->EE->db->where_in('status_id', $settings['statuses']);
			$query = $this->EE->db->get();
			foreach ($query->result_array() AS $row)
			{
				$sql_status[] = $row['status'];
			}
		}
		else
		{
			$sql_status = FALSE;
		}

		// --------------------------------------
		//	Get channel entries
		// --------------------------------------

		$this->EE->db->select('DISTINCT(t.entry_id), t.title');
		$this->EE->db->from('exp_channel_titles t');
		$this->EE->db->join('exp_channels c', 't.channel_id = c.channel_id');
		$this->EE->db->join('exp_channel_data d', 't.entry_id = d.entry_id');
		$this->EE->db->where('c.site_id', $this->EE->config->item('site_id'));
		$this->EE->db->where('c.channel_id', $this->EE->input->get('channel_id'));

		// Limit by status
		if ($sql_status)
		{
			$this->EE->db->where_in('t.status', $sql_status);
		}

		// Limit by category
		if ($settings['categories'] !== FALSE)
		{
			$this->EE->db->join('exp_category_posts cp', 't.entry_id = cp.entry_id', 'left');

			$sql_where = "cp.cat_id IN ('".implode("','", $this->EE->db->escape_str($settings['categories']))."')";

			// account for uncategorized entries
			if (in_array('0', $settings['categories']))
			{
				$sql_where = "( ({$sql_where}) OR (cp.cat_id IS NULL) )";
			}

			$this->EE->db->where($sql_where);
		}		

		// Hide expired entries
		if ( $settings['show_expired'] == 'n' )
		{
			$this->EE->db->where("(t.expiration_date = 0 OR t.expiration_date >= {$this->EE->localize->now})");
		}

		// Hide expired entries
		if ( $settings['show_future'] == 'n' )
		{
			$this->EE->db->where('t.entry_date <=', $this->EE->localize->now);
		}

		$this->EE->db->order_by('d.field_id_'.$field_id, $settings['sort_order']);
		$this->EE->db->order_by('t.entry_date', 'desc');
		$query = $this->EE->db->get();

		// --------------------------------------
		//	Add entries to data array for view
		// --------------------------------------

		$this->data['entries'] = $query->result_array();

		// --------------------------------------
		//	Set page title, breadcrumb and right nav, compile JS and load view
		// --------------------------------------

		// Set page title
		$this->EE->cp->set_variable('cp_page_title', $page_title);

		// Set breadcrumb
		$this->EE->cp->set_breadcrumb(BASE.AMP.$this->mod_url, $this->EE->lang->line('low_reorder_module_name'));

		// Set right nav
		$this->EE->cp->set_right_nav(array(
			'edit_settings'	=> BASE.AMP.$this->mod_url.AMP.'method=settings'.AMP.'channel_id='.$channel_id.AMP.'field_id='.$field_id
		));

		$this->EE->javascript->compile();

		return $this->EE->load->view('display', $this->data, TRUE);	

	}

	// --------------------------------------------------------------------

	/**
	* Save the New Order (dundundun)
	*
	* @return	void
	*/
	function save_order()
	{
		$entries = $this->EE->input->post('entries');
		$channel_id = $this->EE->input->post('channel_id');
		$field_id = $this->EE->input->post('field_id');
		$category = $this->EE->input->post('category');
		$field = 'field_id_'.$field_id;

		$settings = $this->_get_settings($channel_id, $field_id);

		// Loop through entries, set new order
		for ($i = 0, $total = count($entries); $i < $total; $i++)
		{
			$entry_id = $entries[$i];

			// Ascend or descend depending on settings
			$new_order = (string) ($settings['sort_order'] == 'asc') ? ($i + 1) : ($total - $i);

			// Add leading zeries
			$new_order = str_pad($new_order, 4, '0', STR_PAD_LEFT);

			// Update entry
			$this->EE->db->where('entry_id', $entry_id);
			$this->EE->db->update('channel_data', array($field => $new_order));
		}

		// define return url
		$url = BASE.AMP.$this->mod_url.AMP.'method=display&amp;channel_id='.$channel_id.'&amp;field_id='.$field_id;

		// Redirect to selected category, if any
		if ($category)
		{
			$url .= AMP.'category='.$category;
		}

		// Clear caching is so desired
		if ($this->EE->input->post('clear_caching') == 'y')
		{
			$this->EE->functions->clear_caching('all', '', TRUE);
		}

		// Set flashdata
		$this->EE->session->set_flashdata('reorder_msg', $this->EE->lang->line('new_order_saved'));

		$this->EE->functions->redirect($url);
		exit;
	}

	// --------------------------------------------------------------------

	/**
	* Show error message in module
	*
	* @param	string
	* @return	string
	*/
	function show_error($msg)
	{
		// Set page title
		$this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('error'));

		// Set breadcrumb
		$this->EE->cp->set_breadcrumb(BASE.AMP.$this->mod_url, $this->EE->lang->line('low_reorder_module_name'));

		$this->data['error_msg'] = $msg;

		return $this->EE->load->view('error', $this->data, TRUE);
	}

	// --------------------------------------------------------------------

	/**
	* Get channel and field details and store them in $this->data
	*
	* @access	private
	* @param	int
	* @param	int
	* @return	mixed	TRUE if successful, string if unsuccessful
	*/
	function _get_channel_and_field($channel_id, $field_id)
	{
		// --------------------------------------
		//	Get Channel details
		// --------------------------------------

		$this->EE->db->select('channel_id AS id, channel_title AS name, cat_group AS category_groups, status_group, field_group');
		$this->EE->db->from('channels');
		$this->EE->db->where('channel_id', $channel_id);
		$query = $this->EE->db->get();
		if ( $query->num_rows() )
		{
			$this->data['channel'] = $query->row_array();	
		}
		else
		{
			return $this->show_error('channel_not_found');
		}

		// --------------------------------------
		//	Get Field details
		// --------------------------------------

		$this->EE->db->select('field_id AS id, field_label AS name, field_instructions AS notes');
		$this->EE->db->from('channel_fields');
		$this->EE->db->where('field_id', $field_id);
		$this->EE->db->where('group_id', $this->data['channel']['field_group']);
		$this->EE->db->where('field_type', 'low_reorder');
		$query = $this->EE->db->get();
		if ( $query->num_rows() )
		{
			$this->data['field'] = $query->row_array();
		}
		else
		{
			return $this->show_error('field_not_found');
		}

		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	* Get settings for channel/field combination
	*
	* @access	private
	* @param	int
	* @param	int
	* @return	array
	*/
	function _get_settings($channel_id, $field_id)
	{
		$query = $this->EE->db->get_where('low_reorder_settings', array(
			'channel_id' => $channel_id,
			'field_id' => $field_id
		));

		// Merge default settings with specific ones
		$settings = array_merge($this->default_settings, $query->row_array());

		// Check category settings
		if ($settings['categories'] !== FALSE)
		{
			if ( in_array($settings['categories'], array('all', 'one')) )
			{
				$settings['category_options'] = $settings['categories'];
				$settings['categories'] = FALSE;
			}
			else
			{
				$settings['category_options'] = 'some';
				$settings['categories'] = (strlen($settings['categories'])) ? explode('|', $settings['categories']) : FALSE;
			}
		}

		// Check status settings
		if ($settings['statuses'] !== FALSE)
		{
			$settings['statuses'] = (strlen($settings['statuses'])) ? explode('|', $settings['statuses']) : FALSE;
		}

		return $settings;
	}

	// --------------------------------------------------------------------

	/**
	* Flatten result set into (associative) array
	*
	* @access	private
	* @param	array
	* @param	string
	* @param	string
	* @return	array
	*/
	function _flatten_result($array, $val, $key = FALSE)
	{
		// init result
		$res = array();

		// Loop through given array
		foreach ($array AS $row)
		{
			if ($key === FALSE)
			{
				$res[] = $row[$val];
			}
			else
			{
				$res[$row[$key]] = $row[$val];
			}
		}

		return $res;
	}

	// --------------------------------------------------------------------

	/**
	* Show EE notification and hide it after a few seconds
	*
	* @access	private
	* @param	string
	* @return	void
	*/
	function _ee_notice($msg)
	{
		$this->EE->javascript->output(array(
			'$.ee_notice("'.$msg.'",{type:"success",open:true});',
			'window.setTimeout(function(){$.ee_notice.destroy()}, 2000);'
		));
	}

	// --------------------------------------------------------------------

}
// End mcp.low_reorder.php