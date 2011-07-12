<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// Get config file
require(PATH_THIRD.'low_reorder/config.php');

/**
* Low Reorder UPD class
*
* @package		low-reorder-ee2_addon
* @author		Lodewijk Schutte ~ Low <low@loweblog.com>
* @link			http://loweblog.com/software/low-reorder/
* @copyright	Copyright (c) 2010, Low
*/
class Low_reorder_upd {

	/**
	* Version number
	*
	* @var	string
	*/
	var $version = LOW_REORDER_VERSION;

	// --------------------------------------------------------------------

	/**
	* PHP4 Constructor
	*
	* @see	__construct()
	*/
	function Low_reorder_upd()
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
		/** -------------------------------------
		/**  Get global instance
		/** -------------------------------------*/

		$this->EE =& get_instance();
	}

	// --------------------------------------------------------------------

	/**
	* Uninstall the module
	*
	* @return	bool
	*/
	function install()
	{
		// Load forge
		$this->EE->load->dbforge();

		// Insert row into modules table
		$this->EE->db->insert('modules', array(
			'module_name'		=> LOW_REORDER_CLASS_NAME,
			'module_version'	=> LOW_REORDER_VERSION,
			'has_cp_backend'	=> 'y'
		));

		// Create new table for settings
		$fields = array(
			'channel_id'	=> array('type'	=> 'int', 'constraint'	=> '6', 'unsigned' => TRUE, 'null' => FALSE),
			'field_id'		=> array('type'	=> 'int', 'constraint'	=> '6', 'unsigned' => TRUE, 'null' => FALSE),
			'statuses'		=> array('type' => 'varchar', 'constraint' => '255'),
			'categories'	=> array('type' => 'varchar', 'constraint' => '255'),
			'show_expired'	=> array('type' => 'char', 'constraint' => '1', 'null' => FALSE, 'default' => 'y'),
			'show_future'	=> array('type' => 'char', 'constraint' => '1', 'null' => FALSE, 'default' => 'y'),
			'sort_order'	=> array('type' => 'enum', 'constraint' => "'asc','desc'", 'null' => FALSE, 'default' => 'asc')
		);

		$this->EE->dbforge->add_field($fields);
		$this->EE->dbforge->add_key(array('channel_id', 'field_id'), TRUE);

		$this->EE->dbforge->create_table('low_reorder_settings');

		unset($fields);

		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	* Uninstall the module
	*
	* @return	bool
	*/
	function uninstall()
	{
		// Load forge
		$this->EE->load->dbforge();

		// get module id
		$this->EE->db->select('module_id');
		$this->EE->db->from('modules');
		$this->EE->db->where('module_name', LOW_REORDER_CLASS_NAME);
		$query = $this->EE->db->get();

		// remove references from module_member_groups
		$this->EE->db->where('module_id', $query->row('module_id'));
		$this->EE->db->delete('module_member_groups');

		// remove references from modules
		$this->EE->db->where('module_name', LOW_REORDER_CLASS_NAME);
		$this->EE->db->delete('modules');

		// Drop table
		$this->EE->dbforge->drop_table('low_reorder_settings');

		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	* Update the module
	*
	* @return	bool
	*/
	function update($current = '')
	{
		/** -------------------------------------
		/**  Same version? A-okay, daddy-o!
		/** -------------------------------------*/

		if ($current == '' OR version_compare($current, LOW_REORDER_VERSION) === 0)
		{
			return FALSE;
		}
		
		// Load forge
		$this->EE->load->dbforge();
		
		// Update to 1.0.5
		if (version_compare($current, '1.0.5', '<'))
		{
			// Adds sorting order as setting
			$this->EE->db->query("ALTER TABLE `exp_low_reorder_settings` ADD `sort_order` ENUM('asc','desc') NOT NULL DEFAULT 'asc'");
		}

	}

}