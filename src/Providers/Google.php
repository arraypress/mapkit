<?php
/**
 * MapKit Google Maps Service
 *
 * This class provides a fluent interface for building Google Maps URLs and embeds.
 * It supports various features including:
 * - Coordinate-based maps with zoom levels
 * - Search queries with place IDs
 * - Directions with waypoints and travel modes
 * - Street View panoramas
 * - Map types and layers
 * - Iframe embeds
 * - Language and region preferences
 *
 * @package     ArrayPress/MapKit
 * @copyright   Copyright (c) 2024, ArrayPress Limited
 * @license     GPL2+
 */

declare( strict_types=1 );

namespace ArrayPress\MapKit\Providers;

/**
 * Class Google
 *
 * Google Maps URL and embed builder implementation.
 */
class Google extends Base {

	/**
	 * API version parameter
	 */
	private const API_VERSION = '1';

	/**
	 * Base URL for Google Maps
	 */
	private const BASE_URL = 'https://www.google.com/maps';

	/**
	 * Valid travel modes for directions
	 */
	private const TRAVEL_MODES = [
		'driving',
		'walking',
		'bicycling',
		'transit'
	];

	/**
	 * Valid features that can be avoided in routes
	 */
	private const AVOIDABLE_FEATURES = [
		'tolls',
		'highways',
		'ferries'
	];

	/**
	 * Valid base map types
	 */
	private const MAP_TYPES = [
		'roadmap',
		'satellite',
		'terrain'
	];

	/**
	 * Valid map layers
	 */
	private const MAP_LAYERS = [
		'none',
		'transit',
		'traffic',
		'bicycling'
	];

	/**
	 * Maximum number of allowed waypoints
	 */
	private const MAX_WAYPOINTS = 9;

	/**
	 * Street View constraints
	 */
	private const STREET_VIEW_LIMITS = [
		'heading_min' => - 180,
		'heading_max' => 360,
		'pitch_min'   => - 90,
		'pitch_max'   => 90,
		'fov_min'     => 10,
		'fov_max'     => 100
	];

	/**
	 * Search query string for looking up locations
	 *
	 * @var string|null
	 */
	protected ?string $query = null;

	/**
	 * Place ID for more accurate search results
	 *
	 * @var string|null
	 */
	protected ?string $query_place_id = null;

	/**
	 * Starting point for directions
	 *
	 * @var string|null
	 */
	protected ?string $origin = null;

	/**
	 * Place ID for the origin location
	 *
	 * @var string|null
	 */
	protected ?string $origin_place_id = null;

	/**
	 * Destination point for directions
	 *
	 * @var string|null
	 */
	protected ?string $destination = null;

	/**
	 * Place ID for the destination location
	 *
	 * @var string|null
	 */
	protected ?string $destination_place_id = null;

	/**
	 * Travel mode for directions
	 *
	 * @var string
	 */
	protected string $travel_mode = 'driving';

	/**
	 * Waypoints for the route
	 *
	 * @var array
	 */
	protected array $waypoints = [];

	/**
	 * Place IDs for waypoints
	 *
	 * @var array
	 */
	protected array $waypoint_place_ids = [];

	/**
	 * Features to avoid in route calculation
	 *
	 * @var array
	 */
	protected array $avoid = [];

	/**
	 * Navigation mode flag
	 *
	 * @var bool
	 */
	protected bool $navigate = false;

	/**
	 * Base map type
	 *
	 * @var string
	 */
	protected string $basemap = 'roadmap';

	/**
	 * Map overlay layer
	 *
	 * @var string
	 */
	protected string $layer = 'none';

	/**
	 * Street View panorama ID
	 *
	 * @var string|null
	 */
	protected ?string $pano = null;

	/**
	 * Street View heading (compass direction)
	 *
	 * @var int|null
	 */
	protected ?int $heading = null;

	/**
	 * Street View pitch (vertical angle)
	 *
	 * @var int|null
	 */
	protected ?int $pitch = null;

