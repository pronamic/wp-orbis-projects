<?php
/**
 * Billing method
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2026 Pronamic
 * @license   GPL-2.0-or-later
 * @package   Pronamic\Orbis\Projects
 */

namespace Pronamic\Orbis\Projects;

// phpcs:disable PHPCompatibility.Variables.ForbiddenThisUseContexts.OutsideObjectContext -- False positive, `$this` is valid in enum methods.

/**
 * Billing method enum
 *
 * Defines how a billable project is priced.
 */
enum BillingMethod: string {
	case TimeAndMaterials = 'time_and_materials';
	case FixedPrice       = 'fixed_price';

	/**
	 * Get billing method from a value.
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
			self::TimeAndMaterials => \_x( 'Time and materials', 'billing method', 'orbis-projects' ),
			self::FixedPrice       => \_x( 'Fixed price', 'billing method', 'orbis-projects' ),
		};
	}
}
