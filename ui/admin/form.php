<?php
wp_enqueue_script( 'pods' );
wp_enqueue_style( 'pods-form' );

$id = $pod->id();

if ( empty( $fields ) || ! is_array( $fields ) ) {
	$fields = $obj->pod->fields;
}

if ( ! isset( $duplicate ) ) {
	$duplicate = false;
} else {
	$duplicate = (boolean) $duplicate;
}

$block_field_types = Pods_Form::block_field_types();

// unset fields
foreach ( $fields as $k => $field ) {
	if ( in_array( $field['name'], array( 'created', 'modified' ) ) ) {
		unset( $fields[ $k ] );
	} elseif ( false === Pods_Form::permission( $field['type'], $field['name'], $field, $fields, $pod, $id ) ) {
		if ( pods_v( 'hidden', $field, false ) ) {
			$fields[ $k ]['type'] = 'hidden';
		} elseif ( pods_v( 'read_only', $field, false ) ) {
			$fields[ $k ]['readonly'] = true;
		} else {
			unset( $fields[ $k ] );
		}
	} elseif ( ! pods_has_permissions( $field ) ) {
		if ( pods_v( 'hidden', $field, false ) ) {
			$fields[ $k ]['type'] = 'hidden';
		} elseif ( pods_v( 'read_only', $field, false ) ) {
			$fields[ $k ]['readonly'] = true;
		}
	}
}

$submittable_fields = $fields;

foreach ( $submittable_fields as $k => $field ) {
	if ( pods_v( 'readonly', $field, false ) ) {
		unset( $submittable_fields[ $k ] );
	}
}

if ( ! isset( $thank_you_alt ) ) {
	$thank_you_alt = $thank_you;
}

$uri_hash   = wp_create_nonce( 'pods_uri_' . $_SERVER['REQUEST_URI'] );
$field_hash = wp_create_nonce( 'pods_fields_' . implode( ',', array_keys( $submittable_fields ) ) );

$uid = @session_id();

if ( is_user_logged_in() ) {
	$uid = 'user_' . get_current_user_id();
}

$nonce = wp_create_nonce( 'pods_form_' . $pod->pod . '_' . $uid . '_' . ( $duplicate ? 0 : $id ) . '_' . $uri_hash . '_' . $field_hash );

if ( isset( $_POST['_pods_nonce'] ) ) {
	$action = __( 'saved', 'pods' );

	if ( 'create' == pods_v( 'do', 'post', 'save' ) ) {
		$action = __( 'created', 'pods' );
	} elseif ( 'duplicate' == pods_v( 'do', 'get', 'save' ) ) {
		$action = __( 'duplicated', 'pods' );
	}

	try {
		$params = pods_unslash( (array) $_POST );
		$id     = $pod->api->process_form( $params, $pod, $fields, $thank_you );

		$message = sprintf( __( '<strong>Success!</strong> %s %s successfully.', 'pods' ), $obj->item, $action );

		if ( 0 < strlen( $pod->pod_data['detail_url'] ) ) {
			$message .= ' <a target="_blank" href="' . $pod->field( 'detail_url' ) . '">' . sprintf( __( 'View %s', 'pods' ), $obj->item ) . '</a>';
		}

		$error = sprintf( __( '<strong>Error:</strong> %s %s successfully.', 'pods' ), $obj->item, $action );

		if ( 0 < $id ) {
			echo $obj->message( $message );
		} else {
			echo $obj->error( $error );
		}
	} catch ( Exception $e ) {
		echo $obj->error( $e->getMessage() );
	}
} elseif ( isset( $_GET['do'] ) ) {
	$action = __( 'saved', 'pods' );

	if ( 'create' == pods_v( 'do', 'get', 'save' ) ) {
		$action = __( 'created', 'pods' );
	} elseif ( 'duplicate' == pods_v( 'do', 'get', 'save' ) ) {
		$action = __( 'duplicated', 'pods' );
	}

	$message = sprintf( __( '<strong>Success!</strong> %s %s successfully.', 'pods' ), $obj->item, $action );

	if ( 0 < strlen( $pod->pod_data['detail_url'] ) ) {
		$message .= ' <a target="_blank" href="' . $pod->field( 'detail_url' ) . '">' . sprintf( __( 'View %s', 'pods' ), $obj->item ) . '</a>';
	}

	$error = sprintf( __( '<strong>Error:</strong> %s not %s.', 'pods' ), $obj->item, $action );

	if ( 0 < $id ) {
		echo $obj->message( $message );
	} else {
		echo $obj->error( $error );
	}
}

