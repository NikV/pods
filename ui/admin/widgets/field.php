<style type="text/css">
	ol.pods_field_widget_form {
		list-style: none;
		padding-left: 0;
		margin-left: 0;
	}

	ol.pods_field_widget_form label {
		display: block;
	}
</style>

<ol class="pods_field_widget_form">
	<li>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"> <?php _e( 'Title', 'pods' ); ?></label>

		<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr( $title ); ?>" />
	</li>

	<li>
		<?php
		$api      = pods_api();
		$all_pods = $api->load_pods( array( 'names' => true ) );
		?>
		<label for="<?php echo $this->get_field_id( 'pod_type' ); ?>">
			<?php _e( 'Pod', 'pods' ); ?>
		</label>

		<?php if ( 0 < count( $all_pods ) ): ?>
			<select id="<?php echo $this->get_field_id( 'pod_type' ); ?>" name="<?php echo $this->get_field_name( 'pod_type' ); ?>">
				<?php foreach ( $all_pods as $pod_name => $pod_label ): ?>
					<?php $selected = ( $pod_name == $pod_type ) ? 'selected' : ''; ?>
					<option value="<?php echo $pod_name; ?>" <?php echo $selected; ?>>
						<?php echo esc_html( $pod_label . ' (' . $pod_name . ')' ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		<?php else: ?>
			<strong class="red"><?php _e( 'None Found', 'pods' ); ?></strong>
		<?php endif; ?>
	</li>

	<li>
		<label for="<?php echo $this->get_field_id( 'slug' ); ?>">
			<?php _e( 'Slug or ID', 'pods' ); ?>
		</label>

		<input class="widefat" type="text" id="<?php echo $this->get_field_id( 'slug' ); ?>" name="<?php echo $this->get_field_name( 'slug' ); ?>" value="<?php echo esc_attr( $slug ); ?>" />
	</li>

	<li>
		<label for="<?php echo $this->get_field_id( 'field' ); ?>"><?php _e( 'Field', 'pods' ); ?></label>

		<input class="widefat" type="text" name="<?php echo $this->get_field_name( 'field' ); ?>" id="<?php echo $this->get_field_id( 'field' ); ?>" value="<?php echo esc_attr( $field ); ?>" />
	</li>

	<li>
		<label for="<?php echo $this->get_field_id( 'before' ); ?>"><?php _e( 'Before Text', 'pods' ); ?></label>

		<input class="widefat" type="text" name="<?php echo $this->get_field_name( 'before' ); ?>" id="<?php echo $this->get_field_id( 'before' ); ?>" value="<?php echo esc_attr( $before ); ?>" />
	</li>

	<li>
		<label for="<?php echo $this->get_field_id( 'after' ); ?>"><?php _e( 'After Text', 'pods' ); ?></label>

		<input class="widefat" type="text" name="<?php echo $this->get_field_name( 'after' ); ?>" id="<?php echo $this->get_field_id( 'after' ); ?>" value="<?php echo esc_attr( $after ); ?>" />
	</li>

	<li>
		<label for="<?php echo $this->get_field_id( 'shortcodes' ); ?>"><?php _e( 'Enable Shortcodes in output', 'pods' ); ?></label>

		<input type="checkbox" name="<?php echo $this->get_field_name( 'shortcodes' ); ?>" id="<?php echo $this->get_field_id( 'shortcodes' ); ?>" value="1" <?php selected( 1, $shortcodes ); ?> />
	</li>
</ol>
