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

use ArrayPress\MapKit\Abstracts\Provider;

/**
 * Class Google
 *
 * Google Maps URL and embed builder implementation.
 */
class Google extends Provider {

	/**
	 * API version parameter for Google Maps URLs
	 * Used in various endpoints to specify the API version being used
	 */
	private const API_VERSION = '1';

	/**
	 * Base URL for all Google Maps endpoints
	 */
	private const BASE_URL = 'https://www.google.com/maps';

	/**
	 * Default values for various map parameters
	 *
	 * - zoom: Default zoom level (12 provides a good city-level view)
	 * - travel_mode: Default mode of transportation
	 * - basemap: Default map visualization type
	 * - layer: Default map layer (none means no additional layer)
	 * - embed_width: Default iframe width in pixels
	 * - embed_height: Default iframe height in pixels
	 */
	private const DEFAULTS = [
		'zoom'         => 12,
		'travel_mode'  => 'driving',
		'basemap'      => 'roadmap',
		'layer'        => 'none',
		'embed_width'  => 600,
		'embed_height' => 450
	];

	/**
	 * Valid travel modes for directions
	 *
	 * Available transportation modes when calculating routes.
	 * Each mode provides different routing options and timing estimates.
	 */
	private const TRAVEL_MODES = [
		'driving',
		'walking',
		'bicycling',
		'two-wheeler', // Added from docs
		'transit'
	];

	/**
	 * Valid features that can be avoided in routes
	 *
	 * Options for route preferences to avoid specific road features.
	 * Multiple features can be avoided simultaneously.
	 */
	private const AVOIDABLE_FEATURES = [
		'tolls',
		'highways',
		'ferries'
	];

	/**
	 * Valid base map types
	 *
	 * Available visualization types for the base map.
	 */
	private const MAP_TYPES = [
		'roadmap',    // Default view
		'satellite'   // Satellite imagery
	];

	/**
	 * Valid map layers
	 *
	 * Defines extra layers to display on the map.
	 * Each layer adds specific information overlay.
	 * Note: Terrain layer uses a special URL format
	 * - none: No additional layer (default)
	 * - transit: Public transportation routes
	 * - traffic: Real-time traffic conditions
	 * - bicycling: Bike paths and preferred roads
	 * - terrain: Topographical view (uses special URL format)
	 */
	private const LAYER_TYPES = [
		'none',
		'transit',
		'traffic',
		'bicycling',
		'terrain'  // Special case - uses different URL structure
	];

	/**
	 * Maximum number of allowed waypoints
	 *
	 * Google Maps limit for intermediate stops in a route.
	 * Exceeding this limit may result in truncated routes.
	 */
	private const MAX_WAYPOINTS = 9;

	/**
	 * Street View constraints
	 *
	 * Valid ranges for Street View camera parameters:
	 * - heading: compass direction (-180° to 360°)
	 * - pitch: vertical angle (-90° to 90°)
	 * - fov: zoom level (10° to 100°)
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
	 * Search query string
	 *
	 * Text query for searching locations, businesses, or points of interest.
	 * Example: "Eiffel Tower" or "Coffee shops in Paris"
	 *
	 * @var string|null
	 */
	protected ?string $query = null;

	/**
	 * Place ID for search query
	 *
	 * Google's unique identifier for a specific place.
	 * Provides more accurate results than text search.
	 * Example: "ChIJZ3JPJ3B65kcRDVuR1RHyrL0" (Eiffel Tower)
	 *
	 * @var string|null
	 */
	protected ?string $query_place_id = null;

	/**
	 * Starting point for directions
	 *
	 * Origin address or location name for route calculations.
	 * Example: "Louvre Museum, Paris" or "48.8606,2.3376"
	 *
	 * @var string|null
	 */
	protected ?string $origin = null;

	/**
	 * Place ID for origin location
	 *
	 * Google's unique identifier for the starting point.
	 * More accurate than text address for routing.
	 *
	 * @var string|null
	 */
	protected ?string $origin_place_id = null;

	/**
	 * Destination point for directions
	 *
	 * Destination address or location name for route calculations.
	 * Example: "Notre-Dame Cathedral, Paris"
	 *
	 * @var string|null
	 */
	protected ?string $destination = null;