if ( ! isset( $label ) ) {
	$label = __( 'Save', 'pods' );
}

$do = 'create';

if ( 0 < $id ) {
	if ( $duplicate ) {
		$do = 'duplicate';
	} else {
		$do = 'save';
	}
}
?>

<form action="" method="post" class="pods-submittable pods-form pods-form-pod-<?php echo $pod->pod; ?> pods-submittable-ajax">
<div class="pods-submittable-fields">
<?php echo Pods_Form::field( 'action', 'pods_admin', 'hidden' ); ?>
<?php echo Pods_Form::field( 'method', 'process_form', 'hidden' ); ?>
<?php echo Pods_Form::field( 'do', $do, 'hidden' ); ?>
<?php echo Pods_Form::field( '_pods_nonce', $nonce, 'hidden' ); ?>
<?php echo Pods_Form::field( '_pods_pod', $pod->pod, 'hidden' ); ?>
<?php echo Pods_Form::field( '_pods_id', ( $duplicate ? 0 : $id ), 'hidden' ); ?>
<?php echo Pods_Form::field( '_pods_uri', $uri_hash, 'hidden' ); ?>
<?php echo Pods_Form::field( '_pods_form', implode( ',', array_keys( $fields ) ), 'hidden' ); ?>
<?php echo Pods_Form::field( '_pods_location', $_SERVER['REQUEST_URI'], 'hidden' ); ?>

