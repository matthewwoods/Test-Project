<?php

	/**********************************************************************************
	
	------------------------
	Changelog
	------------------------
	
	*****
	0.3
	*****
	- Plugin now allows input of any file.
	- Can now define which minify group to use (set in groupsConfig.php)
	
	*****
	0.2
	*****
	- Fixed issue with document root if on media temple
	
	*****
	0.1
	*****
	- First release
	
	**********************************************************************************/

	$plugin_info       = array(
	'pi_name'        => 'Minify with File Timestamp',
	'pi_version'     => '0.3',
	'pi_author'      => 'Strawberry',
	'pi_author_url'  => 'http://strawberry.co.uk',
	'pi_description' => 'Gets the timestamp of a file.',
	'pi_usage'       => Minify_with_timestamp::usage()
	);
	
	if ( ! defined('BASEPATH')) exit('No direct script access allowed');
	
	class Minify_with_timestamp
	{

		var $return_data = '';
				
		function minify_with_timestamp()
		{
			$this->EE =& get_instance();
			
			if ($_SERVER['SERVER_ADDR'] == '192.9.200.196') {
				$doc_root = $_SERVER['DOCUMENT_ROOT'];
			} else {
				$doc_root = $_SERVER['DOCUMENT_ROOT'] . '/';
			}
			
			$group = $this->EE->TMPL->fetch_param('min_group');
			$file = $this->EE->TMPL->fetch_param('file');

			$unixdatum = filemtime($doc_root . $file);
			$timestamp = date('YmdHis', $unixdatum);
			
			// F d Y H:i:s
			// YmdHis			
			
			$this->return_data = '/min/g=' . $group . '?v=' . $timestamp;		
		}
		
		// ----------------------------------------
		//  Plugin Usage
		// ----------------------------------------
		
		// This function describes how the plugin is used.
		//  Make sure and use output buffering
		
		function usage()
		{
			ob_start();
			?>
			
			{exp:minify_with_timestamp min_group="css" file="_inc/css/styles.css"}
			{exp:minify_with_timestamp min_group="js" file="_inc/scripts/scripts.js"}
			{exp:minify_with_timestamp min_group="404" file="_inc/css/404.css"}
			
			<?php
			$buffer = ob_get_contents();
			
			ob_end_clean(); 
			
			return $buffer;
		}
		
	}

/* End of file pi.auto_acronym.php */
/* Location: ./system/plugins/pi.auto_acronym.php */
