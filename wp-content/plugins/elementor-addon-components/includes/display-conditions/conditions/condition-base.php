<?php
/**
 * Class: Condition
 *
 * Description:
 *
 * @since 2.1.7
 */

namespace EACCustomWidgets\Includes\DisplayConditions\Conditions;

/**
 * Abstract Class Condition.
 */
abstract class Condition_Base {

	/**
	 * Get Controls Options.
	 *
	 * @access public
	 * @since 2.1.7
	 *
	 * @return array|void  controls options
	 */
	public function get_target_control() {}

	/**
	 * Le nom de la class namespace inclus
	 *
	 * @access public
	 * @since 2.1.7
	 *
	 * @return string|void
	 */
	public function get_called_classname() {}

	/**
	 * Compare Condition Value.
	 *
	 * @access public
	 * @since 2.1.7
	 *
	 * @param array       $settings element settings.
	 * @param string      $operator condition operator.
	 * @param string      $value    condition value.
	 * @param string|bool $tz        time zone.
	 *
	 * @return bool|void
	 */
	public function check( $settings, $value, $operateur, $tz ) {}

}
