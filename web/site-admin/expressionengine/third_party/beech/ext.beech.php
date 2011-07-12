<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * @package		Beech
 * @author		Greg Salt <drylouvre> <greg@purple-dogfish.co.uk>
 * @copyright	Copyright (c) 2010 Purple Dogfish Ltd
 * @license		http://www.purple-dogfish.co.uk/licence/free
 * @link		http://www.purple-dogfish.co.uk/free-stuff/beech
 * @since		Version 0.1
 * 
 */

/**
 * Changelog
 * ---------------------------------------------------------
 * Version 0.1 20101026
 * Initial public release
 * * ---------------------------------------------------------
 */
class Beech_ext {
	
	public $name = 'Beech';
	public $version = '0.1';
	public $description = 'Set the CP sidebar to be off by default';
	public $settings_exist = 'n';
	public $docs_url = 'http://www.purple-dogfish.co.uk/';
	
	public $settings = array();
	
	private $EE;
	
	public function __construct($settings = '')
	{
		$this->EE =& get_instance();
		$this->settings = $settings;
		
		if (REQ !== 'CP')
		{
			return;
		}
		
		$this->setup_donation_button();
	}
	
	public function set_cp_cookie()
	{
		if ($this->EE->input->cookie('cp_sidebar_state') == '')
		{
			setcookie('exp_cp_sidebar_state', 'off');
		}
	}
	
	public function setup_donation_button()
	{
		$this->name .=<<<DONATE
&nbsp;&nbsp;<form style="display: inline;" action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="98RUKB74X6UHE">
<input style="vertical-align: middle;" height="18" type="image" src="https://www.paypal.com/en_GB/i/btn/btn_donate_SM.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online.">
<img alt="" border="0" src="https://www.paypal.com/en_GB/i/scr/pixel.gif" width="1" height="1">
</form>
DONATE;
	}
	
	public function activate_extension()
	{
		$data = array();

		$data['class']			= __CLASS__;
		$data['method']			= "set_cp_cookie";
		$data['hook']     	    = "cp_member_login";
		$data['settings']	    = "";
		$data['priority']	    = 10;
		$data['version']		= $this->version;
		$data['enabled']		= "y";
		
		$this->EE->db->insert('extensions', $data);

	}

	public function update_extension($current = '')
	{
		$status = FALSE;
		
		if ($this->version != $current)
		{
			$data = array();
			$data['version'] = $this->version;
			$this->EE->db->update('extensions', $data, 'version = '.$current);
			
			if($this->EE->db->affected_rows() == 1)
			{
				$status = TRUE;
			}
		}
		
		return $status;
	}

	public function disable_extension()
	{
		$this->EE->db->where('class', __CLASS__);
		$this->EE->db->delete('extensions');
	}
}
/* End of file ext.beech.php */
/* Location: ./system/expressionengine/third_party/beech/ext.beech.php */