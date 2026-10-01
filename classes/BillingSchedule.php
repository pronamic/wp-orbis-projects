<?php
/**
 * Billing schedule
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2026 Pronamic
 * @license   GPL-2.0-or-later
 * @package   Pronamic\Orbis\Projects
 */

namespace Pronamic\Orbis\Projects;

// phpcs:disable PHPCompatibility.Variables.ForbiddenThisUseContexts.OutsideObjectContext -- False positive, `$this` is valid in enum methods.

/**
 * Billing schedule enum
 *
 * Defines when the price of a fixed price project is billed.
 */
enum BillingSchedule: string {
	case InAdvance    = 'in_advance';
	case InArrears    = 'in_arrears';
	case ProRata      = 'pro_rata';
	case Flexible     = 'flexible';

	/**
	 * Get billing schedule from a value.
	 *
	 * @param mixed $value Value.
	 * @return self|null
	 */
	public static function from_value( $value ): ?self {
		return \is_scalar( $value ) ? self::tryFrom( (string) $value ) : null;
	}

	/**
	 * Get label.
	 *
	 * @return string
	 */
	public function label(): string {
		return match ( $this ) {
			self::InAdvance    => \_x( 'In advance', 'billing schedule', 'orbis-projects' ),
			self::InArrears    => \_x( 'In arrears', 'billing schedule', 'orbis-projects' ),
			self::ProRata      => \_x( 'Pro rata', 'billing schedule', 'orbis-projects' ),
			self::Flexible     => \_x( 'Flexible', 'billing schedule', 'orbis-projects' ),
		};
	}
}
