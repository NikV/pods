<?php
$options['data'] = (array) pods_var_raw( 'data', $options, array(), null, true );

if ( 1 == pods_v( 'grouped', $options, 0, true ) ) {
	?>
	<div class="pods-pick-values pods-pick-radio">
	<ul>
<?php
}

$counter        = 1;
$primary_name   = $name;
$primary_id     = 'pods-form-ui-' . Pods_Form::clean( $name );
$selection_made = false;

foreach ( $options['data'] as $val => $label ) {
	if ( is_array( $label ) ) {
		if ( isset( $label['label'] ) ) {
			$label = $label['label'];
		} else {
			$label = $val;
		}
	}

	$attributes = array();

	$attributes['type'] = 'radio';

	$attributes['checked']  = null;
	$attributes['tabindex'] = 2;

	if ( ! $selection_made && ( $val == $value || ( is_array( $value ) && in_array( $val, $value ) ) ) ) {
		$attributes['checked'] = 'CHECKED';
		$selection_made        = true;
	}

	$attributes['value'] = $val;

	$attributes = Pods_Form::merge_attributes( $attributes, $name, $form_field_type, $options );

	$indent = '';

	$indent_count = substr_count( $label, '&nbsp;&nbsp;&nbsp;' );

	if ( 0 < $indent_count ) {
		$label = str_replace( '&nbsp;&nbsp;&nbsp;', '', $label );

		$indent = ' style="margin-left:' . ( 18 * $indent_count ) . 'px;"';
	}

	if ( pods_v( 'readonly', $options, false ) ) {
		$attributes['readonly'] = 'READONLY';

		$attributes['class'] .= ' pods-form-ui-read-only';
	}

	if ( 1 < count( $options['data'] ) ) {
		$attributes['id'] = $primary_id . $counter;
	}

	if ( 1 == pods_v( 'grouped', $options, 0, true ) ) {
		?>
		<li>
	<?php
	}
	?>
	<div class="pods-field pods-boolean"<?php echo $indent; ?>>
		<input<?php Pods_Form::attributes( $attributes, $name, $form_field_type, $options ); ?> />
		<?php
		if ( 0 < strlen( $label ) ) {
			$help = pods_v( 'help', $options );

			if ( 1 == pods_v( 'grouped', $options, 0, true ) || empty( $help ) ) {
				$help = '';
			}

			echo Pods_Form::label( $attributes['id'], $label, $help );
		}
		?>
	</div>
	<?php

	if ( 1 == pods_v( 'grouped', $options, 0, true ) ) {
		?>
		</li>
	<?php
	}

	$counter ++;
}

if ( 1 == pods_v( 'grouped', $options, 0, true ) ) {
	?>
	</ul>
	</div>
<?php
}
