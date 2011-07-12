<?php
  
$plugin_info = array(
						'pi_name'			=> 'Alternate Redirect',
						'pi_version'		=> '1.0',
						'pi_author'			=> 'Matthew Woods',
						'pi_author_url'		=> 'http://www.strawberry.co.uk/',
						'pi_description'	=> 'Change the header for redirect from default 302',  
						'pi_usage'       => Altredirect::usage()  
					);

  
class Altredirect  
{  
  
  var $return_data;
  
   function Altredirect()  
   {  
		global $TMPL; 
		
		$this->EE =& get_instance();
		
		if ($this->EE->TMPL->fetch_param('url') === FALSE) { $url = ""; } else { $url = $this->EE->TMPL->fetch_param('url'); }
		if ($this->EE->TMPL->fetch_param('status') === FALSE) { $status = "301"; } else { $status = $this->EE->TMPL->fetch_param('status'); }
		
		$url =  html_entity_decode($url);
		
		$statusid = array(
							

							300	=> 'Multiple Choices',
							301	=> 'Moved Permanently',
							302	=> 'Found',
							304	=> 'Not Modified',
							305	=> 'Use Proxy',
							307	=> 'Temporary Redirect',

							
						);
		  
		Header( "HTTP/1.1 ".$status." ".$statusid[$status]."" ); 
		Header( "Location: ".$url );
		exit();
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
  
 
  {exp:altredirect url=""} 
  
   <?php  
   $buffer = ob_get_contents();  
  
   ob_end_clean();   
  
   return $buffer;  
   }  
  
}  
?>