<?php

add_action( 'show_user_profile', 'hm_time_user_profile_fields' );
add_action( 'edit_user_profile', 'hm_time_user_profile_fields' );

function hm_time_user_profile_fields(){
	global $user_id;
	$user_id = (int) $user_id;

	echo '<h3>'.__('Time Zone') .'</h3>
		  <table class="form-table">';

	/**
	 * %1$s - field id/name
	 * %2$s - label text
	 * %3$s - field value e.g. $profileuser->id
	 * %4$s - description of field  __('')
	 */
	$table_row = '	<tr>
						<th><label for="%1$s">%2$s</label></th>
						<td>%3$s
							<span class="description">%4$s</span>
						</td>
					</tr>';
	$input_text = '<input type="text" name="%1$s" id="%1$s" value="%2$s"/>';

	// Set Timezone Entry Method Fields
	$hm_tz_set_method_array = array(
		'manual' => 'Manual'
	);

	apply_filters( 'hm_tz_set_method_array', $hm_tz_set_method_array );

	// only need to display set method if there is more than one option
	if(1 < count($hm_tz_set_method_array)){
		$hm_tz_set_method_input = '<p><label><input type="radio" name="%1$s" value="%2$s" %3$s/> %4$s</label></p>';
		$hm_tz_set_method_value = get_user_meta($user_id, 'hm_tz_set_method', true);
		$hm_tz_set_method = '';

		foreach ( $hm_tz_set_method_array as $sm_value => $sm_label ) {

			if( empty( $hm_tz_set_method_value ) ){
				$hm_tz_set_method_value = 'manual';
				$options = get_option('hm_tz_options');

				if(!empty($options['default_set_method'])){
					$hm_tz_set_method_value = $options['default_set_method'];     // set the default value
				}
			}

			$sm_saved = ( $hm_tz_set_method_value === $sm_value ? 'checked=checked' : '');

			$hm_tz_set_method .= sprintf($hm_tz_set_method_input, 'hm_tz_set_method', $sm_value , $sm_saved , $sm_label);
		}

		printf($table_row, 'hm_tz_set_method', __('Set method'), $hm_tz_set_method , __('Please select how you want your timezone to be updated'));
	} else {
		update_user_meta( $user_id, 'hm_tz_set_method', 'manual');
	}

	do_action( 'hm_tz_add_options', $user_id );

	// Set timezone manually
	$hm_tz_manual_value = get_user_meta($user_id, 'hm_tz_timezone', true);
	$locations = hm_tz_locations();
	$hm_tz_manual_inputs = '';
	foreach($locations as $lkey => $lvalue){
		$hm_tz_manual_inputs .= '<optgroup label="'.$lkey.'">';
		if(is_array($lvalue)){
			foreach($lvalue as $value => $label){
				$selected = ($hm_tz_manual_value == $value ? 'selected="selected"' : '');
				$hm_tz_manual_inputs .= '<option value="'.$value.'" '.$selected.'>'.$label.'</option>';
			}
		}
		$hm_tz_manual_inputs .= '</optgroup>';
	}

	$hm_tz_manual = '<select name="hm_tz_timezone">'.$hm_tz_manual_inputs.'</select>';
	printf($table_row, 'hm_tz_timezone', __('Manual Selection'), $hm_tz_manual, __('Please select your timezone'));

	echo '</table>';

}

add_action( 'personal_options_update', 'hm_time_save_profile_fields' );
add_action( 'edit_user_profile_update', 'hm_time_save_profile_fields' );

function hm_time_save_profile_fields( $user_id ) {

	if ( !current_user_can( 'edit_user', $user_id ) ) { return false; }

	if(isset($_POST['hm_tz_set_method']) && !empty($_POST['hm_tz_set_method'])){
		update_user_meta( $user_id, 'hm_tz_set_method', $_POST['hm_tz_set_method'] );
	}

	$hm_tz_new_timezone = $_POST['hm_tz_timezone'];

	apply_filters( 'hm_tz_save_options', $user_id, $_POST );

	update_user_meta( $user_id, 'hm_tz_timezone', $hm_tz_new_timezone );
}


function hm_tz_locations(){
	$zones = timezone_identifiers_list();
	$locations = array();
	foreach ($zones as $zone)
	{
		$zone = explode('/', $zone); // 0 => Continent, 1 => City

		// Only use "friendly" continent names
		if ($zone[0] == 'Africa' || $zone[0] == 'America' || $zone[0] == 'Antarctica' || $zone[0] == 'Arctic' || $zone[0] == 'Asia' || $zone[0] == 'Atlantic' || $zone[0] == 'Australia' || $zone[0] == 'Europe' || $zone[0] == 'Indian' || $zone[0] == 'Pacific')
		{
			if (isset($zone[1]) != '')
			{
				$locations[$zone[0]][$zone[0]. '/' . $zone[1]] = str_replace('_', ' ', $zone[1]); // Creates array(DateTimeZone => 'Friendly name')
			}
		}
	}
	return $locations;
}

