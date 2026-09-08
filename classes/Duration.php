<?php
/**
 * Duration
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2026 Pronamic
 * @license   GPL-2.0-or-later
 * @package   Pronamic\Orbis\Projects
 */

namespace Pronamic\Orbis\Projects;

/**
 * Duration value object.
 */
class Duration {
	/**
	 * Seconds.
	 *
	 * @var int
	 */
	private int $seconds;

	/**
	 * Construct.
	 *
	 * @param int $seconds Seconds.
	 */
	public function __construct( int $seconds = 0 ) {
		$this->seconds = $seconds;
    }

	/**
	 * Create duration from seconds.
	 *
	 * @param int|float|string $seconds Seconds.
	 * @return self
	 * @throws \InvalidArgumentException When seconds are not an integer.
	 */
	public static function from_seconds( int|float|string $seconds ) {
		if ( \is_float( $seconds ) ) {
			$seconds = (int) $seconds;
		}

		if ( \is_string( $seconds ) ) {
			if ( ! \ctype_digit( $seconds ) ) {
				throw new \InvalidArgumentException( 'Seconds must be an integer.' );
			}

			$seconds = (int) $seconds;
		}

		return new self( $seconds );
	}

	/**
	 * Try to create duration from seconds.
	 *
	 * @param mixed $seconds Seconds.
	 * @return self|null
	 */
	public static function try_from_seconds( $seconds ) {
		try {
			return self::from_seconds( $seconds );
		} catch ( \InvalidArgumentException | \TypeError ) {
			return null;
		}
	}

	/**
	 * Create duration from a string.
	 *
	 * @param string $value Duration as decimal hours or hours and minutes.
	 * @return self|null
	 */
	public static function from_string( $value ) {
		$seconds = 0;

		$part_hours   = $value;
		$part_minutes = null;

		$position_colon = \strpos( $value, ':' );

		if ( false !== $position_colon ) {
			$part_hours   = \substr( $value, 0, $position_colon );
			$part_minutes = \substr( $value, $position_colon + 1 );
		}

		$has_duration = \is_numeric( $part_hours ) || \is_numeric( $part_minutes );

		if ( ! $has_duration ) {
			return null;
		}

		if ( \is_numeric( $part_hours ) ) {
			$seconds += $part_hours * HOUR_IN_SECONDS;
		}

		if ( \is_numeric( $part_minutes ) ) {
			$seconds += $part_minutes * MINUTE_IN_SECONDS;
		}

		return self::from_seconds( $seconds );
	}

	/**
	 * Get seconds.
	 *
	 * @return int
	 */
	public function get_seconds() {
		return $this->seconds;
	}

	/**
	 * Format duration.
	 *
	 * @param string $format Format.
	 * @return string
	 */
	public function format( $format = 'HH:MM' ) {
		$hours   = \floor( $this->seconds / HOUR_IN_SECONDS );
		$minutes = \floor( ( $this->seconds - ( $hours * HOUR_IN_SECONDS ) ) / MINUTE_IN_SECONDS );
		$seconds = \floor( $this->seconds % MINUTE_IN_SECONDS );

		$search = [
			'HH',
			'H',
			'MM',
			'M',
			'SS',
			'S',
		];

		$replace = [
			\sprintf( '%02d', $hours ),
			$hours,
			\sprintf( '%02d', $minutes ),
			$minutes,
			\sprintf( '%02d', $seconds ),
			$seconds,
		];

		return \str_replace( $search, $replace, $format );
	}

	/**
	 * String representation.
	 *
	 * @return string
	 */
	public function __toString() {
		return $this->format();
	}
}