	/**
	 * Place ID for destination location
	 *
	 * Google's unique identifier for the destination point.
	 * More accurate than text address for routing.
	 *
	 * @var string|null
	 */
	protected ?string $destination_place_id = null;

	/**
	 * Travel mode for directions
	 *
	 * Mode of transportation for route calculations.
	 * Affects route options, timing, and available paths.
	 * Must be one of: 'driving', 'walking', 'bicycling', 'transit'
	 *
	 * @var string
	 */
	protected string $travel_mode = self::DEFAULTS['travel_mode'];

	/**
	 * Waypoints for the route
	 *
	 * Array of intermediate stops in the route.
	 * Limited to MAX_WAYPOINTS (9) stops.
	 * Example: ["Arc de Triomphe, Paris", "Sacré-Cœur, Paris"]
	 *
	 * @var array
	 */
	protected array $waypoints = [];

	/**
	 * Place IDs for waypoints
	 *
	 * Array of Google Place IDs corresponding to waypoints.
	 * Must match the order of waypoints array.
	 *
	 * @var array
	 */
	protected array $waypoint_place_ids = [];

	/**
	 * Features to avoid in route
	 *
	 * Array of route preferences for avoiding specific features.
	 * Must be from AVOIDABLE_FEATURES constant.
	 * Example: ['tolls', 'highways']
	 *
	 * @var array
	 */
	protected array $avoid = [];

	/**
	 * Navigation mode flag
	 *
	 * When true, attempts to launch turn-by-turn navigation.
	 * Only works on supported devices/browsers.
	 *
	 * @var bool
	 */
	protected bool $navigate = false;

	/**
	 * Base map type
	 *
	 * Visual style of the map display.
	 * Must be one of MAP_TYPES constant values.
	 * Used with MAP_DATA parameters for proper visualization.
	 *
	 * @var string
	 */
	protected string $basemap = self::DEFAULTS['basemap'];

	/**
	 * Map overlay layer
	 *
	 * Additional information layer on top of base map.
	 * Used with LAYER_DATA parameters for proper display.
	 * Example: 'traffic' shows real-time traffic conditions
	 *
	 * @var string
	 */
	protected string $layer = self::DEFAULTS['layer'];

	/**
	 * Street View panorama ID
	 *
	 * Google's unique identifier for a specific Street View scene.
	 * When provided, displays that exact panorama.
	 *
	 * @var string|null
	 */
	protected ?string $pano = null;

	/**
	 * Street View heading
	 *
	 * Camera compass direction in degrees.
	 * Range: -180 to 360 (0 = North, 90 = East, etc.)
	 *
	 * @var int|null
	 */
	protected ?int $heading = null;

	/**
	 * Street View pitch
	 *
	 * Camera vertical angle in degrees.
	 * Range: -90 to 90 (0 = horizontal, positive = up)
	 *
	 * @var int|null
	 */
	protected ?int $pitch = null;

	/**
	 * Street View field of view
	 *
	 * Camera zoom level in degrees.
	 * Range: 10 to 100 (smaller number = more zoom)
	 *
	 * @var int|null
	 */
	protected ?int $fov = null;

	/**
	 * Interface language code
	 *
	 * ISO 639-1 language code for map labels and UI.
	 * Example: 'en' for English, 'fr' for French
	 *
	 * @var string|null
	 */
	protected ?string $language = null;

	/**
	 * Region preference code
	 *
	 * ISO 3166-1 alpha-2 country code for regional settings.
	 * Affects search results and location biasing.
	 * Example: 'US' for United States, 'FR' for France
	 *
	 * @var string|null
	 */
	protected ?string $region = null;

	/**
	 * Embed mode flag
	 *
	 * When true, generates an iframe embed code instead of URL.
	 * Used for embedding maps in web pages.
	 *
	 * @var bool
	 */
	protected bool $is_embed = false;

	/**
	 * Street View mode flag
	 *
	 * Indicates whether the URL should be generated for Street View.
	 * When true and coordinates are set, generates a Street View URL.
	 * When true but only pano ID is set, uses the panorama ID.
	 * When false, generates regular map URLs.
	 *
	 * @var bool
	 */
	protected bool $is_street_view_mode = false;

