<?php
/**
 * MapKit HERE Maps Service
 *
 * @package     ArrayPress/MapKit
 * @copyright   Copyright (c) 2024, ArrayPress Limited
 * @license     GPL2+
 */

declare( strict_types=1 );

namespace ArrayPress\MapKit\Providers;

use ArrayPress\MapKit\Abstracts\Provider;

/**
 * Class HereMaps
 *
 * HERE Maps URL builder implementation.
 * Provides methods for building HERE Maps URLs with various parameters
 * including location, routing, and map display options.
 */
class HereMaps extends Provider {

	/**
	 * Base URL for HERE Maps
	 *
	 * @var string
	 */
	private const BASE_URL = 'https://wego.here.com/';

	/**
	 * Starting point for directions
	 *
	 * @var string|null
	 */
	private ?string $origin = null;

	/**
	 * Destination point for directions
	 *
	 * @var string|null
	 */
	private ?string $destination = null;

	/**
	 * Transport mode
	 *
	 * @var string
	 */
	private string $transport_mode = 'drive';

	/**
	 * Set the starting point for directions
	 *
	 * @param string $address Starting address or location
	 *
	 * @return self
	 */
	public function from( string $address ): self {
		$this->origin = $address;

		return $this;
	}

	/**
	 * Set the destination point for directions
	 *
	 * @param string $address Destination address or location
	 *
	 * @return self
	 */
	public function to( string $address ): self {
		$this->destination = $address;

		return $this;
	}

	/**
	 * Set the transport mode
	 *
	 * @param string $mode Transport mode ('drive', 'walk', 'transit')
	 *
	 * @return self
	 */
	public function transport_mode( string $mode ): self {
		$valid_modes          = [ 'drive', 'walk', 'transit' ];
		$this->transport_mode = in_array( $mode, $valid_modes ) ? $mode : 'drive';

		return $this;
	}

	/**
	 * Generate the HERE Maps URL
	 *
	 * @return string|null The generated URL or null if required parameters are missing
	 */
	public function get_url(): ?string {
		if ( isset( $this->origin, $this->destination ) ) {
			return $this->get_directions_url();
		}

		if ( $this->validate() ) {
			return $this->get_location_url();
		}

		return null;
	}

	/**
	 * Generate a location URL
	 *
	 * @return string The generated location URL
	 */
	private function get_location_url(): string {
		return sprintf(
			'%sdirections/%f,%f',
			self::BASE_URL,
			$this->latitude,
			$this->longitude
		);
	}

	/**
	 * Generate a directions URL
	 *
	 * @return string The generated directions URL
	 */
	private function get_directions_url(): string {
		return sprintf(
			'%sdirections/%s/%s/%s',
			self::BASE_URL,
			urlencode( $this->origin ),
			urlencode( $this->destination ),
			urlencode( $this->transport_mode )
		);
	}

}