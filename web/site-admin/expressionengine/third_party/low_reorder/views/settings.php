
<?=form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=low_reorder'.AMP.'method=save_settings')?>
	<div>
		<input type="hidden" name="channel_id" value="<?=$channel['id']?>" />
		<input type="hidden" name="field_id" value="<?=$field['id']?>" />
	</div>
	<table class="mainTable" id="reorder-settings" cellspacing="0" cellpadding="0">
		<colgroup>
			<col style="width:30%" />
			<col style="width:70%" />
		</colgroup>
		<thead>
			<tr>
				<th scope="col"><?=lang('setting')?></th>
				<th scope="col"><?=lang('value')?></th>
			</tr>
		</thead>
		<tbody>
			<?php $i = 0; ?>
			<?php if ($category_groups !== FALSE): ?>
				<tr class="<?=(++$i%2?'odd':'even')?>">
					<td class="multi">
						<label for="category_options"><strong><?=lang('categories')?></strong></label>
					</td>
					<td id="reorder-category-list">
						<div id="reorder-category-options">
							<select name="category_options" id="category_options">
								<option value="all"<?php if ($settings['category_options'] == 'all'): ?> selected="selected"<?php endif; ?>><?=lang('show_all')?></option>
								<option value="one"<?php if ($settings['category_options'] == 'one'): ?> selected="selected"<?php endif; ?>><?=lang('show_one')?></option>
								<option value="some"<?php if ($settings['category_options'] == 'some'): ?> selected="selected"<?php endif; ?>><?=lang('show_some')?></option>
							</select>
						</div>
						<div id="reorder-category-container"<?php if ($settings['category_options'] != 'some'): ?> style="display:none"<?php endif; ?>>
							<label>
								<input type="checkbox" name="categories[]" value="0"<?php if($settings['categories'] === FALSE OR in_array('0', $settings['categories'])): ?> checked="checked"<?php endif; ?> />
								<?=lang('uncategorized')?>
							</label><br />
							<?php foreach ($category_groups AS $key => $group): ?>
								<h4><?=htmlspecialchars($group['group_name'])?></h4>
								<?php foreach ($group['categories'] AS $v): ?>
									<?php $indent = ($v['5'] != 1) ? repeater(NBS.NBS.NBS, $v['5']) : ''; ?>
									<label>
										<?=$indent.form_checkbox('categories[]', $v['0'], ($settings['categories'] === FALSE OR in_array($v['0'], $settings['categories']) ))?>
										<?=$v['1']?>
									</label><br />
								<?php endforeach; ?>
							<?php endforeach; ?>
						</div>
					</td>
				</tr>
			<?php endif; ?>
			<?php if (count($all_statuses)): ?>
			<tr class="<?=(++$i%2?'odd':'even')?>">
				<td class="multi">
					<strong><?=lang('statuses')?></strong>
				</td>
				<td>
					<?php foreach($all_statuses AS $row): ?>
						<label<?php if($row['highlight']): ?> style="color:#<?=$row['highlight']?>"<?php endif; ?>>
							<input type="checkbox" name="statuses[]" value="<?=$row['status_id']?>"<?php if($settings['statuses'] === FALSE OR in_array($row['status_id'], $settings['statuses'])): ?> checked="checked"<?php endif; ?>>
							<?=ucfirst($row['status'])?>
						</label><br />
					<?php endforeach; ?>
				</td>
			</tr>
			<?php endif; ?>
			<tr class="<?=(++$i%2?'odd':'even')?>">
				<td>
					<strong><?=lang('show_expired')?></strong>
				</td>
				<td>
					<label>
						<input type="checkbox" name="show_expired" value="y"<?php if($settings['show_expired'] == 'y'): ?> checked="checked"<?php endif; ?>>
						<?=lang('show_expired_entries')?>
					</label>
				</td>
			</tr>
			<tr class="<?=(++$i%2?'odd':'even')?>">
				<td>
					<strong><?=lang('show_future')?></strong>
				</td>
				<td>
					<label>
						<input type="checkbox" name="show_future" value="y"<?php if($settings['show_future'] == 'y'): ?> checked="checked"<?php endif; ?>>
						<?=lang('show_future_entries')?>
					</label>
				</td>
			</tr>
			<tr class="<?=(++$i%2?'odd':'even')?>">
				<td class="multi">
					<strong><?=lang('sort_order')?></strong>
				</td>
				<td>
					<label>
						<input type="radio" name="sort_order" value="asc"<?php if($settings['sort_order'] == 'asc'): ?> checked="checked"<?php endif; ?>>
						<?=lang('sort_order_asc')?>
					</label>
					<br />
					<label>
						<input type="radio" name="sort_order" value="desc"<?php if($settings['sort_order'] == 'desc'): ?> checked="checked"<?php endif; ?>>
						<?=lang('sort_order_desc')?>
					</label>
				</td>
			</tr>
			<tr class="<?=(++$i%2?'odd':'even')?>">
				<td class="multi">
					<strong>Entries tag</strong>
				</td>
				<td>
					<code id="tag-target"></code>
				</td>
			</tr>
		</tbody>
	</table>
	<p><?=form_submit(array('name' => 'submit', 'value' => lang('save'), 'class' => 'submit'))?></p>
<?=form_close()?>

<script type="text/javascript">
// <![CDATA[

	if (typeof LOW == 'undefined') {
		var LOW = new Object;
	}

	if (typeof LOW.Reorder == 'undefined') {
		LOW.Reorder = new Object;
	}

	LOW.Reorder.settings = {
		"channel" : "<?=$json['channel']?>",
		"field" : "<?=$json['field']?>",
		"statuses" : { 
			<?php foreach ($json['statuses'] AS $status_id => $status_name): ?>
				"<?=$status_id?>" : "<?=$status_name?>",
			<?php endforeach; ?>
			"0" : ""
		}
	};

// ]]>
</script>