	/**
	 * Embed width
	 *
	 * Width of the embedded map in pixels.
	 * Used when generating iframe embed code.
	 *
	 * @var int
	 */
	protected int $embed_width = self::DEFAULTS['embed_width'];

	/**
	 * Embed height
	 *
	 * Height of the embedded map in pixels.
	 * Used when generating iframe embed code.
	 *
	 * @var int
	 */
	protected int $embed_height = self::DEFAULTS['embed_height'];

	/**
	 * Reset all properties to their default values
	 *
	 * Useful when reusing the same instance for multiple URL generations.
	 * Resets all properties including search, directions, map display,
	 * Street View, and embed settings.
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
		$this->travel_mode          = self::DEFAULTS['travel_mode'];
		$this->waypoints            = [];
		$this->waypoint_place_ids   = [];
		$this->avoid                = [];
		$this->navigate             = false;

		// Reset map display
		$this->basemap = self::DEFAULTS['basemap'];
		$this->layer   = self::DEFAULTS['layer'];

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
		$this->embed_width  = self::DEFAULTS['embed_width'];
		$this->embed_height = self::DEFAULTS['embed_height'];

		return $this;
	}

	/**
	 * Set a search query
	 *
	 * Defines what to search for on Google Maps.
	 * Can be a place name, address, or business.
	 * Optionally accepts a place ID for exact matching.
	 *
	 * @param string      $query    Location or business to search for (e.g., "Eiffel Tower")
	 * @param string|null $place_id Optional Google Place ID for exact matching
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
	 * Defines where to start the route from.
	 * Can be an address, place name, or coordinates.
	 * Place ID provides more accurate location matching.
	 *
	 * @param string      $address  Starting address or location (e.g., "Louvre Museum, Paris")
	 * @param string|null $place_id Optional Google Place ID for exact matching
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
	 * Defines where the route should end.
	 * Can be an address, place name, or coordinates.
	 * Place ID provides more accurate location matching.
	 *
	 * @param string      $address  Destination address or location
	 * @param string|null $place_id Optional Google Place ID for exact matching
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
	 * Defines how the route should be calculated.
	 * Different modes provide different routes and timing estimates.
	 * Invalid modes fall back to 'driving'.
	 *
	 * @param string $mode Travel mode ('driving', 'walking', 'bicycling', 'transit')
	 *
	 * @return self
	 */
	public function travel_mode( string $mode ): self {
		$this->travel_mode = in_array( $mode, self::TRAVEL_MODES, true )
			? $mode
			: self::DEFAULTS['travel_mode'];

		return $this;
	}

	/**
	 * Add waypoints to the route
	 *
	 * Defines intermediate stops along the route.
	 * Limited to MAX_WAYPOINTS (9) stops.
	 * Place IDs can be provided for more accurate locations.
	 * Waypoints are visited in the order provided.
	 *
	 * @param array $waypoints Array of addresses/locations
	 * @param array $place_ids Optional array of place IDs matching waypoints
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
	 * Defines route preferences for avoiding specific features.
	 * Multiple features can be avoided simultaneously.
	 * Invalid features are ignored.
	 *
	 * @param array $features Features to avoid ('tolls', 'highways', 'ferries')
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
	 * When enabled, attempts to launch navigation mode.
	 * Only works on supported devices/browsers.
	 * May prompt for device location access.
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
	 * Changes the fundamental visualization of the map.
	 * Invalid types fall back to default 'roadmap'.
	 *
	 * @param string $type Map type ('roadmap', 'satellite')
	 *
	 * @return self
	 */
	public function basemap( string $type ): self {
		$this->basemap = in_array( $type, self::MAP_TYPES, true )
			? $type
			: self::DEFAULTS['basemap'];

		return $this;
	}

	/**
	 * Set the map overlay layer
	 *
	 * Adds an information layer on top of the base map.
	 * Uses special data parameters for proper display.
	 * Invalid layer types fall back to 'none'.
	 *
	 * @param string $type Layer type ('none', 'transit', 'traffic', 'bicycling')
	 *
	 * @return self
	 */
	public function layer( string $type ): self {
		$this->layer = in_array( $type, self::LAYER_TYPES, true )
			? $type
			: self::DEFAULTS['layer'];

		return $this;
	}

