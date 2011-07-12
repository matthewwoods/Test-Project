<?php if (empty($channels)): ?>

	<p><?=lang('no_reorder_fields')?></p>

<?php else: ?>
	<?php

		$this->table->set_template($cp_table_template);

		$this->table->set_heading(
			lang('channel'),
			lang('field'),
			lang('settings'),
			lang('reorder')
		);

		foreach($channels as $channel)
		{
			foreach($channel['fields'] as $field)
			{
				$this->table->add_row(
					'<strong>'.$channel['channel_title'].'</strong>',
					$field['field_label'],
					'<a href="'.BASE.AMP.$mod_url.'&amp;method=settings&amp;channel_id='.$channel['channel_id'].'&amp;field_id='.$field['field_id'].'">'.lang('edit_settings').'</a>',
					'<a href="'.BASE.AMP.$mod_url.'&amp;method=display&amp;channel_id='.$channel['channel_id'].'&amp;field_id='.$field['field_id'].'">'.lang('reorder_entries').'</a>'
				);
			}
		}

		echo $this->table->generate();
	?>
<?php endif; ?>