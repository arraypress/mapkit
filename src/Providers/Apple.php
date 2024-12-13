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
 * Class Apple
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
	 * Search query
	 *
	 * @var string|null
	 */
	private ?string $query = null;

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
	 * Address for location display
	 *
	 * @var string|null
	 */
	private ?string $address = null;

	/**
	 * Map type
	 *
	 * @var string
	 */
	protected string $map_type = 'm';

	/**
	 * Search location coordinates
	 *
	 * @var array|null
	 */
	private ?array $search_location = null;

	/**
	 * Search location span
	 *
	 * @var array|null
	 */
	private ?array $search_span = null;

	/**
	 * Near location hint
	 *
	 * @var array|null
	 */
	private ?array $near_location = null;

	/**
	 * Set a search query
	 *
	 * @param string     $query Search query or label
	 * @param array|null $near  Optional nearby location coordinates [lat, lon]
	 *
	 * @return self
	 */
	public function search( string $query, ?array $near = null ): self {
		$this->query = $query;
		if ( $near && count( $near ) === 2 ) {
			$this->near_location = $near;
		}

		return $this;
	}

	/**
	 * Set a specific address to display
	 *
	 * @param string $address Address string
	 *
	 * @return self
	 */
	public function address( string $address ): self {
		$this->address = $address;

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
	 * Set map type
	 *
	 * @param string $type Map type ('standard', 'satellite', 'hybrid', 'transit')
	 *
	 * @return self
	 */
	public function map_type( string $type ): self {
		$map_types      = [
			'standard'  => 'm',
			'satellite' => 'k',
			'hybrid'    => 'h',
			'transit'   => 'r'
		];
		$this->map_type = $map_types[ $type ] ?? 'm';

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
		$valid_types          = [ 'automobile', 'walking', 'transit' ];
		$this->transport_type = in_array( $type, $valid_types ) ? $type : 'automobile';

		return $this;
	}

	/**
	 * Set search location and optional span
	 *
	 * @param float      $latitude  Latitude
	 * @param float      $longitude Longitude
	 * @param float|null $lat_span  Optional latitude span
	 * @param float|null $lon_span  Optional longitude span
	 *
	 * @return self
	 */
	public function search_location( float $latitude, float $longitude, ?float $lat_span = null, ?float $lon_span = null ): self {
		$this->search_location = [ $latitude, $longitude ];
		if ( $lat_span !== null && $lon_span !== null ) {
			$this->search_span = [ $lat_span, $lon_span ];
		}

		return $this;
	}

	/**
	 * Generate the Apple Maps URL
	 *
	 * @return string|null The generated URL or null if required parameters are missing
	 */
	public function get_url(): ?string {
		$params = [];

		// Map type
		if ( $this->map_type !== 'm' ) {
			$params['t'] = $this->map_type;
		}

		// Handle different URL types
		if ( $this->destination ) {
			// Directions URL
			return $this->get_directions_url();
		}

		if ( $this->query ) {
			// Search URL
			$params['q'] = $this->query;

			if ( $this->near_location ) {
				$params['near'] = implode( ',', $this->near_location );
			}
		}

		if ( $this->validate() ) {
			// Coordinates URL with optional label
			$params['ll'] = "{$this->latitude},{$this->longitude}";
			if ( $this->zoom !== 12 ) {
				$params['z'] = $this->zoom;
			}
		}

		if ( $this->address && ! isset( $params['ll'] ) ) {
			// Address URL
			$params['address'] = $this->address;
		}

		if ( $this->search_location ) {
			// Search location
			$params['sll'] = implode( ',', $this->search_location );
			if ( $this->search_span ) {
				$params['sspn'] = implode( ',', $this->search_span );
			}
		}

		return empty( $params ) ? null : self::BASE_URL . '?' . http_build_query( $params );
	}

	/**
	 * Generate a directions URL
	 *
	 * @return string The generated directions URL
	 */
	private function get_directions_url(): string {
		$params = [
			'daddr'  => $this->destination,
			'dirflg' => $this->get_directions_flag()
		];

		if ( $this->map_type !== 'm' ) {
			$params['t'] = $this->map_type;
		}

		if ( $this->origin ) {
			$params['saddr'] = $this->origin;
		}

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