	/**
	 * Set interface language
	 *
	 * Changes the language of map labels and UI elements.
	 * Uses ISO 639-1 language codes.
	 * Setting cleared if empty string provided.
	 *
	 * @param string $lang_code Language code (e.g., 'en', 'fr', 'de')
	 *
	 * @return self
	 */
	public function language( string $lang_code ): self {
		$this->language = ! empty( $lang_code ) ? strtolower( $lang_code ) : null;

		return $this;
	}

	/**
	 * Set region preference
	 *
	 * Changes region biasing for search results and display.
	 * Uses ISO 3166-1 alpha-2 country codes.
	 * Setting cleared if empty string provided.
	 *
	 * @param string $region_code Country code (e.g., 'US', 'FR', 'GB')
	 *
	 * @return self
	 */
	public function region( string $region_code ): self {
		$this->region = ! empty( $region_code ) ? strtoupper( $region_code ) : null;

		return $this;
	}

	/**
	 * Configure for embed usage
	 *
	 * Prepares for generating an iframe embed code instead of URL.
	 * Width and height define the size of the embedded map.
	 * Uses defaults if dimensions not provided.
	 *
	 * @param int $width  Width in pixels
	 * @param int $height Height in pixels
	 *
	 * @return self
	 */
	public function as_embed( int $width = self::DEFAULTS['embed_width'], int $height = self::DEFAULTS['embed_height'] ): self {
		$this->is_embed     = true;
		$this->embed_width  = max( 0, $width );
		$this->embed_height = max( 0, $height );

		return $this;
	}