	/**
	 * Street View field of view
	 *
	 * @var int|null
	 */
	protected ?int $fov = null;

	/**
	 * Interface language code (ISO 639-1)
	 *
	 * @var string|null
	 */
	protected ?string $language = null;

	/**
	 * Region code (ISO 3166-1 alpha-2)
	 *
	 * @var string|null
	 */
	protected ?string $region = null;

	/**
	 * Embed mode flag
	 *
	 * @var bool
	 */
	protected bool $is_embed = false;

	/**
	 * Embed width in pixels
	 *
	 * @var int
	 */
	protected int $embed_width = 600;

	/**
	 * Embed height in pixels
	 *
	 * @var int
	 */
	protected int $embed_height = 450;

	/**
	 * Set a search query
	 *
	 * @param string      $query    Location or business to search for
	 * @param string|null $place_id Optional Google Place ID
	 *
	 * @return self
	 */
	public function search( string $query, ?string $place_id = null ): self {
		$this->query          = $query;
		$this->query_place_id = $place_id;

		return $this;
	}

	/**
	 * Set the starting point for directions
	 *
	 * @param string      $address  Starting address or location
	 * @param string|null $place_id Optional Google Place ID
	 *
	 * @return self
	 */
	public function from( string $address, ?string $place_id = null ): self {
		$this->origin          = $address;
		$this->origin_place_id = $place_id;

		return $this;
	}

	/**
	 * Set the destination point for directions
	 *
	 * @param string      $address  Destination address or location
	 * @param string|null $place_id Optional Google Place ID
	 *
	 * @return self
	 */
	public function to( string $address, ?string $place_id = null ): self {
		$this->destination          = $address;
		$this->destination_place_id = $place_id;

		return $this;
	}

	/**
	 * Set the travel mode for directions
	 *
	 * @param string $mode Travel mode (driving, walking, bicycling, transit)
	 *
	 * @return self
	 */
	public function travel_mode( string $mode ): self {
		$this->travel_mode = in_array( $mode, self::TRAVEL_MODES ) ? $mode : 'driving';

		return $this;
	}

	/**
	 * Add waypoints to the route
	 *
	 * @param array $waypoints Array of addresses/locations
	 * @param array $place_ids Optional array of place IDs
	 *
	 * @return self
	 */
	public function waypoints( array $waypoints, array $place_ids = [] ): self {
		$this->waypoints = array_slice( $waypoints, 0, self::MAX_WAYPOINTS );
		if ( ! empty( $place_ids ) ) {
			$this->waypoint_place_ids = array_slice( $place_ids, 0, count( $this->waypoints ) );
		}

		return $this;
	}

	/**
	 * Set features to avoid in directions
	 *
	 * @param array $features Features to avoid (tolls, highways, ferries)
	 *
	 * @return self
	 */
	public function avoid( array $features ): self {
		$this->avoid = array_intersect( $features, self::AVOIDABLE_FEATURES );

		return $this;
	}

	/**
	 * Enable turn-by-turn navigation mode
	 *
	 * @param bool $enable Whether to enable navigation
	 *
	 * @return self
	 */
	public function navigate( bool $enable = true ): self {
		$this->navigate = $enable;

		return $this;
	}

	/**
	 * Set the base map display type
	 *
	 * @param string $type Map type (roadmap, satellite, terrain)
	 *
	 * @return self
	 */
	public function basemap( string $type ): self {
		$this->basemap = in_array( $type, self::MAP_TYPES ) ? $type : 'roadmap';

		return $this;
	}

	/**
	 * Set the map overlay layer
	 *
	 * @param string $type Layer type (none, transit, traffic, bicycling)
	 *
	 * @return self
	 */
	public function layer( string $type ): self {
		$this->layer = in_array( $type, self::MAP_LAYERS ) ? $type : 'none';

		return $this;
	}

	/**
	 * Set interface language
	 *
	 * @param string $lang_code ISO 639-1 language code
	 *
	 * @return self
	 */
	public function language( string $lang_code ): self {
		$this->language = strtolower( $lang_code );

		return $this;
	}

