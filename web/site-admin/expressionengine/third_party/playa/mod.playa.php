<?php if (! defined('BASEPATH')) exit('Invalid file request');

/**
 * Playa Module Class
 *
 * @package   Playa
 * @author    Brandon Kelly <brandon@pixelandtonic.com>
 * @copyright Copyright (c) 2011 Pixel & Tonic, Inc
 */
class Playa
{
	/**
	 * Constructor
	 */
	function __construct()
	{
		$this->EE =& get_instance();

		// -------------------------------------------
		//  Prepare Cache
		// -------------------------------------------

		if (! isset($this->EE->session->cache['playa']))
		{
			$this->EE->session->cache['playa'] = array();
		}

		$this->cache =& $this->EE->session->cache['playa'];

		// -------------------------------------------
		//  Load the helper
		// -------------------------------------------

		if (! class_exists('Playa_Helper'))
		{
			require_once PATH_THIRD.'playa/helper.php';
		}

		$this->helper = new Playa_Helper();

		// -------------------------------------------
		//  Grab the entry_id
		// -------------------------------------------

		$this->entry_id = $this->EE->TMPL->fetch_param('entry_id');
		unset($this->EE->TMPL->tagparams['entry_id']);
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch Tagdata
	 *
	 * If this tag is within an {exp:channel:entries} tag, we've cached its tagdata
	 * so that Channel::entries() doesn't parse our precious bodily variables
	 */
	private function _fetch_tagdata($debug=FALSE)
	{
		// are we caching the tagdata?
		if (($index = $this->EE->TMPL->fetch_param('tagdata_cache_index')) !== FALSE
			&& isset($this->cache['tagdata'][$index]))
		{
			// get the tagdata from cache
			$tagdata = $this->cache['tagdata'][$index];

			// unset the index param
			unset($this->EE->TMPL->tagparams['tagdata_cache_index']);
		}
		else
		{
			$tagdata = $this->EE->TMPL->tagdata;

			// are we hiding the delimiters?
			if ($marker = $this->EE->TMPL->fetch_param('delimiter_marker'))
			{
				$LD = 'LD['.$marker.']LD';
				$RD = 'RD['.$marker.']RD';

				$tagdata = str_replace(array($LD, $RD), array(LD, RD), $tagdata);

				// unset the delimiter param
				unset($this->EE->TMPL->tagparams['delimiter_marker']);
			}
		}

		return $tagdata;
	}

	// --------------------------------------------------------------------

	/**
	 * Names to IDs
	 */
	private function _names_to_ids($what, $table, $names)
	{
		$names = explode('|', $names);

		// which site are we looking at?
		$row = $this->_fetch_entry_row();
		$site_id = $row['entry_site_id'];

		if (! isset($this->cache['site_'.$what.'s'][$site_id]))
		{
			$this->cache['site_'.$what.'s'][$site_id] = array();
		}

		$site_fields =& $this->cache['site_'.$what.'s'][$site_id];

		// figure out which ones are already cached
		$get_names = array();

		foreach ($names as $name)
		{
			if (array_search($name, $site_fields) === FALSE)
			{
				$get_names[] = $name;
			}
		}

		// grab the ones we're missing
		if ($get_names)
		{
			$this->EE->db->select($what.'_id AS id, '.$what.'_name AS name');
			$this->helper->db_where($what.'_name', $get_names);
			$query = $this->EE->db->get($table);

			foreach ($query->result() as $row)
			{
				$site_fields[$row->id] = $row->name;
			}
		}

		// return the field IDs
		$ids = array();

		foreach ($names as $name)
		{
			if (($id = array_search($name, $site_fields)) !== FALSE)
			{
				$ids[] = $id;
			}
		}

		return $ids;
	}

	/**
	 * Fetch Field IDs
	 *
	 * Returns an array of field IDs associated with this tag,
	 * whether passed via the field_id= or field= params
	 */
	private function _fetch_field_ids()
	{
		if ($field_id = $this->EE->TMPL->fetch_param('field_id'))
		{
			// unset the param
			unset($this->EE->TMPL->tagparams['field_id']);

			return explode('|', $field_id);
		}

		if (($field_names = $this->EE->TMPL->fetch_param('field')))
		{
			// unset the param
			unset($this->EE->TMPL->tagparams['field']);

			return $this->_names_to_ids('field', 'channel_fields', $field_names);
		}
	}

	/**
	 * Fetch Column IDs
	 *
	 * Returns an array of col IDs associated with this tag,
	 * whether passed via the col_id= or col= params
	 */
	private function _fetch_col_ids()
	{
		if ($col_id = $this->EE->TMPL->fetch_param('col_id'))
		{
			// unset the param
			unset($this->EE->TMPL->tagparams['col_id']);

			return explode('|', $col_id);
		}

		if ($col_names = $this->EE->TMPL->fetch_param('col'))
		{
			// unset the param
			unset($this->EE->TMPL->tagparams['col']);

			return $this->_names_to_ids('col', 'matrix_cols', $col_names);
		}

	}

	/**
	 * Fetch Row IDs
	 *
	 * Returns an array of row IDs associated with this tag via the row_id= param
	 */
	private function _fetch_row_ids()
	{
		if ($row_id = $this->EE->TMPL->fetch_param('row_id'))
		{
			// unset the param
			unset($this->EE->TMPL->tagparams['row_id']);

			return explode('|', $row_id);
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Parse No Results Tag
	 */
	private function _parse_no_results_tag(&$tagdata, $tag)
	{
		$tagdata = preg_replace_callback("/\{if {$tag}\}(.*?)\{\/if\}/s", array(&$this, '_replace_no_results_tag'), $tagdata, 1);
	}

	/**
	 * Replace No Results Tag
	 */
	private function _replace_no_results_tag($m)
	{
		$this->EE->TMPL->no_results_block = $m[0];
		$this->EE->TMPL->no_results = $m[1];

		return '';
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch Entry Row
	 */
	private function _fetch_entry_row()
	{
		if (! isset($this->cache['entry_rows'][$this->entry_id]))
		{
			// just grab it from the DB
			$row = $this->EE->db->query("SELECT ct.author_id, c.channel_title, c.channel_id, c.channel_name, ct.entry_date, ct.site_id AS entry_site_id, ct.expiration_date, m.screen_name, ct.status, ct.title, ct.url_title
			                             FROM exp_channel_titles AS ct
			                             LEFT JOIN exp_channels AS c ON ct.channel_id = c.channel_id
			                             LEFT JOIN exp_members AS m ON ct.author_id = m.member_id
			                             WHERE ct.entry_id = '{$this->entry_id}'")
			                    ->row_array();

			$row['entry_id'] = $this->entry_id;
			$row['count'] = '1';

			// cache it for maybe later
			$this->cache['entry_rows'][$this->entry_id] =& $row;
		}

		return $this->cache['entry_rows'][$this->entry_id];
	}

	/**
	 * Fetch Entry Data
	 */
	private function _parse_relative_entry_tags(&$tagdata, $tag_prefix)
	{
		// ignore if no relative entry tags
		if (strpos($tagdata, LD.$tag_prefix) === FALSE) return;

		// -------------------------------------------
		//  Get the entry's row data
		// -------------------------------------------

		$row = $this->_fetch_entry_row();

		// -------------------------------------------
		//  Parse the relative entry's tags
		// -------------------------------------------

		$rel_row_tags = array(
			$tag_prefix.'author_id'          => $row['author_id'],
			$tag_prefix.'channel'            => $row['channel_title'],
			$tag_prefix.'channel_id'         => $row['channel_id'],
			$tag_prefix.'channel_short_name' => $row['channel_name'],
			$tag_prefix.'count'              => $row['count'],
			$tag_prefix.'entry_date'         => $row['entry_date'],
			$tag_prefix.'entry_id'           => $row['entry_id'],
			$tag_prefix.'entry_site_id'      => $row['entry_site_id'],
			$tag_prefix.'expiration_date'    => $row['expiration_date'],
			$tag_prefix.'screen_name'        => $row['screen_name'],
			$tag_prefix.'status'             => $row['status'],
			$tag_prefix.'title'              => $row['title'],
			$tag_prefix.'url_title'          => $row['url_title']
		);

		$tagdata = $this->EE->functions->var_swap($tagdata, $rel_row_tags);
	}

	// --------------------------------------------------------------------

	/**
	 * Parse Relationships
	 *
	 * Fools EE into thinking that this was an {exp:channel:entries} tag
	 */
	private function _parse_relationships($tagdata, $entry_ids, $fixed_order)
	{
		if (! $tagdata) return;

		// -------------------------------------------
		//  Parse the relationships
		// -------------------------------------------

		// make sure $entry_ids is a pipe delimited string
		if (is_array($entry_ids)) $entry_ids = implode('|', $entry_ids);

		// entry_id= or fixed_order=
		$entry_id_param = ($fixed_order && ! $this->EE->TMPL->fetch_param('orderby')) ? 'fixed_order' : 'entry_id';
		$this->EE->TMPL->tagparams[$entry_id_param] = '0|'.$entry_ids;

		// dynamic="nuts"
		$this->EE->TMPL->tagparams['dynamic'] = 'no';

		if (! isset($this->EE->TMPL->tagparams['disable']))
		{
			// disable everything but custom_fields by default
			$this->EE->TMPL->tagparams['disable'] = 'categories|category_fields|member_data|pagination';
		}

		// prep TMPL with the cached tagdata
		$this->EE->TMPL->tagdata = $tagdata;

		$vars = $this->EE->functions->assign_variables($tagdata);
		$this->EE->TMPL->var_single = $vars['var_single'];
		$this->EE->TMPL->var_pair   = $vars['var_pair'];

		// _fetch_site_ids is meant to be a private method,
		// so make sure it's still around before calling it
		if (method_exists($this->EE->TMPL, '_fetch_site_ids'))
		{
			$this->EE->TMPL->_fetch_site_ids();
		}

		if (! class_exists('Channel'))
		{
			require PATH_MOD.'channel/mod.channel.php';
		}

		// create a new Channel object and run entries()
		$Channel = new Channel();
		return $Channel->entries();
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch Relationships
	 */
	private function _fetch_rels($dir)
	{
		// field, col, and row params
		$field_ids = $this->_fetch_field_ids();
		$col_ids   = $this->_fetch_col_ids();
		$row_ids   = $this->_fetch_row_ids();

		// cached?
		$cache_key = $this->entry_id . '|'
		           . $dir . '|'
		           . ($field_ids ? implode(',', $field_ids) : '*') . '|'
		           . ($col_ids   ? implode(',', $col_ids)   : '*') . '|'
		           . ($row_ids   ? implode(',', $row_ids)   : '*');

		// find the rels if they aren't already cached
		if (! isset($this->cache['rels'][$cache_key]))
		{
			if ($dir == 'children')
			{
				$this->EE->db->where('parent_entry_id', $this->entry_id);

				// the rel_order matters
				$this->EE->db->order_by('rel_order');
			}
			else
			{
				$this->EE->db->where('child_entry_id', $this->entry_id);
			}

			// filter by field?
			if ($field_ids)
			{
				$this->helper->db_where('parent_field_id', $field_ids);
			}

			// filter by column?
			if ($col_ids)
			{
				$this->helper->db_where('parent_col_id', $col_ids);
			}

			// filter by row?
			if ($row_ids)
			{
				$this->helper->db_where('parent_row_id', $row_ids);
			}

			// get the relationships
			$rels = $this->EE->db->get('playa_relationships');

			// cache them in case an identical request comes later
			$this->cache['rels'][$cache_key] = $rels;
		}

		return $this->cache['rels'][$cache_key];
	}

	/**
	 * Fetch Related Entry IDs
	 *
	 * Returns a pipe-delimited string of the related entry_ids
	 */
	private function _fetch_rel_entry_ids($dir, $ids_tag = FALSE)
	{
		$rels = $this->_fetch_rels($dir);

		$select = ($dir == 'children') ? 'child_entry_id' : 'parent_entry_id';

		$entry_ids = array();

		foreach ($rels->result() as $i => $rel)
		{
			$entry_ids[] = $rel->$select;
		}

		// get the delimiter
		$delimiter = ($ids_tag && ($delimiter = $this->EE->TMPL->fetch_param('delimiter')) !== FALSE) ? $delimiter : '|';

		// flatten the array
		$r = implode($delimiter, $entry_ids);

		// backspace=
		if ($ids_tag && ($backspace = $this->EE->TMPL->fetch_param('backspace')))
		{
			$r = substr($r, 0, -$backspace);
		}

		return $r;
	}

	/**
	 * Child IDs
	 */
	function child_ids($ids_tag = TRUE)
	{
		if (! $this->entry_id) return;

		return $this->_fetch_rel_entry_ids('children', $ids_tag);
	}

	/**
	 * Parent IDs
	 */
	function parent_ids($ids_tag = TRUE)
	{
		if (! $this->entry_id) return;

		return $this->_fetch_rel_entry_ids('parents', $ids_tag);
	}

	// --------------------------------------------------------------------

	/**
	 * Children
	 */
	function children()
	{
		if (! $this->entry_id) return;

		$tagdata = $this->_fetch_tagdata();

		if (! $tagdata) return;

		// tagdata prep
		$tagdata = str_replace('total_related_entries', 'total_results', $tagdata);
		$this->_parse_no_results_tag($tagdata, 'no_children');
		$this->_parse_relative_entry_tags($tagdata, 'parent:');

		// get the child entry IDs
		$child_ids = $this->child_ids(FALSE);

		return $this->_parse_relationships($tagdata, $child_ids, TRUE);
	}

	/**
	 * Parents
	 */
	function parents()
	{
		if (! $this->entry_id) return;

		$tagdata = $this->_fetch_tagdata(TRUE);

		if (! $tagdata) return;

		// tagdata prep
		$this->_parse_no_results_tag($tagdata, 'no_parents');
		$this->_parse_relative_entry_tags($tagdata, 'child:');

		// get the parent entry IDs
		$parent_ids = $this->parent_ids(FALSE);

		return $this->_parse_relationships($tagdata, $parent_ids, FALSE);
	}

	// --------------------------------------------------------------------

	/**
	 * Entries Query
	 *
	 * Loads the Playa Helper class and calls its entries_query() function
	 */
	private function _total_entries($entry_ids)
	{
		// ignore if no entry_ids
		if (! $entry_ids) return 0;

		// -------------------------------------------
		//  Get the parameters
		// -------------------------------------------

		$params = array(
			'count'               => TRUE,
			'entry_id'            => $entry_ids,
			'status'              => 'open',
			'show_future_entries' => 'no',
			'show_expired'        => 'no'
		);

		$check_params = array('author_id', 'group_id', 'category', 'category_group', 'show_future_entries', 'show_expired', 'status', 'url_title', 'channel', 'channel_id', 'keywords', 'orderby', 'sort', 'limit', 'offset');

		foreach ($check_params as $param)
		{
			if (($val = $this->EE->TMPL->fetch_param($param)) !== FALSE)
			{
				$params[$param] = $val;
			}
		}

		return $this->helper->entries_query($params);
	}

	/**
	 * Total Children
	 *
	 * Returns the total number of children for this entry/field/column/row
	 */
	function total_children()
	{
		if (! $this->entry_id) return 0;

		$child_ids = $this->child_ids(FALSE);

		return $this->_total_entries($child_ids);
	}

	/**
	 * Total Parents
	 *
	 * Returns the total number of parents for this entry
	 */
	function total_parents()
	{
		if (! $this->entry_id) return 0;

		$parent_ids = $this->parent_ids(FALSE);
		return $this->_total_entries($parent_ids);
	}

}
