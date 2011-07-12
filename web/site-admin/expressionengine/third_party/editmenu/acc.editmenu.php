<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * ExpressionEngine - by EllisLab
 *
 * 
 * @author		Matthew Woods - Strawberry
 * @license		http://strawberry.co.uk
 * @version		Version 1.2
 *
 * @tweaked		Ability to separate content menus and hide the help button - 15-04-2011
 * @fixed		nav_divider now appears	- 19-04-2011
 *
 */

// ------------------------------------------------------------------------


class editmenu_acc {

	var $name			= 'EditMenu';
	var $id				= 'editmenu_info';
	var $version		= '1.2';
	var $description	= 'Add a edit menu list like the publish tab.';
	var $sections		= array();
	
	
	/**
	 * Constructor
	 */
	function editmenu_acc()
	{
		$this->EE =& get_instance();
		$this->EE->load->helper('array');
	}

	function update()
	{
		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Set Sections
	 *
	 * Set content for the accessory
	 *
	 * @access	public
	 * @return	void
	 */
	function set_sections()
	{
		
		// Get Extension Settings
		$this->EE->db->select('settings');
		$this->EE->db->from('exp_extensions');
		$this->EE->db->where('class', 'Editmenu_ext');
		$query = $this->EE->db->get();
		
		// Loop through results
		foreach ($query->result() AS $row)
		{
			$settings = $row->settings;
		}
		$settings_data = unserialize($settings);
		
		// Help button setting
		$help_button = $settings_data['help_button'];
		// Separate content tab setting
		$separate_content = $settings_data['separate_content'];
		// Publish tab name
		$publish_name = $settings_data['publish_name'];
		// Edit tab name
		$edit_name = $settings_data['edit_name'];
		
		if($publish_name=="") { $publish_name = "Publish";}
		if($edit_name=="") { $edit_name = "Edit";}
		
		// this prevents errors because our view path has been remapped
		$current_view_path = $this->EE->load->_ci_view_path;
		$this->EE->load->_ci_view_path = PATH_CP_THEME.'default/';
		$menu_string = "";
		
		// backup existing themed menu views
		$menu_views = array(
			'menu_parent' => $this->EE->menu->menu_parent,
			'menu_item' => $this->EE->menu->menu_item,
			'menu_divider' => $this->EE->menu->menu_divider
		);
		
		// whip up a new menu array
		$menu = $this->EE->menu->generate_menu();
		
		
		// restore menu view files & view path
		$this->EE->menu->menu_parent = $menu_views['menu_parent'];
		$this->EE->menu->menu_item = $menu_views['menu_item'];
		$this->EE->menu->menu_divider = $menu_views['menu_divider'];
		
		$menu['content']['publish'] = $menu['content']['publish'];
		$menu['content']['edit'] = $menu['content']['publish'];
		$menu['content']['edit'] = str_replace('C=content_publish&amp;M=entry_form', 'C=content_edit', $menu['content']['edit']);
		
		if($separate_content!="no") {
			$publishContent = $menu['content']['publish'];
			$editContent = $menu['content']['edit'];
			$fileManagerContent = $menu['content']['files'];
			
			$menu_string .= $this->EE->menu->_process_menu(array($publish_name => $publishContent));
			$menu_string .= $this->EE->menu->_process_menu(array($edit_name => $editContent));
			
			unset($menu['content']);
		} 
		
		
		//print($settings['help_button']);
		$menu_string .= $this->EE->menu->_process_menu($menu);
		$menu_string .= $this->EE->menu->_process_menu($this->EE->menu->_fetch_quick_tabs(), 0, FALSE);
		
		// Disable help link, it's not very helpful
		if($help_button=="on") {
			$menu_string .= $this->EE->menu->_process_menu(array('help' => $this->EE->menu->generate_help_link()));
		}
		
		if($separate_content!="no") {
			$menu_string .= $this->EE->menu->_process_menu(array('File Manager' => $fileManagerContent));
		}
		
		$menu_string .= $this->EE->menu->_process_menu($this->EE->menu->_fetch_site_list(), 0, FALSE, 'msm_sites');
		
		// stupid EE prepends 'nav_' in front of menu items when it tries to localize them
		$menu_string = str_replace('nav_', '', $menu_string);
		$menu_string = str_replace('divider', 'nav_divider', $menu_string);
		
		//print($menu_string); 
		// save our new menu html
		$this->EE->load->vars('menu_string', $menu_string);
		
					
			// remove the unused accessory tab
		$this->EE->javascript->output('$("#accessoryTabs > ul > li > a.editmenu_info").parent("li").remove();');
		$this->EE->javascript->compile();
		
		// be polite and restore the old view path
		$this->EE->load->_ci_view_path = $current_view_path;
	}

	// --------------------------------------------------------------------

	function _build_tips()
	{
	
		$information = "<p>Never shows or it shouldn't anyways</p>";
	
		return $information;
	}

	
}
// END CLASS

/* End of file acc.strawberry.php */
/* Location: ./system/expressionengine/accessories/acc.strwberry.php */