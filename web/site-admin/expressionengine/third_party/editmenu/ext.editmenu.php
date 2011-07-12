<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Editmenu_ext {

    var $settings        = array();
    
    var $name            = 'Menu Edit Extension';
    var $version         = '1.0.0';
    var $description     = 'Menu Edit extension';
    var $settings_exist  = 'y';
    var $docs_url        = 'http://www.strawberry.co.uk';//'http://expressionengine.com';
    
    // -------------------------------
    //   Constructor - Extensions use this for settings
    // -------------------------------
    
    function Editmenu_ext($settings='')
    {
        $this->settings = $settings;
		$this->EE =& get_instance();
    }
    // END
	
	
	
	// --------------------------------
	//  Activate Extension
	// --------------------------------

	
	
	function activate_extension()
	{

	  $this->EE->db->query($this->EE->db->insert_string('exp_extensions',
	    	array(
				'extension_id' => '',
		        'class'        => ucfirst(get_class($this)),
		        'method'       => '',
		        'hook'         => '',
		        'settings'     => '',
		        'priority'     => 10,
		        'version'      => $this->version,
		        'enabled'      => "y"
				)
			)
		);
	}
	// END


	// --------------------------------
	//  Update Extension
	// --------------------------------  

	function update_extension($current='')
	{
		
		if ($current == '' OR $current == $this->version)
		{
			return FALSE;
		}
		
		
		$this->EE->db->query("UPDATE exp_extensions 
					SET version = '".$this->EE->db->escape_str($this->version)."' 
					WHERE class = '".ucfirst(get_class($this))."'");
	}
	// END
	
	// --------------------------------
	//  Disable Extension
	// --------------------------------

	function disable_extension()
	{
		$this->EE->db->query("DELETE FROM exp_extensions WHERE class = '".ucfirst(get_class($this))."'");
	}
	
	// END
	
	
	// --------------------------------
	//  Settings
	// --------------------------------  

	function settings()
	{
		$settings = array();
		
		$settings['publish_name']    = "";
		$settings['edit_name']    = "";
		
		$settings['separate_content'] = array('r', array('yes' => "yes", 'no' => "no"), 'yes');
		$settings['help_button'] = array('r', array('on' => "on", 'off' => "off"), 'off');
		
		// Complex:
		// [variable_name] => array(type, values, default value)
		// variable_name => short name for setting and used as the key for language file variable
		// type:  t - textarea, r - radio buttons, s - select, ms - multiselect, f - function calls
		// values:  can be array (r, s, ms), string (t), function name (f)
		// default:  name of array member, string, nothing
		//
		// Simple:
		// [variable_name] => 'Butter'
		// Text input, with 'Butter' as the default.
		
		
		
		return $settings;
	}
	// END

/*
	if (isset($EXT->extensions['publish_form_new_tabs']))
	{
	 $publish_tabs = $EXT->call_extension('publish_form_new_tabs', $publish_tabs, $weblog_id, $entry_id);
	 
	 $publish_tabs['bm'] = 'Meta';
	 
	 return $publish_tabs;
	}
*/
function publish_form_new_tabs( $publish_tabs, $weblog_id, $entry_id, $hidden )
	{
	
		if (isset($EXT->extensions['publish_form_new_tabs']))
		{
		  $publish_tabs = $EXT->call_extension('publish_form_new_tabs', $publish_tabs, $weblog_id, $entry_id);
	 
		$publish_tabs['bm'] = 'Meta';
		} 
		
		return $publish_tabs;
	}
	

	

}
// END CLASS
?>