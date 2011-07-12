<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// Get config file
require(PATH_THIRD.'low_reorder/config.php');

/**
* Low Reorder Fieldtype class
*
* @package		low-reorder-ee2_addon
* @author		Lodewijk Schutte ~ Low <low@loweblog.com>
* @link			http://loweblog.com/software/low-reorder/
* @copyright	Copyright (c) 2010, Low
*/
class Low_reorder_ft extends EE_Fieldtype {

	/**
	* Info array
	*
	* @var	array
	*/
	var $info = array(
		'name'		=> LOW_REORDER_NAME,
		'version'	=> LOW_REORDER_VERSION
	);

	// --------------------------------------------------------------------

	/**
	* PHP4 Constructor
	*
	* @see	__construct()
	*/
	function Low_reorder_ft()
	{
		$this->__construct();
	}

	// --------------------------------------------------------------------

	/**
	* PHP5 Constructor
	*
	* @return	void
	*/
	function __construct()
	{
		parent::EE_Fieldtype();
	}

	// --------------------------------------------------------------------

	/**
	* Display field in publish form
	*
	* @param	string	Current value for field
	* @return	string	HTML containing input field
	*/
	function display_field($data)
	{
		// Hide field by triggering hide-button if it's there
		// Or just hide the containing div
		$this->EE->javascript->output("
			/* Low Reorder: Hide reorder field */
			(function(){
				var hideLink = $('#remove_field_{$this->field_id}').get(0);
				if (hideLink) {
					$(hideLink).trigger('click');
				} else {
					$('#hold_field_{$this->field_id}').hide();
				}
			})();
		");

		// Initialize data
		if ($data == '')
		{
			$sql_field_id = $this->EE->db->escape_str($this->field_id);
			$sql_channel_id = $this->EE->db->escape_str($this->EE->input->get('channel_id'));

			$sql = "SELECT MAX(field_id_{$sql_field_id}) AS num FROM exp_channel_data WHERE channel_id = '{$sql_channel_id}'";
			$query = $this->EE->db->query($sql);

			$row = $query->row_array();

			$data = (int) $row['num'];
			$data++;

			$data = str_pad((string) $data, 4, '0', STR_PAD_LEFT);
		}

		// return read-only field
		return '<p><input type="text" id="field_id_'.$this->field_id.'" name="field_id_'.$this->field_id.'" value="'.htmlspecialchars($data).'" readonly="readonly" /></p>';
	}

	// --------------------------------------------------------------------

	/**
	* Display tag in template
	*
	* @param	string	Current value for field
	* @param	array	Tag parameters
	* @param	bool
	* @return	string
	*/
	function replace_tag($data, $params = array(), $tagdata = FALSE)
	{
		// Strips leading zeroes
		$data = intval($data);

		// Add zeroes if padding parameter is given
		if (isset($params['padding']) && is_numeric($params['padding']))
		{
			$padding = (int) $params['padding'] + 1;
			$data = str_pad($data, $padding, '0', STR_PAD_LEFT);
		}

		return $data;
	}

	// --------------------------------------------------------------------

	/**
	* Install field type extras
	*
	* @return	array
	*/
	function install()
	{
		return array();
	}

	// --------------------------------------------------------------------

}
// END Low_reorder_ft class