	/**
	 * Set region preference
	 *
	 * @param string $region_code ISO 3166-1 alpha-2 country code
	 *
	 * @return self
	 */
	public function region( string $region_code ): self {
		$this->region = strtoupper( $region_code );

		return $this;
	}

	/**
	 * Configure for embed usage
	 *
	 * @param int $width  Width in pixels
	 * @param int $height Height in pixels
	 *
	 * @return self
	 */
	public function as_embed( int $width = 600, int $height = 450 ): self {
		$this->is_embed     = true;
		$this->embed_width  = $width;
		$this->embed_height = $height;

		return $this;
	}

	/**
	 * Set Street View panorama parameters
	 *
	 * @param string|null $pano_id Panorama ID
	 * @param int|null    $heading Compass heading (-180 to 360)
	 * @param int|null    $pitch   Vertical angle (-90 to 90)
	 * @param int|null    $fov     Field of view (10 to 100)
	 *
	 * @return self
	 */
	public function street_view( ?string $pano_id = null, ?int $heading = null, ?int $pitch = null, ?int $fov = null ): self {
		$this->pano = $pano_id;

		// Constrain values within valid ranges
		if ( $heading !== null ) {
			$this->heading = max( self::STREET_VIEW_LIMITS['heading_min'],
				min( self::STREET_VIEW_LIMITS['heading_max'], $heading ) );
		}

		if ( $pitch !== null ) {
			$this->pitch = max( self::STREET_VIEW_LIMITS['pitch_min'],
				min( self::STREET_VIEW_LIMITS['pitch_max'], $pitch ) );
		}

		if ( $fov !== null ) {
			$this->fov = max( self::STREET_VIEW_LIMITS['fov_min'],
				min( self::STREET_VIEW_LIMITS['fov_max'], $fov ) );
		}

		return $this;
	}

	/**
	 * Get embed HTML code
	 *
	 * @return string|null HTML iframe code or null if invalid
	 */
	public function get_embed(): ?string {
		if ( ! $this->is_embed ) {
			return null;
		}

		$url = $this->get_url();
		if ( ! $url ) {
			return null;
		}

		return sprintf(
			'<iframe width="%d" height="%d" style="border:0" loading="lazy" allowfullscreen src="%s"></iframe>',
			$this->embed_width,
			$this->embed_height,
			esc_url( $url )
		);
	}

	/**
	 * Generate the Google Maps URL
	 *
	 * @return string|null The generated URL or null if invalid
	 */
	public function get_url(): ?string {
		// Street View panorama
		if ( $this->pano !== null ) {
			return $this->get_street_view_url();
		}

		// Search
		if ( $this->query !== null ) {
			return $this->get_search_url();
		}

		// Directions
		if ( $this->origin !== null || $this->destination !== null ) {
			return $this->get_directions_url();
		}

		// Basic map view
		if ( $this->validate() ) {
			return $this->get_map_url();
		}

		return null;
	}

	/**
	 * Generate a search URL
	 *
	 * @return string The generated search URL
	 */
	private function get_search_url(): string {
		$params = [
			'api'   => '1',
			'query' => $this->query
		];

		if ( $this->query_place_id ) {
			$params['query_place_id'] = $this->query_place_id;
		}

		$this->add_common_params( $params );

		return self::BASE_URL . '/search/?' . http_build_query( $params );
	}

