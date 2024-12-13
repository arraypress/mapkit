<?php
/**
 * MapKit Google Maps Service
 *
 * @package     ArrayPress/MapKit
 * @copyright   Copyright (c) 2024, ArrayPress Limited
 * @license     GPL2+
 */

declare( strict_types=1 );

namespace ArrayPress\MapKit\Providers;

/**
 * Class GoogleMaps
 *
 * Google Maps URL builder implementation.
 * Provides methods for building Google Maps URLs with various parameters
 * including markers, labels, map types, and navigation options.
 */
class Google extends Base {

	/**
	 * Base URL for Google Maps
	 *
	 * @var string
	 */
	private const BASE_URL = 'https://www.google.com/maps';

	/**
	 * Marker color for the map pin
	 *
	 * @var string|null
	 */
	private ?string $marker = null;

	/**
	 * Label text for the marker
	 *
	 * @var string|null
	 */
	private ?string $label = null;

	/**
	 * Travel mode for directions
	 *
	 * @var string|null
	 */
	private ?string $travel_mode = null;

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
	 * Add a marker to the map
	 *
	 * @param string $color Marker color (e.g., 'red', 'blue', 'green')
	 *
	 * @return self
	 */
	public function marker( string $color = 'red' ): self {
		$this->marker = $color;

		return $this;
	}

	/**
	 * Add a label to the marker
	 *
	 * @param string $text Label text
	 *
	 * @return self
	 */
	public function label( string $text ): self {
		$this->label = $text;

		return $this;
	}

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
	 * Set the travel mode for directions
	 *
	 * @param string $mode Travel mode ('driving', 'walking', 'bicycling', 'transit')
	 *
	 * @return self
	 */
	public function travel_mode( string $mode ): self {
		$valid_modes       = [ 'driving', 'walking', 'bicycling', 'transit' ];
		$this->travel_mode = in_array( $mode, $valid_modes ) ? $mode : 'driving';

		return $this;
	}

	/**
	 * Generate the Google Maps URL
	 *
	 * Generates a URL based on the set parameters. Will create either a search URL
	 * for simple locations or a directions URL if origin/destination are set.
	 *
	 * @return string|null The generated URL or null if required parameters are missing
	 */
	public function get_url(): ?string {
		// Check if we're generating a directions URL
		if ( isset( $this->origin, $this->destination ) ) {
			return $this->get_directions_url();
		}

		// Validate coordinates for location URL
		if ( ! $this->validate() ) {
			return null;
		}

		$params = [
			'api'   => '1',
			'query' => "{$this->latitude},{$this->longitude}"
		];

		if ( $this->zoom !== 12 ) {
			$params['zoom'] = $this->zoom;
		}

		if ( $this->map_type !== 'roadmap' ) {
			$params['maptype'] = $this->map_type;
		}

		if ( $this->marker ) {
			$marker = "color:{$this->marker}";
			if ( $this->label ) {
				$marker .= "|label:{$this->label}";
			}
			$params['marker'] = $marker;
		}

		return self::BASE_URL . '/search/?' . http_build_query( $params );
	}

	/**
	 * Generate a directions URL
	 *
	 * @return string The generated directions URL
	 */
	private function get_directions_url(): string {
		$params = [
			'api'         => '1',
			'origin'      => $this->origin,
			'destination' => $this->destination
		];

		if ( $this->travel_mode ) {
			$params['travel_mode'] = $this->travel_mode;
		}

		return self::BASE_URL . '/dir/?' . http_build_query( $params );
	}

}