<?php
foreach ( $fields as $field ) {
	if ( 'hidden' != $field['type'] ) {
		continue;
	}

	echo Pods_Form::field( 'pods_field_' . $field['name'], $pod->field( array( 'name' => $field['name'], 'in_form' => true ) ), 'hidden' );
}
?>
<div id="poststuff" class="metabox-holder has-right-sidebar"> <!-- class "has-right-sidebar" preps for a sidebar... always present? -->
<div id="side-info-column" class="inner-sidebar">
	<?php
		/**
		 * Action that runs before the sidebar of the editor for an Advanced Content Type
		 *
		 * Occurs at the top of #side-info-column
		 *
		 * @param obj $pod Current Pods object.
		 *
		 * @since 2.4.1
		 */
		do_action( 'pods_act_editor_before_sidebar', $pod );
	?>

	<div id="side-sortables" class="meta-box-sortables ui-sortable">
		<!-- BEGIN PUBLISH DIV -->
		<div id="submitdiv" class="postbox">
			<div class="handlediv" title="Click to toggle"><br /></div>
			<h3 class="hndle"><span><?php _e( 'Manage', 'pods' ); ?></span></h3>

			<div class="inside">
				<div class="submitbox" id="submitpost">
					<?php
					if ( 0 < $id && ( isset( $pod->pod_data['fields']['created'] ) || isset( $pod->pod_data['fields']['modified'] ) || 0 < strlen( $pod->pod_data['detail_url'] ) ) ) {
						?>
						<div id="minor-publishing">
							<?php
							if ( 0 < strlen( $pod->pod_data['detail_url'] ) ) {
								?>
								<div id="minor-publishing-actions">
									<div id="preview-action">
										<a class="button" href="<?php echo $pod->field( 'detail_url' ); ?>" target="_blank"><?php echo sprintf( __( 'View %s', 'pods' ), $obj->item ); ?></a>
									</div>
									<div class="clear"></div>
								</div>
							<?php
							}

							if ( isset( $pod->pod_data['fields']['created'] ) || isset( $pod->pod_data['fields']['modified'] ) ) {
								?>
								<div id="misc-publishing-actions">
									<?php
									$datef = __( 'M j, Y @ G:i' );

									if ( isset( $pod->pod_data['fields']['created'] ) ) {
										$date = date_i18n( $datef, strtotime( $pod->field( 'created' ) ) );
										?>
										<div class="misc-pub-section curtime">
											<span id="timestamp"><?php _e( 'Created on', 'pods' ); ?>: <b><?php echo $date; ?></b></span>
										</div>
									<?php
									}

									if ( isset( $pod->pod_data['fields']['modified'] ) && $pod->display( 'created' ) != $pod->display( 'modified' ) ) {
										$date = date_i18n( $datef, strtotime( $pod->field( 'modified' ) ) );
										?>
										<div class="misc-pub-section curtime">
											<span id="timestamp"><?php _e( 'Last Modified', 'pods' ); ?>: <b><?php echo $date; ?></b></span>
										</div>
									<?php
									}
									?>
								</div>
							<?php
							}
							?>
						</div>
						<!-- /#minor-publishing -->
					<?php
					}
					?>

					<div id="major-publishing-actions">
						<?php
						if ( pods_is_admin( array( 'pods', 'pods_delete_' . $pod->pod ) ) && null !== $id && ! $duplicate && ! in_array( 'delete', $obj->actions_disabled ) && ! in_array( 'delete', $obj->actions_hidden ) ) {
							?>
							<div id="delete-action">
								<a class="submitdelete deletion" href="<?php echo pods_var_update( array( 'action' => 'delete' ) ) ?>" onclick="return confirm('You are about to permanently delete this item\n Choose \'Cancel\' to stop, \'OK\' to delete.');"><?php _e( 'Delete', 'pods' ); ?></a>
							</div>
							<!-- /#delete-action -->
						<?php } ?>

						<div id="publishing-action">
							<img class="waiting" src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" alt="" /> <input type="submit" name="publish" id="publish" class="button button-primary button-large" value="<?php echo esc_attr( $label ); ?>" accesskey="p" />
						</div>
						<!-- /#publishing-action -->

						<div class="clear"></div>
					</div>
					<!-- /#major-publishing-actions -->
				</div>
				<!-- /#submitpost -->
			</div>
			<!-- /.inside -->
		</div>
		<!-- /#submitdiv --><!-- END PUBLISH DIV --><!-- TODO: minor column fields -->
		<?php
		if ( 'settings' != $pod->pod_data['type'] && 'edit' == pods_v( 'action' ) && ! $duplicate && ! in_array( 'navigate', $obj->actions_disabled ) && ! in_array( 'navigate', $obj->actions_hidden ) ) {
			if ( ! isset( $singular_label ) ) {
				$singular_label = ucwords( str_replace( '_', ' ', $pod->pod_data['name'] ) );
			}

			$singular_label = pods_v( 'label', $pod->pod_data, $singular_label, true );
			$singular_label = pods_v( 'label_singular', $pod->pod_data, $singular_label, true );

			$prev = $pod->prev_id();
			$next = $pod->next_id();

			if ( 0 < $prev || 0 < $next ) {
				?>
				<div id="navigatediv" class="postbox">
					<?php
					/**
					 * Action that runs before the post navagiation in the editor for an Advanced Content Type
					 *
					 * Occurs at the top of #navigatediv
					 *
					 * @param obj $pod Current Pods object.
					 *
					 * @since 2.4.1
					 */
					do_action( 'pods_act_editor_before_navigation', $pod );
					?>

					<div class="handlediv" title="Click to toggle"><br /></div>
					<h3 class="hndle"><span><?php _e( 'Navigate', 'pods' ); ?></span></h3>

					<div class="inside">
						<div class="pods-admin" id="navigatebox">
							<div id="navigation-actions">
								<?php
								if ( 0 < $prev ) {
									?>
									<a class="previous-item" href="<?php echo pods_var_update( array( 'id' => $prev ), null, 'do' ); ?>"> <span>&laquo;</span>
										<?php echo sprintf( __( 'Previous %s', 'pods' ), $singular_label ); ?>
									</a>
								<?php
								}

								if ( 0 < $next ) {
									?>
									<a class="next-item" href="<?php echo pods_var_update( array( 'id' => $next ), null, 'do' ); ?>">
										<?php echo sprintf( __( 'Next %s', 'pods' ), $singular_label ); ?>
										<span>&raquo;</span> </a>
								<?php
								}
								?>

								<div class="clear"></div>
							</div>
							<!-- /#navigation-actions -->
						</div>
						<!-- /#navigatebox -->
					</div>
					<!-- /.inside -->
					<?php
						/**
						 * Action that runs after the post navagiation in the editor for an Advanced Content Type
						 *
						 * Occurs at the bottom of #navigatediv
						 *
						 * @param obj $pod Current Pods object.
						 *
						 * @since 2.4.1
						 */
						do_action( 'pods_act_editor_after_navigation', $pod );
					?>

				</div> <!-- /#navigatediv -->
			<?php
			}
		}
		?>
	</div>
	<!-- /#side-sortables -->
	<?php
		/**
		 * Action that runs after the sidebar of the editor for an Advanced Content Type
		 *
		 * Occurs at the bottom of #side-info-column
		 *
		 * @param obj $pod Current Pods object.
		 *
		 * @since 2.4.1
		 */
		do_action( 'pods_act_editor_after_sidebar', $pod );
	?>

