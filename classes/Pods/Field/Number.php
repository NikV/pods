<?php

/**
 * @package Pods\Fields
 */
class Pods_Field_Number extends
	Pods_Field {

	/**
	 * Field Type Group
	 *
	 * @var string
	 * @since 2.0
	 */
	public static $group = 'Number';

	/**
	 * Field Type Identifier
	 *
	 * @var string
	 * @since 2.0
	 */
	public static $type = 'number';

	/**
	 * Field Type Label
	 *
	 * @var string
	 * @since 2.0
	 */
	public static $label = 'Plain Number';

	/**
	 * Field Type Preparation
	 *
	 * @var string
	 * @since 2.0
	 */
	public static $prepare = '%d';

	/**
	 * {@inheritDocs}
	 */
	public function __construct() {

	}

	/**
	 * {@inheritDocs}
	 */
	public function options() {
		$options = array(
			self::$type . '_repeatable'  => array(
				'label'             => __( 'Repeatable Field', 'pods' ),
				'default'           => 0,
				'type'              => 'boolean',
				'help'              => __( 'Making a field repeatable will add controls next to the field which allows users to Add/Remove/Reorder additional values. These values are saved in the database as an array, so searching and filtering by them may require further adjustments".', 'pods' ),
				'boolean_yes_label' => '',
				'dependency'        => true,
				'developer_mode'    => true
			),
			self::$type . '_format_type' => array(
				'label'      => __( 'Input Type', 'pods' ),
				'default'    => 'number',
				'type'       => 'pick',
				'data'       => array(
					'number' => __( 'Freeform Number', 'pods' ),
					'slider' => __( 'Slider', 'pods' )
				),
				'dependency' => true
			),
			self::$type . '_format'      => array(
				'label'   => __( 'Format', 'pods' ),
				'default' => apply_filters( 'pods_form_ui_field_number_format_default', 'i18n' ),
				'type'    => 'pick',
				'data'    => array(
					'i18n'     => __( 'Localized Default', 'pods' ),
					'9,999.99' => '1,234.00',
					'9.999,99' => '1.234,00',
					'9 999,99' => '1 234,00',
					'9999.99'  => '1234.00',
					'9999,99'  => '1234,00'
				)
			),
			self::$type . '_decimals'    => array(
				'label'   => __( 'Decimals', 'pods' ),
				'default' => 0,
				'type'    => 'number',
				'help'    => __( 'Maximum allowed is 30 decimals', 'pods' )
			),
			self::$type . '_step'        => array(
				'label'      => __( 'Slider Increment (Step)', 'pods' ),
				'depends-on' => array( self::$type . '_format_type' => 'slider' ),
				'default'    => 1,
				'type'       => 'text'
			),
			self::$type . '_min'         => array(
				'label'      => __( 'Minimum Number', 'pods' ),
				'depends-on' => array( self::$type . '_format_type' => 'slider' ),
				'default'    => 0,
				'type'       => 'text'
			),
			self::$type . '_max'         => array(
				'label'      => __( 'Maximum Number', 'pods' ),
				'depends-on' => array( self::$type . '_format_type' => 'slider' ),
				'default'    => 100,
				'type'       => 'text'
			),
			self::$type . '_max_length'  => array(
				'label'   => __( 'Maximum Length', 'pods' ),
				'default' => 12,
				'type'    => 'number',
				'help'    => __( 'Maximum allowed is 64 digits', 'pods' )
			)
			/*,
						self::$type . '_size' => array(
							'label' => __( 'Field Size', 'pods' ),
							'default' => 'medium',
							'type' => 'pick',
							'data' => array(
								'small' => __( 'Small', 'pods' ),
								'medium' => __( 'Medium', 'pods' ),
								'large' => __( 'Large', 'pods' )
							)
						)*/
		);

		return $options;
	}

	/**
	 * {@inheritDocs}
	 */
	public function schema( $options = null ) {
		$length = (int) pods_v( self::$type . '_max_length', $options, 12, true );

		if ( $length < 1 || 64 < $length ) {
			$length = 64;
		}

		$decimals = (int) pods_v( self::$type . '_decimals', $options, 0, true );

		if ( $decimals < 1 ) {
			$decimals = 0;
		} elseif ( 30 < $decimals ) {
			$decimals = 30;
		}

		if ( $length < $decimals ) {
			$decimals = $length;
		}

		$schema = 'DECIMAL(' . $length . ',' . $decimals . ')';

		return $schema;
	}

	/**
	 * {@inheritDocs}
	 */
	public function prepare( $options = null ) {
		$format = self::$prepare;

		$length = (int) pods_v( self::$type . '_max_length', $options, 12, true );

		if ( $length < 1 || 64 < $length ) {
			$length = 64;
		}

		$decimals = (int) pods_v( self::$type . '_decimals', $options, 0, true );

		if ( $decimals < 1 ) {
			$decimals = 0;
		} elseif ( 30 < $decimals ) {
			$decimals = 30;
		}

		if ( $length < $decimals ) {
			$decimals = $length;
		}

		if ( 0 < $decimals ) {
			$format = '%01.' . $decimals . 'f';
		} else {
			$format = '%d';
		}

		return $format;
	}

	/**
	 * {@inheritDocs}
	 */
	public function display( $value = null, $name = null, $options = null, $pod = null, $id = null ) {
		$value = $this->format( $value, $name, $options, $pod, $id );

		return $value;
	}

	/**
	 * {@inheritDocs}
	 */
	public function input( $name, $value = null, $options = null, $pod = null, $id = null ) {
		$form_field_type = Pods_Form::$field_type;

		if ( is_array( $value ) ) {
			$value = implode( '', $value );
		}

		if ( 'slider' == pods_v( self::$type . '_format_type', $options, 'number' ) ) {
			$field_type = 'slider';
		} else {
			$field_type = 'number';
		}

		if ( isset( $options['name'] ) && false === Pods_Form::permission( self::$type, $options['name'], $options, null, $pod, $id ) ) {
			if ( pods_v( 'read_only', $options, false ) ) {
				$options['readonly'] = true;

				$field_type = 'text';

				$value = $this->format( $value, $name, $options, $pod, $id );
			} else {
				return;
			}
		} elseif ( ! pods_has_permissions( $options ) && pods_v( 'read_only', $options, false ) ) {
			$options['readonly'] = true;

			$field_type = 'text';

			$value = $this->format( $value, $name, $options, $pod, $id );
		}

		pods_view( PODS_DIR . 'ui/fields/' . $field_type . '.php', compact( array_keys( get_defined_vars() ) ) );
	}

	/**
	 * {@inheritDocs}
	 */
	public function regex( $value = null, $name = null, $options = null, $pod = null, $id = null ) {
		global $wp_locale;

		if ( '9.999,99' == pods_v( self::$type . '_format', $options ) ) {
			$thousands = '.';
			$dot       = ',';
		} elseif ( '9,999.99' == pods_v( self::$type . '_format', $options ) ) {
			$thousands = ',';
			$dot       = '.';
		} elseif ( '9 999,99' == pods_v( self::$type . '_format', $options ) ) {
			$thousands = ' ';
			$dot       = ',';
		} elseif ( '9999.99' == pods_v( self::$type . '_format', $options ) ) {
			$thousands = '';
			$dot       = '.';
		} elseif ( '9999,99' == pods_v( self::$type . '_format', $options ) ) {
			$thousands = '';
			$dot       = ',';
		} else {
			$thousands = $wp_locale->number_format['thousands_sep'];
			$dot       = $wp_locale->number_format['decimal_point'];
		}

		return '\-*[0-9\\' . implode( '\\', array_filter( array( $dot, $thousands ) ) ) . ']+';
	}

	/**
	 * {@inheritDocs}
	 */
	public function validate( $value, $name = null, $options = null, $fields = null, $pod = null, $id = null, $params = null ) {
		global $wp_locale;

		if ( '9.999,99' == pods_v( self::$type . '_format', $options ) ) {
			$thousands = '.';
			$dot       = ',';
		} elseif ( '9,999.99' == pods_v( self::$type . '_format', $options ) ) {
			$thousands = ',';
			$dot       = '.';
		} elseif ( '9 999,99' == pods_v( self::$type . '_format', $options ) ) {
			$thousands = ' ';
			$dot       = ',';
		} elseif ( '9999.99' == pods_v( self::$type . '_format', $options ) ) {
			$thousands = ',';
			$dot       = '.';
		} elseif ( '9999,99' == pods_v( self::$type . '_format', $options ) ) {
			$thousands = '.';
			$dot       = ',';
		} else {
			$thousands = $wp_locale->number_format['thousands_sep'];
			$dot       = $wp_locale->number_format['decimal_point'];
		}

		$check = str_replace( array( $thousands, $dot ), array( '', '.' ), $value );

		$check = preg_replace( '/[0-9\.\-]/', '', $check );

		$label = pods_var( 'label', $options, ucwords( str_replace( '_', ' ', $name ) ) );

		if ( 0 < strlen( $check ) ) {
			return sprintf( __( '%s is not numeric', 'pods' ), $label );
		}

		return true;
	}

	/**
	 * {@inheritDocs}
	 */
	public function pre_save( $value, $id = null, $name = null, $options = null, $fields = null, $pod = null, $params = null ) {
		global $wp_locale;

		if ( '9.999,99' == pods_v( self::$type . '_format', $options ) ) {
			$thousands = '.';
			$dot       = ',';
		} elseif ( '9,999.99' == pods_v( self::$type . '_format', $options ) ) {
			$thousands = ',';
			$dot       = '.';
		} elseif ( '9 999,99' == pods_v( self::$type . '_format', $options ) ) {
			$thousands = ' ';
			$dot       = ',';
		} elseif ( '9999.99' == pods_v( self::$type . '_format', $options ) ) {
			$thousands = ',';
			$dot       = '.';
		} elseif ( '9999,99' == pods_v( self::$type . '_format', $options ) ) {
			$thousands = '.';
			$dot       = ',';
		} else {
			$thousands = $wp_locale->number_format['thousands_sep'];
			$dot       = $wp_locale->number_format['decimal_point'];
		}

		$value = str_replace( array( $thousands, $dot ), array( '', '.' ), $value );

		$value = preg_replace( '/[^0-9\.\-]/', '', $value );

		$length = (int) pods_v( self::$type . '_max_length', $options, 12, true );

		if ( $length < 1 || 64 < $length ) {
			$length = 64;
		}

		$decimals = (int) pods_v( self::$type . '_decimals', $options, 0, true );

		if ( $decimals < 1 ) {
			$decimals = 0;
		} elseif ( 30 < $decimals ) {
			$decimals = 30;
		}

		if ( $length < $decimals ) {
			$decimals = $length;
		}

		$value = number_format( (float) $value, $decimals, '.', '' );

		return $value;
	}

	/**
	 * {@inheritDocs}
	 */
	public function ui( $id, $value, $name = null, $options = null, $fields = null, $pod = null ) {
		return $this->display( $value, $name, $options, $pod, $id );
	}

	/**
	 * Reformat a number to the way the value of the field is displayed
	 *
	 * @param mixed  $value
	 * @param string $name
	 * @param array  $options
	 * @param array  $pod
	 * @param int    $id
	 *
	 * @return string
	 * @since 2.0
	 */
	public function format( $value = null, $name = null, $options = null, $pod = null, $id = null ) {
		global $wp_locale;

		if ( '9.999,99' == pods_v( self::$type . '_format', $options ) ) {
			$thousands = '.';
			$dot       = ',';
		} elseif ( '9,999.99' == pods_v( self::$type . '_format', $options ) ) {
			$thousands = ',';
			$dot       = '.';
		} elseif ( '9 999,99' == pods_v( self::$type . '_format', $options ) ) {
			$thousands = ' ';
			$dot       = ',';
		} elseif ( '9999.99' == pods_v( self::$type . '_format', $options ) ) {
			$thousands = '';
			$dot       = '.';
		} elseif ( '9999,99' == pods_v( self::$type . '_format', $options ) ) {
			$thousands = '';
			$dot       = ',';
		} else {
			$thousands = $wp_locale->number_format['thousands_sep'];
			$dot       = $wp_locale->number_format['decimal_point'];
		}

		$length = (int) pods_v( self::$type . '_max_length', $options, 12, true );

		if ( $length < 1 || 64 < $length ) {
			$length = 64;
		}

		$decimals = (int) pods_v( self::$type . '_decimals', $options, 0, true );

		if ( $decimals < 1 ) {
			$decimals = 0;
		} elseif ( 30 < $decimals ) {
			$decimals = 30;
		}

		if ( $length < $decimals ) {
			$decimals = $length;
		}

		if ( 'i18n' == pods_v( self::$type . '_format', $options ) ) {
			$value = number_format_i18n( (float) $value, $decimals );
		} else {
			$value = number_format( (float) $value, $decimals, $dot, $thousands );
		}

		return $value;
	}
}
