<?php if ( $settings_form ) : ?>
<?php echo form_open(
		'C=addons_extensions&M=extension_settings&file=mx_status_change',
		'',
		array( "file" => "mx_status_change" )
	)
?>
<table class="mainTable padTable"  border="0" cellpadding="0" cellspacing="0">
<tbody>
<tr>
<th style="width:7%"><?php echo lang( 'site_id' )?></th>
<th><?php echo lang( 'site_label' )?></th>
<th><?php echo lang( 'site_short_name' )?></th>
<th><?php echo lang( 'site_theme' )?></th>
</tr>
</tbody> <?php endif; ?>
<tbody id="cond_table">

<?php

if ( ! function_exists( 'print_var' ) ) {
	function print_var( $var, $row, $index, $default = '' ) {
		return ( isset( $var[$row][$index] ) ) ? $var[$row][$index] : $default;
	}
}

$out = '';

foreach ($site_data->result() as $site)
{

	$out .= '<tr>';
	$out .= '<td class="">'.$site->site_id.'</td>';
	$out .= '<td class="">'."<strong>{$site->site_label}</strong>".'</td>';
	$out .= '<td class="">'.$site->site_name.'</td>';
	$out .= '<td class="">'.form_dropdown( $input_prefix . '[' . $site->site_id . '][status]', $status, (isset( $settings[$site->site_id]['status'])) ? $settings[$site->site_id]['statud'] : '')
.'</td>';
	$out .= '</tr>';


}

print $out;

?>
</tbody></table>
<p class="centerSubmit"><input name="edit_screen_size" value="<?php echo lang( 'save_extension_settings' ); ?>" class="submit" type="submit"></p>
<?php echo form_close(); ?>


