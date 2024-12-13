<?php
/**
 * MapKit Apple Maps Service
 *
 * @package     ArrayPress/MapKit
 * @copyright   Copyright (c) 2024, ArrayPress Limited
 * @license     GPL2+
 */

declare( strict_types=1 );

namespace ArrayPress\MapKit\Providers;

/**
 * Class AppleMaps
 *
 * Apple Maps URL builder implementation.
 * Provides methods for building Apple Maps URLs with various parameters
 * including search queries, directions, and map types.
 */
class Apple extends Base {

	/**
	 * Base URL for Apple Maps
	 *
	 * @var string
	 */
	private const BASE_URL = 'https://maps.apple.com/';

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
	 * Transportation type for directions
	 *
	 * @var string
	 */
	private string $transport_type = 'automobile';

	/**
	 * Search query for location search
	 *
	 * @var string|null
	 */
	private ?string $search_query = null;

	/**
	 * Map display mode
	 *
	 * @var string
	 */
	private string $map_mode = 'standard';

	/**
	 * Set the search query
	 *
	 * @param string $query Location or business name to search for
	 *
	 * @return self
	 */
	public function search( string $query ): self {
		$this->search_query = $query;
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
	 * Set the map display mode
	 *
	 * @param string $mode Map display mode ('standard', 'satellite', 'hybrid', 'transit')
	 *
	 * @return self
	 */
	public function display_mode( string $mode ): self {
		$valid_modes = ['standard', 'satellite', 'hybrid', 'transit'];
		$this->map_mode = in_array( $mode, $valid_modes ) ? $mode : 'standard';
		return $this;
	}

	/**
	 * Set the transportation type
	 *
	 * @param string $type Transport type ('automobile', 'walking', 'transit')
	 *
	 * @return self
	 */
	public function transport_type( string $type ): self {
		$valid_types = ['automobile', 'walking', 'transit'];
		$this->transport_type = in_array( $type, $valid_types ) ? $type : 'automobile';
		return $this;
	}

	/**
	 * Generate the Apple Maps URL
	 *
	 * Generates a URL based on the set parameters. Will create either a search URL,
	 * coordinates URL, or a directions URL depending on the parameters set.
	 *
	 * @return string|null The generated URL or null if required parameters are missing
	 */
	public function get_url(): ?string {
		// Check if we're generating a directions URL
		if ( isset( $this->origin, $this->destination ) ) {
			return $this->get_directions_url();
		}

		// Check if we're generating a search URL
		if ( isset( $this->search_query ) ) {
			return $this->get_search_url();
		}

		// Fall back to coordinates URL if available
		if ( $this->validate() ) {
			return $this->get_coordinates_url();
		}

		return null;
	}

	/**
	 * Generate a coordinates-based URL
	 *
	 * @return string The generated coordinates URL
	 */
	private function get_coordinates_url(): string {
		$params = [
			'll'     => "{$this->latitude},{$this->longitude}",
			'z'      => $this->zoom,
			't'      => $this->map_mode,
		];

		return self::BASE_URL . '?' . http_build_query( $params );
	}

	/**
	 * Generate a search URL
	 *
	 * @return string The generated search URL
	 */
	private function get_search_url(): string {
		$params = [
			'q' => $this->search_query,
			't' => $this->map_mode,
		];

		return self::BASE_URL . '?' . http_build_query( $params );
	}

	/**
	 * Generate a directions URL
	 *
	 * @return string The generated directions URL
	 */
	private function get_directions_url(): string {
		$params = [
			'saddr'      => $this->origin,
			'daddr'      => $this->destination,
			'dirflg'     => $this->get_directions_flag(),
			't'          => $this->map_mode,
		];

		return self::BASE_URL . '?' . http_build_query( $params );
	}

	/**
	 * Get the directions flag based on transport type
	 *
	 * @return string The directions flag
	 */
	private function get_directions_flag(): string {
		switch ( $this->transport_type ) {
			case 'walking':
				return 'w';
			case 'transit':
				return 'r';
			default:
				return 'd';
		}
	}
}