</div>
<!-- /#side-info-column -->

<div id="post-body">
	<div id="post-body-content">
		<?php
		$more = false;

		$table_info = $pod->pod_data['table_info'];

		if ( $table_info['field_index'] != $table_info['field_id'] ) {
			foreach ( $fields as $k => $field ) {
				if ( $table_info['field_index'] != $field['name'] || 'text' != $field['type'] ) {
					continue;
				}

				$more  = true;
				$extra = '';

				$max_length = (int) pods_var( 'maxlength', $field, pods_v( $field['type'] . '_max_length', $field, 0 ), null, true );

				if ( 0 < $max_length ) {
					$extra .= ' maxlength="' . $max_length . '"';
				}
				?>
				<div id="titlediv">
					<?php
						/**
						 * Action that runs before the title field of the editor for an Advanced Content Type
						 *
						 * Occurs at the top of #titlediv
						 *
						 * @param obj $pod Current Pods object.
						 *
						 * @since 2.4.1
						 */
						do_action( 'pods_act_editor_before_title', $pod );
					?>
					<div id="titlewrap">
						<label class="hide-if-no-js screen-reader-text" id="title-prompt-text" for="title"><?php echo apply_filters( 'pods_enter_name_here', __( 'Enter name here', 'pods' ), $pod, $fields ); ?></label> <input type="text" name="pods_field_<?php echo $table_info['field_index']; ?>" data-name-clean="pods-field-<?php echo $table_info['field_index']; ?>" id="title" size="30" tabindex="1" value="<?php echo esc_attr( htmlspecialchars( $pod->index() ) ); ?>" class="pods-form-ui-field-name-pods-field-<?php echo $table_info['field_index']; ?>" autocomplete="off"<?php echo $extra; ?> />
					</div>
					<!-- /#titlewrap -->

					<div class="inside">
						<div id="edit-slug-box">
						</div>
						<!-- /#edit-slug-box -->
					</div>
					<!-- /.inside -->
					<?php
						/**
						 * Action that runs after the title field of the editor for an Advanced Content Type
						 *
						 * Occurs at the bottom of #titlediv
						 *
						 * @param obj $pod Current Pods object.
						 *
						 * @since 2.4.1
						 */
						do_action( 'pods_act_editor_after_title', $pod );
					?>

				</div>
				<!-- /#titlediv -->
				<?php
				unset( $fields[ $k ] );
			}
		}

		$groups = Pods_Init::$meta->groups_get( $pod->pod_data['type'], $pod->pod_data['name'] );

		if ( 0 < count( $groups ) ) {
			foreach ( $groups as $group ) {
				if ( empty( $group['fields'] ) ) {
					continue;
				}

				$hidden_fields = array();
				?>
				<div id="normal-sortables" class="meta-box-sortables ui-sortable">
					<?php
						/**
						 * Action that runs before the main fields metabox in the editor for an Advanced Content Type
						 *
						 * Occurs at the top of #normal-sortables
						 *
						 * @param obj $pod Current Pods object.
						 *
						 * @since 2.4.1
						 */
						do_action( 'pods_act_editor_before_metabox', $pod );
					?>

					<div id="pods-meta-box" class="postbox" style="">
						<div class="handlediv" title="Click to toggle"><br /></div>
						<h3 class="hndle">
									<span>
										<?php echo $group['label']; ?>
									</span>
						</h3>

						<?php echo Pods_Form::field( 'pods_meta', wp_create_nonce( 'pods_meta_pod' ), 'hidden' ); ?>

						<div class="inside">
							<table class="form-table pods-metabox">
								<tbody>
									<?php
									foreach ( $group['fields'] as $field ) {
										if ( false === Pods_Form::permission( $field['type'], $field['name'], $field, $group['fields'], $pod, $id ) ) {
											if ( pods_v( 'hidden', $field, false ) ) {
												$field['type'] = 'hidden';
											} else {
												continue;
											}
										} elseif ( ! pods_has_permissions( $field ) && pods_v( 'hidden', $field, false ) ) {
											$field['type'] = 'hidden';
										}

										$value = $pod->field( array( 'name' => $field['name'], 'in_form' => true ) );

										if ( 'hidden' == $field['type'] ) {
											$hidden_fields[] = array(
												'field' => $field,
												'value' => $value
											);

											continue;
										}

										if ( in_array( $field['type'], $block_field_types ) ) {
											?>
											<tr class="form-field pods-field <?php echo 'pods-form-ui-row-type-' . $field['type'] . ' pods-form-ui-row-name-' . Pods_Form::clean( $field['name'], true ); ?>">
												<td colspan="2">
													<?php echo Pods_Form::field( 'pods_field_' . $field['name'], $value, $field['type'], $field, $pod, $id ); ?>
												</td>
											</tr>
										<?php
										} else {
											?>
											<tr class="form-field pods-field <?php echo 'pods-form-ui-row-type-' . $field['type'] . ' pods-form-ui-row-name-' . Pods_Form::clean( $field['name'], true ); ?>">
												<th scope="row" valign="top"><?php echo Pods_Form::label( 'pods_field_' . $field['name'], $field['label'], $field['help'], $field ); ?></th>
												<td>
													<?php echo Pods_Form::field( 'pods_field_' . $field['name'], $value, $field['type'], $field, $pod, $id ); ?>
													<?php echo Pods_Form::comment( 'pods_field_' . $field['name'], $field['description'], $field ); ?>
												</td>
											</tr>
										<?php
										}
									}
									?>
								</tbody>
							</table>
						</div>
						<!-- /.inside -->
					</div>
					<!-- /#pods-meta-box -->
					<?php
						/**
						 * Action that runs after the main fields metabox in the editor for an Advanced Content Type
						 *
						 * Occurs at the bottom of #normal-sortables
						 *
						 * @param obj $pod Current Pods object.
						 *
						 * @since 2.4.1
						 */
						do_action( 'pods_act_editor_after_metabox', $pod );
					?>

				</div>
				<!-- /#normal-sortables -->
				<?php
				foreach ( $hidden_fields as $hidden_field ) {
					$field = $hidden_field['field'];

					echo Pods_Form::field( 'pods_meta_' . $field['name'], $hidden_field['value'], 'hidden' );
				}
			}
		}
		?>

		<!--<div id="advanced-sortables" class="meta-box-sortables ui-sortable">
		</div>
		 /#advanced-sortables -->

	</div>
	<!-- /#post-body-content -->

	<br class="clear" />
</div>
<!-- /#post-body -->

<br class="clear" />
</div>
<!-- /#poststuff -->
</div>
</form>
<!-- /#pods-record -->

<script type="text/javascript">
	if('undefined' == typeof ajaxurl) {
		var ajaxurl = '<?php echo admin_url( 'admin-ajax.php' ); ?>';
	}

	jQuery(function($) {
		$(document).Pods('validate');
		$(document).Pods('submit');
		$(document).Pods('dependency');
		$(document).Pods('confirm');
		$(document).Pods('exit_confirm');
	});

	var pods_admin_submit_callback = function(id) {
		id = parseInt(id);
		var thank_you = '<?php echo pods_slash( $thank_you ); ?>';
		var thank_you_alt = '<?php echo pods_slash( $thank_you_alt ); ?>';

		if('NaN' == id) {
			document.location = thank_you_alt.replace('X_ID_X',0);
		}
		else {
			document.location = thank_you.replace('X_ID_X',id);
		}
	}
</script>