	/**
	 * Generate a directions URL
	 *
	 * @return string The generated directions URL
	 */
	private function get_directions_url(): string {
		$params = [];

		if ($this->origin) {
			$params['origin'] = $this->origin;
			if ($this->origin_place_id) {
				$params['origin_place_id'] = $this->origin_place_id;
			}
		}

		if ($this->destination) {
			$params['destination'] = $this->destination;
			if ($this->destination_place_id) {
				$params['destination_place_id'] = $this->destination_place_id;
			}
		}

		if ($this->travel_mode !== 'driving') {
			$params['travelmode'] = strtoupper($this->travel_mode);
		}

		if (!empty($this->waypoints)) {
			$params['waypoints'] = implode('|', $this->waypoints);
			if (!empty($this->waypoint_place_ids)) {
				$params['waypoint_place_ids'] = implode('|', $this->waypoint_place_ids);
			}
		}

		if (!empty($this->avoid)) {
			$params['avoid'] = implode(',', $this->avoid);
		}

		if ($this->navigate) {
			$params['dir_action'] = 'navigate';
		}

		$this->add_common_params($params);

		// URL format should be: /dir/origin/destination/[@lat,lng]
		$base_url = self::BASE_URL . '/dir/';
		if ($this->latitude !== null && $this->longitude !== null) {
			$base_url .= '@' . $this->latitude . ',' . $this->longitude . ',' . $this->zoom . 'z';
		}

		return $base_url . (!empty($params) ? '?' . http_build_query($params) : '');
	}

	/**
	 * Generate a basic map URL
	 *
	 * @return string The generated map URL
	 */
	private function get_map_url(): string {
		$params = [];
		$this->add_common_params( $params );

		$base_url = self::BASE_URL . '/@' . $this->latitude . ',' . $this->longitude . ',' . $this->zoom . 'z';

		return empty( $params ) ? $base_url : $base_url . '?' . http_build_query( $params );
	}

	/**
	 * Generate a Street View panorama URL
	 *
	 * @return string The generated Street View URL
	 */
	private function get_street_view_url(): string {
		if ( $this->pano ) {
			$base   = self::BASE_URL . '/place/?api=1';
			$params = [
				'pano' => $this->pano
			];
		} else {
			$base   = self::BASE_URL . '/@' . $this->latitude . ',' . $this->longitude;
			$params = [
				'api'        => '1',
				'map_action' => 'pano'
			];
		}

		if ( $this->heading !== null ) {
			$params['heading'] = $this->heading;
		}
		if ( $this->pitch !== null ) {
			$params['pitch'] = $this->pitch;
		}
		if ( $this->fov !== null ) {
			$params['fov'] = $this->fov;
		}

		$this->add_common_params( $params );

		return $base . ( ! empty( $params ) ? '?' . http_build_query( $params ) : '' );
	}

	/**
	 * Add common parameters to the URL
	 *
	 * @param array $params Reference to the parameters array
	 */
	private function add_common_params( array &$params ): void {
		// Add base map type if not default
		if ( $this->basemap !== 'roadmap' ) {
			$params['map_type'] = $this->basemap;
		}

		// Add layer if not none
		if ( $this->layer !== 'none' ) {
			$params['layer'] = $this->layer;
		}

		// Add language if set
		if ( $this->language ) {
			$params['hl'] = $this->language;
		}

		// Add region if set
		if ( $this->region ) {
			$params['gl'] = $this->region;
		}
	}

	/**
	 * Reset all properties to their default values.
	 * Useful when reusing the same instance for multiple URL generations.
	 *
	 * @return self
	 */
	public function reset(): self {
		// Reset search related
		$this->query          = null;
		$this->query_place_id = null;

		// Reset directions related
		$this->origin               = null;
		$this->origin_place_id      = null;
		$this->destination          = null;
		$this->destination_place_id = null;
		$this->travel_mode          = 'driving';
		$this->waypoints            = [];
		$this->waypoint_place_ids   = [];
		$this->avoid                = [];
		$this->navigate             = false;

		// Reset map display
		$this->basemap = 'roadmap';
		$this->layer   = 'none';

		// Reset Street View
		$this->pano    = null;
		$this->heading = null;
		$this->pitch   = null;
		$this->fov     = null;

		// Reset language/region
		$this->language = null;
		$this->region   = null;

		// Reset embed
		$this->is_embed     = false;
		$this->embed_width  = 600;
		$this->embed_height = 450;

		return $this;
	}

}