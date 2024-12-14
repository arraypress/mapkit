<?php
/**
 * MapKit Waze Service
 *
 * @package     ArrayPress/MapKit
 * @copyright   Copyright (c) 2024, ArrayPress Limited
 * @license     GPL2+
 */

declare( strict_types=1 );

namespace ArrayPress\MapKit\Providers;

use ArrayPress\MapKit\Abstracts\Provider;

/**
 * Class Waze
 *
 * Waze URL builder implementation.
 * Provides methods for building Waze URLs with various parameters
 * including navigation, locations, and live routing options.
 */
class Waze extends Provider {

	/**
	 * Base URL for Waze web
	 *
	 * @var string
	 */
	private const WEB_URL = 'https://www.waze.com/live-map';

	/**
	 * Base URL for Waze mobile app
	 *
	 * @var string
	 */
	private const APP_URL = 'https://www.waze.com/ul';

	/**
	 * Destination address or name
	 *
	 * @var string|null
	 */
	private ?string $destination = null;

	/**
	 * Navigation mode
	 *
	 * @var string
	 */
	private string $navigate = 'yes';

	/**
	 * Set the destination for navigation
	 *
	 * @param string $address Destination address or location name
	 *
	 * @return self
	 */
	public function to( string $address ): self {
		$this->destination = $address;
		return $this;
	}

	/**
	 * Set whether to automatically start navigation
	 *
	 * @param bool $auto_navigate Whether to start navigation automatically
	 *
	 * @return self
	 */
	public function auto_navigate( bool $auto_navigate = true ): self {
		$this->navigate = $auto_navigate ? 'yes' : 'no';
		return $this;
	}

	/**
	 * Generate the Waze URL
	 *
	 * @param bool $useApp Whether to generate a URL for the mobile app (true) or web (false)
	 *
	 * @return string|null The generated URL or null if required parameters are missing
	 */
	public function get_url( bool $useApp = true ): ?string {
		if ( isset( $this->destination ) ) {
			return $this->get_navigation_url( $useApp );
		}

		if ( $this->validate() ) {
			return $this->get_location_url( $useApp );
		}

		return null;
	}

	/**
	 * Generate a location URL
	 *
	 * @param bool $useApp Whether to generate a URL for the mobile app
	 *
	 * @return string The generated location URL
	 */
	private function get_location_url( bool $useApp ): string {
		if ( $useApp ) {
			return sprintf(
				'%s?ll=%f,%f&navigate=%s',
				self::APP_URL,
				$this->latitude,
				$this->longitude,
				$this->navigate
			);
		}

		return sprintf(
			'%s/directions?to=ll.%f,%f',
			self::WEB_URL,
			$this->latitude,
			$this->longitude
		);
	}

	/**
	 * Generate a navigation URL
	 *
	 * @param bool $useApp Whether to generate a URL for the mobile app
	 *
	 * @return string The generated navigation URL
	 */
	private function get_navigation_url( bool $useApp ): string {
		if ( $useApp ) {
			return sprintf(
				'%s?q=%s&navigate=%s',
				self::APP_URL,
				urlencode( $this->destination ),
				$this->navigate
			);
		}

		return sprintf(
			'%s/directions?q=%s',
			self::WEB_URL,
			urlencode( $this->destination )
		);
	}

}