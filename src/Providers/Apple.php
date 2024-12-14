<?php
/**
 * MapKit Apple Maps Service
 *
 * This class provides a fluent interface for building Apple Maps URLs. It supports various features including:
 * - Coordinate-based locations with zoom levels
 * - Search queries with optional location context
 * - Directions with multiple transportation modes
 * - Map type selection (standard, satellite, hybrid, transit)
 * - Location markers/pins
 * - Language preferences
 * - Regional settings
 *
 * @package     ArrayPress/MapKit
 * @copyright   Copyright (c) 2024, ArrayPress Limited
 * @license     GPL2+
 */

declare( strict_types=1 );

namespace ArrayPress\MapKit\Providers;

use ArrayPress\MapKit\Abstracts\Provider;

class Apple extends Provider {

	/**
	 * Base URL for Apple Maps
	 *
	 * The base URL used for all Apple Maps links.
	 *
	 * @var string
	 */
	private const BASE_URL = 'https://maps.apple.com/';

	/**
	 * Search query string
	 *
	 * The search term or query to look up on the map.
	 *
	 * @var string|null
	 */
	protected ?string $query = null;

	/**
	 * Starting point for directions
	 *
	 * The origin address or location for route calculations.
	 *
	 * @var string|null
	 */
	protected ?string $origin = null;

	/**
	 * Destination point for directions
	 *
	 * The destination address or location for route calculations.
	 *
	 * @var string|null
	 */
	protected ?string $destination = null;

	/**
	 * Transportation type for directions
	 *
	 * The mode of transportation to use for route calculations.
	 * Valid values: 'automobile', 'walking', 'transit', 'bicycle'
	 *
	 * @var string
	 */
	protected string $transport_type = 'automobile';

	/**
	 * Address for location display
	 *
	 * A specific address to display on the map.
	 *
	 * @var string|null
	 */
	protected ?string $address = null;

	/**
	 * Map display type
	 *
	 * The visual style of the map.
	 * Valid values: 'standard', 'satellite', 'hybrid', 'transit'
	 *
	 * @var string
	 */
	protected string $map_type = 'standard';

	/**
	 * Search location coordinates
	 *
	 * Array containing [latitude, longitude] for search context.
	 *
	 * @var array|null
	 */
	protected ?array $search_location = null;

	/**
	 * Search location span
	 *
	 * Array containing [latitude_span, longitude_span] defining the search area size.
	 *
	 * @var array|null
	 */
	protected ?array $search_span = null;

	/**
	 * Near location hint
	 *
	 * Array containing [latitude, longitude] for location context.
	 *
	 * @var array|null
	 */
	protected ?array $near_location = null;

	/**
	 * Pin location coordinates
	 *
	 * Array containing [latitude, longitude] for placing a map marker.
	 *
	 * @var array|null
	 */
	protected ?array $pin_location = null;

	/**
	 * Language preference
	 *
	 * ISO 639-1 language code for map labels and interface.
	 * Example: 'en' for English, 'es' for Spanish.
	 *
	 * @var string|null
	 */
	protected ?string $language = null;

	/**
	 * Region setting
	 *
	 * ISO 3166-1 alpha-2 country code for regional preferences.
	 * Example: 'US' for United States, 'GB' for United Kingdom.
	 *
	 * @var string|null
	 */
	protected ?string $region = null;

	/**
	 * Maximum allowed zoom level
	 *
	 * Apple Maps supports zoom levels from 1 to 21.
	 * This overrides the base class maximum zoom level.
	 *
	 * @var int
	 */
	protected int $max_zoom = 21;

	/**
	 * Map type translation array
	 *
	 * Converts human-readable map types to Apple Maps URL parameters:
	 * - 'm' = standard map view
	 * - 'k' = satellite view
	 * - 'h' = hybrid view
	 * - 'r' = transit view
	 *
	 * @var array
	 */
	private array $map_type_codes = [
		'standard'  => 'm',
		'satellite' => 'k',
		'hybrid'    => 'h',
		'transit'   => 'r'
	];

	/**
	 * Set a search query
	 *
	 * Defines a location or business to search for on the map.
	 * Optionally accepts nearby coordinates to provide search context.
	 *
	 * @param string     $query Search query or label (e.g., "Coffee shops", "Central Park")
	 * @param array|null $near  Optional nearby location coordinates [latitude, longitude]
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
	 * Specifies an address to show on the map. This is different from a search
	 * as it expects a properly formatted address string.
	 *
	 * @param string $address Full address string (e.g., "123 Main St, City, State")
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
	 * Specifies the starting location for route calculations.
	 *
	 * @param string $address Starting address or location name
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
	 * Specifies the destination location for route calculations.
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
	 * Set map type
	 *
	 * Changes the visual style of the map display.
	 *
	 * @param string $type Map type ('standard', 'satellite', 'hybrid', 'transit')
	 *
	 * @return self
	 */
	public function map_type( string $type ): self {
		if ( isset( $this->map_type_codes[ $type ] ) ) {
			$this->map_type = $type;
		}

		return $this;
	}

	/**
	 * Set the transportation type
	 *
	 * Specifies the mode of transportation for route calculations.
	 *
	 * @param string $type Transport type ('automobile', 'walking', 'transit', 'bicycle')
	 *
	 * @return self
	 */
	public function transport_type( string $type ): self {
		$valid_types          = [ 'automobile', 'walking', 'transit', 'bicycle' ];
		$this->transport_type = in_array( $type, $valid_types ) ? $type : 'automobile';

		return $this;
	}