	/**
	 * Set Street View panorama parameters
	 *
	 * Configures the Street View camera and perspective.
	 * All parameters are optional, defaults used if not provided.
	 * Values are constrained to valid ranges.
	 *
	 * @param string|null $pano_id Panorama ID for specific location
	 * @param int|null    $heading Compass direction (-180 to 360)
	 * @param int|null    $pitch   Vertical angle (-90 to 90)
	 * @param int|null    $fov     Field of view (10 to 100)
	 *
	 * @return self
	 */
	public function street_view( ?string $pano_id = null, ?int $heading = null, ?int $pitch = null, ?int $fov = null ): self {
		$this->is_street_view_mode = true;  // Set the flag
		$this->pano                = $pano_id;

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
	 * Generates an iframe embed code for the map.
	 * Only works if as_embed() has been called.
	 * Returns null if URL generation fails.
	 *
	 * @return string|null HTML iframe code or null if invalid
	 */
	public function get_embed(): ?string {
		if ( ! $this->is_embed ) {
			return null;
		}

		$params = [];

		if ( $this->query ) {
			$params['q'] = $this->query;
		} else if ( $this->latitude !== null && $this->longitude !== null ) {
			$params['q'] = $this->latitude . ',' . $this->longitude;
		}

		if ( $this->zoom !== self::DEFAULTS['zoom'] ) {
			$params['z'] = $this->zoom;
		}

		if ( $this->basemap !== self::DEFAULTS['basemap'] ) {
			$params['t'] = $this->basemap;
		}

		// Note: For embeds to work, an API key would be needed
		$url = self::BASE_URL . '/embed?' . http_build_query( $params );

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
	 * Creates the appropriate URL based on set parameters.
	 * Prioritizes different modes in this order:
	 * 1. Street View
	 * 2. Search
	 * 3. Directions
	 * 4. Basic map view
	 *
	 * @return string|null The generated URL or null if invalid
	 */
	public function get_url(): ?string {
		// Street View panorama (check if we have either pano ID or coordinates)
		if ( $this->pano !== null || ( $this->latitude !== null && $this->longitude !== null && $this->is_street_view_mode ) ) {
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
	 * Creates a URL for searching locations on Google Maps.
	 * Includes query parameters and optional place ID.
	 *
	 * @return string The generated search URL
	 */
	/**
	 * Generate a search URL
	 *
	 * Format: https://www.google.com/maps/search/?api=1&parameters
	 *
	 * @return string The generated search URL
	 */
	private function get_search_url(): string {
		$params = [
			'api'   => self::API_VERSION,
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
	 * Format: https://www.google.com/maps/dir/?api=1&parameters
	 *
	 * @return string The generated directions URL
	 */
	private function get_directions_url(): string {
		$params = [ 'api' => self::API_VERSION ];

		// Required parameters
		if ( $this->origin ) {
			$params['origin'] = $this->origin;
			if ( $this->origin_place_id ) {
				$params['origin_place_id'] = $this->origin_place_id;
			}
		}

		if ( $this->destination ) {
			$params['destination'] = $this->destination;
			if ( $this->destination_place_id ) {
				$params['destination_place_id'] = $this->destination_place_id;
			}
		}

		// Optional parameters
		if ( $this->travel_mode !== self::DEFAULTS['travel_mode'] ) {
			$params['travelmode'] = $this->travel_mode;
		}

		if ( ! empty( $this->waypoints ) ) {
			$params['waypoints'] = implode( '|', $this->waypoints );
			if ( ! empty( $this->waypoint_place_ids ) ) {
				$params['waypoint_place_ids'] = implode( '|', $this->waypoint_place_ids );
			}
		}

		if ( ! empty( $this->avoid ) ) {
			$params['avoid'] = implode( ',', $this->avoid );
		}

		if ( $this->navigate ) {
			$params['dir_action'] = 'navigate';
		}

		$this->add_common_params( $params );

		return self::BASE_URL . '/dir/?' . http_build_query( $params );
	}

	/**
	 * Generate a basic map URL
	 *
	 * Format: https://www.google.com/maps/@?api=1&map_action=map&parameters
	 * Special case for terrain layer which uses different URL structure
	 *
	 * @return string The generated map URL
	 */
	private function get_map_url(): string {
		// Special case for terrain layer
		if ( $this->layer === 'terrain' && $this->latitude !== null && $this->longitude !== null ) {
			return sprintf(
				'https://www.google.com/maps/@%f,%f,%dz/data=!5m1!1e4',
				$this->latitude,
				$this->longitude,
				$this->zoom
			);
		}

		// Standard URL generation for other cases
		$params = [
			'api'        => self::API_VERSION,
			'map_action' => 'map'
		];

		if ( $this->latitude !== null && $this->longitude !== null ) {
			$params['center'] = $this->latitude . ',' . $this->longitude;
		}

		if ( $this->zoom !== self::DEFAULTS['zoom'] ) {
			$params['zoom'] = $this->zoom;
		}

		if ( $this->basemap !== self::DEFAULTS['basemap'] ) {
			$params['basemap'] = $this->basemap;  // Just use the name directly
		}

		if ( $this->layer !== self::DEFAULTS['layer'] && $this->layer !== 'terrain' ) {
			$params['layer'] = $this->layer;
		}

		$this->add_common_params( $params );

		return self::BASE_URL . '/@?' . http_build_query( $params );
	}

	/**
	 * Generate a Street View panorama URL
	 *
	 * Format: https://www.google.com/maps/@?api=1&map_action=pano&parameters
	 *
	 * @return string The generated Street View URL
	 */
	private function get_street_view_url(): string {
		$params = [
			'api'        => self::API_VERSION,
			'map_action' => 'pano'
		];

		// If we have a pano ID, use that
		if ( $this->pano ) {
			$params['pano'] = $this->pano;
		} // Otherwise use coordinates as viewpoint
		else if ( $this->latitude !== null && $this->longitude !== null ) {
			$params['viewpoint'] = $this->latitude . ',' . $this->longitude;
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

		return self::BASE_URL . '/@?' . http_build_query( $params );
	}

	/**
	 * Add common parameters to the URL
	 *
	 * Adds language and region parameters if set.
	 * Used by all URL generation methods.
	 *
	 * @param array $params Reference to the parameters array
	 */
	private function add_common_params( array &$params ): void {
		if ( $this->language ) {
			$params['hl'] = $this->language;
		}

		if ( $this->region ) {
			$params['gl'] = $this->region;
		}
	}

}