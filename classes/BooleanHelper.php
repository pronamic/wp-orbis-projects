<?php
/**
 * Boolean helper.
 *
 * @package Pronamic\Orbis\Projects
 */

namespace Pronamic\Orbis\Projects;

/**
 * Boolean helper.
 */
class BooleanHelper {
	/**
	 * Convert a value to a boolean.
	 *
	 * @param mixed $value Value.
	 * @return bool
	 */
	public static function from_mixed( $value ) {
		if ( is_bool( $value ) ) {
			return $value;
		}

		if ( ! is_scalar( $value ) ) {
			return false;
		}

		return in_array( strtolower( trim( (string) $value ) ), [ '1', 'true', 'on', 'yes' ], true );
	}
}