	/**
	 * Set search location and optional span
	 *
	 * Defines a center point for the search and optionally the size of the search area.
	 * The span parameters define how much area around the center point to include in the search.
	 *
	 * @param float      $latitude  Latitude of the search center (-90 to 90)
	 * @param float      $longitude Longitude of the search center (-180 to 180)
	 * @param float|null $lat_span  Optional latitude span (height of search area)
	 * @param float|null $lon_span  Optional longitude span (width of search area)
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
	 * Set a pin (marker) on the map
	 *
	 * Places a marker pin at the specified coordinates on the map.
	 *
	 * @param float $latitude  Latitude for the pin (-90 to 90)
	 * @param float $longitude Longitude for the pin (-180 to 180)
	 *
	 * @return self
	 */
	public function add_pin( float $latitude, float $longitude ): self {
		$this->pin_location = [ $latitude, $longitude ];

		return $this;
	}

	/**
	 * Set the interface language
	 *
	 * Specifies the language for map labels and interface elements.
	 *
	 * @param string $lang_code ISO 639-1 language code (e.g., 'en', 'es', 'fr')
	 *
	 * @return self
	 */
	public function language( string $lang_code ): self {
		$this->language = strtolower( $lang_code );

		return $this;
	}

	/**
	 * Set the region preference
	 *
	 * Specifies regional preferences for search results and display.
	 *
	 * @param string $country_code ISO 3166-1 alpha-2 country code (e.g., 'US', 'GB', 'DE')
	 *
	 * @return self
	 */
	public function region( string $country_code ): self {
		$this->region = strtoupper( $country_code );

		return $this;
	}

	/**
	 * Generate the Apple Maps URL
	 *
	 * Builds the final URL based on all set parameters. Returns null if no
	 * valid combination of parameters is found.
	 *
	 * @return string|null The generated URL or null if required parameters are missing
	 */
	public function get_url(): ?string {
		$params = [];

		// Handle different URL types
		if ( $this->destination ) {
			// Directions URL
			return $this->get_directions_url();
		}

		if ( $this->query ) {
			// Search URL
			return $this->get_search_url();
		}

		if ( $this->validate() ) {
			// Coordinates URL
			return $this->get_coordinates_url();
		}

		if ( $this->address ) {
			// Address URL
			$params['address'] = $this->address;
		}

		// Add common parameters
		$this->add_common_params( $params );

		return empty( $params ) ? null : self::BASE_URL . '?' . http_build_query( $params );
	}

	/**
	 * Generate a coordinates-based URL
	 *
	 * @return string The generated coordinates URL
	 */
	private function get_coordinates_url(): string {
		$params = [
			'll' => "{$this->latitude},{$this->longitude}",
			'z'  => $this->zoom
		];

		// Add common parameters
		$this->add_common_params( $params );

		return self::BASE_URL . '?' . http_build_query( $params );
	}

	/**
	 * Generate a search URL
	 *
	 * @return string The generated search URL
	 */
	private function get_search_url(): string {
		$params = [
			'q' => $this->query,
			'z' => $this->zoom
		];

		if ( $this->near_location ) {
			$params['near'] = implode( ',', $this->near_location );
		}

		if ( $this->search_location ) {
			$params['sll'] = implode( ',', $this->search_location );
			if ( $this->search_span ) {
				$params['sspn'] = implode( ',', $this->search_span );
			}
		}

		// Add common parameters
		$this->add_common_params( $params );

		return self::BASE_URL . '?' . http_build_query( $params );
	}

	/**
	 * Generate a directions URL
	 *
	 * @return string The generated directions URL
	 */
	private function get_directions_url(): string {
		$params = [
			'z' => $this->zoom
		];

		// Set the transportation mode first
		$params['dirflg'] = $this->get_directions_flag();

		// Then set origin and destination
		if ( $this->origin ) {
			$params['saddr'] = $this->origin;
		}
		$params['daddr'] = $this->destination;

		// Add common parameters
		$this->add_common_params( $params );

		// Maintain consistent parameter order
		ksort( $params, SORT_NATURAL );

		return self::BASE_URL . '?' . http_build_query( $params, '', '&', PHP_QUERY_RFC3986 );
	}

	/**
	 * Add common URL parameters
	 *
	 * Adds map type, pin location, language, and region parameters if set.
	 *
	 * @param array $params Reference to the parameters array
	 */
	private function add_common_params( array &$params ): void {
		// Add map type if not standard
		if ( $this->map_type !== 'standard' ) {
			$params['t'] = $this->map_type_codes[ $this->map_type ];
		}

		// Add pin if set
		if ( $this->pin_location ) {
			$params['pin'] = implode( ',', $this->pin_location );
		}

		// Add language if set
		if ( $this->language ) {
			$params['lang'] = $this->language;
		}

		// Add region if set
		if ( $this->region ) {
			$params['region'] = $this->region;
		}
	}

	/**
	 * Get the directions flag based on transport type
	 *
	 * @return string The directions flag ('d' = driving, 'w' = walking, 'r' = transit, 'b' = bicycle)
	 */
	private function get_directions_flag(): string {
		switch ( $this->transport_type ) {
			case 'walking':
				return 'w';
			case 'transit':
				return 'r';
			case 'bicycle':
				return 'b';
			default:
				return 'd';
		}
	}

}