<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
* Low Reorder Module Class
*
* Class to be used in templates
*
* @package		low-reorder-ee2_addon
* @author		Lodewijk Schutte <low@loweblog.com>
* @link			http://loweblog.com/freelance/
* @copyright	Copyright (c) 2010, Low
* @since		1.0.2
*/

class Low_reorder {

	/**
	*	Return data
	*
	*	@var	string
	*/
	var $return_data = '';

	// --------------------------------------------------------------------

	/**
	*	PHP4 Constructor
	*
	*	@see	__construct()
	*/
	function Low_reorder()
    {
		$this->__construct();
	}

	// --------------------------------------------------------------------

	/**
	*	PHP5 constructor
	*
	*	@return	void
	*/
	function __construct()
	{
		/** -------------------------------------
		/**  Get global object
		/** -------------------------------------*/

		$this->EE =& get_instance();
	}

	// --------------------------------------------------------------------

	/**
	*	Get next entry in custom order
	*
	*	@return	string
	*/
	function next_entry()
	{
		return $this->_prev_next('next');
	}

	// --------------------------------------------------------------------

	/**
	*	Get previous entry in custom order
	*
	*	@return	string
	*/
	function prev_entry()
	{
		return $this->_prev_next('prev');
	}

	// --------------------------------------------------------------------

	/**
	*	Get previous or next entry in custom order
	*
	*	@param	string
	*	@return	string
	*/
	function _prev_next($which)
	{
		/** -------------------------------------
		/**  Get parameters
		/** -------------------------------------*/

		foreach (array('field', 'entry_id', 'url_title', 'prefix', 'no_results') AS $param)
		{
			$$param = $this->EE->TMPL->fetch_param($param);
		}

		/** -------------------------------------
		/**  Get site id
		/** -------------------------------------*/

		$site_id = $this->EE->config->item('site_id');

		/** -------------------------------------
		/**  Check field parameter
		/** -------------------------------------*/

		if ( ! $field || ! isset($this->EE->session->cache['channel']['custom_channel_fields'][$site_id][$field]))
		{
			$this->EE->TMPL->log_item('Low Reorder: field not found, returning empty string');
			return '';
		}

		/** -------------------------------------
		/**  We need a $entry_id or $url_title to go on
		/** -------------------------------------*/

		if ( ! $entry_id && ! $url_title)
		{
			$this->EE->TMPL->log_item('Low Reorder: no entry_id or url_title given, returning empty string');
			return '';
		}

		/** -------------------------------------
		/**  Retrieve field id from cache
		/** -------------------------------------*/

		$field_id = $this->EE->session->cache['channel']['custom_channel_fields'][$site_id][$field];

		/** -------------------------------------
		/**  Retrieve current entry's channel and order
		/** -------------------------------------*/

		if (isset($this->EE->session->cache['low']['reorder']['entries'][$entry_id]))
		{
			// Getting this entry's data from cache
			$entry = $this->EE->session->cache['low']['reorder']['entries'][$entry_id];
		}
		else
		{
			// Getting this entry's data from DB
			$this->EE->db->select("exp_channel_data.entry_id, exp_channel_data.channel_id, exp_channel_data.field_id_{$field_id} AS current_order");
			$this->EE->db->from('exp_channel_data');

			if ($entry_id === FALSE)
			{
				// If $entry_id is false, $url_title must be given, as checked above
				// Only if there's no entry_id, we'll join the exp_channel_titles table,
				// otherwise it's not necessary
				$this->EE->db->join('exp_channel_titles', 'exp_channel_data.entry_id = exp_channel_titles.entry_id');
				$this->EE->db->where('exp_channel_titles.url_title', $url_title);
			}
			else
			{
				// There is an entry id, so just query that
				$this->EE->db->where('exp_channel_data.entry_id', $entry_id);
			}

			// Site id and limit by one, which is good practice
			$this->EE->db->where('exp_channel_data.site_id', $site_id);
			$this->EE->db->limit(1);
			$query = $this->EE->db->get();

			if ($query->num_rows())
			{
				// If we have results, store it in session cache
				$entry = $query->row_array();
				$this->EE->session->cache['low']['reorder']['entries'][$entry['entry_id']] = $entry;
			}
			else
			{
				// No results for given entry? Nothing to do...
				$this->EE->TMPL->log_item('Low Reorder: Could not retrieve details for given entry, returning empty string');
				return '';
			}
		}

		/** -------------------------------------
		/**  Now then, retrieve prev/next entry
		/**  based on the found order, channel_id and field_id
		/** -------------------------------------*/

		$this->EE->TMPL->log_item("Low Reorder: Getting {$which} entry from database");

		$gtlt = ($which == 'next') ? '>' : '<';
		$sort = ($which == 'next') ? 'asc' : 'desc';

		$this->EE->db->select('exp_channel_titles.entry_id, exp_channel_titles.url_title, exp_channel_titles.title');
		$this->EE->db->from('exp_channel_titles');
		$this->EE->db->join('exp_channel_data', 'exp_channel_titles.entry_id = exp_channel_data.entry_id');
		$this->EE->db->where('exp_channel_titles.site_id', $site_id);
		$this->EE->db->where('exp_channel_titles.channel_id', $entry['channel_id']);
		$this->EE->db->where("exp_channel_data.field_id_{$field_id} !=", '');

		// Prev/Next magic
		$this->EE->db->where("exp_channel_data.field_id_{$field_id} {$gtlt}", $entry['current_order']);
		$this->EE->db->order_by("exp_channel_data.field_id_{$field_id}", $sort);

		$this->EE->db->limit(1);
		$query = $this->EE->db->get();

		/** -------------------------------------
		/**  Parse variables or show no_results
		/** -------------------------------------*/

		if ($query->num_rows())
		{
			$row = $query->row_array();

			// Add prefix to variables if so desired
			if ($prefix)
			{
				// Copy row
				$tmp = $row;

				foreach ($tmp AS $key => $val)
				{
					// Add prefix
					$row[$prefix.$key] = $val;

					// remove original
					unset($row[$key]);
				}

				// clean up
				unset($tmp);
			}

			// Parse template
			$this->return_data = $this->EE->TMPL->parse_variables($this->EE->TMPL->tagdata, array($row));
		}
		else
		{
			// No results if no record found
			$this->return_data = ($no_results === FALSE) ? $this->EE->TMPL->no_results() :  $no_results;
		}

		/** -------------------------------------
		/**  Please return it
		/** -------------------------------------*/

		return $this->return_data;
	}

	// --------------------------------------------------------------------

}
// END Low_reorder class