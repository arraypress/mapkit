<?php
/**
 * MapKit Bing Maps Service
 *
 * This class provides a fluent interface for building Bing Maps URLs.
 * It supports various features including:
 * - Coordinate-based maps with zoom levels and styles
 * - Search queries for locations and businesses
 * - Directions with multiple travel modes
 * - Birds eye views with scene and direction control
 * - Traffic overlays
 *
 * @package     ArrayPress/MapKit
 * @copyright   Copyright (c) 2024, ArrayPress Limited
 * @license     GPL2+
 */

declare( strict_types=1 );

namespace ArrayPress\MapKit\Providers;

use ArrayPress\MapKit\Abstracts\Provider;

/**
 * Class Bing
 *
 * Bing Maps URL builder implementation.
 */
class Bing extends Provider {

	/**
	 * Base URL for Bing Maps
	 */
	private const BASE_URL = 'https://bing.com/maps/default.aspx';

	/**
	 * Default values for various map parameters
	 *
	 * - zoom: Default zoom level (12 provides a good city-level view)
	 * - travel_mode: Default mode of transportation
	 * - style: Default map visualization type
	 */
	private const DEFAULTS = [
		'zoom'        => 12,
		'travel_mode' => 'driving',
		'style'       => 'road'  // road view
	];

	/**
	 * Valid map styles with their URL parameters
	 *
	 * Available visualization types for the map:
	 * - Default: no style parameter needed (road)
	 * - h: Aerial/Satellite with labels
	 * - s: Ordnance Survey
	 * - g: Bird's eye
	 */
	private const MAP_STYLES = [
		'road'            => '',      // Default - no parameter needed
		'satellite'       => 'h',     // Aerial with labels
		'ordnance-survey' => 's',     // Ordnance survey view
		'birds-eye'       => 'g'      // Bird's eye view
	];

	/**
	 * Valid travel modes with their URL parameters
	 *
	 * Available transportation modes:
	 * - D: Driving (default)
	 * - W: Walking
	 * - T: Transit
	 */
	private const TRAVEL_MODES = [
		'driving' => 'D',
		'walking' => 'W',
		'transit' => 'T'
	];

	/**
	 * Valid time limit types
	 *
	 * Options for arrival/departure timing:
	 * - D: Depart at specified time
	 * - A: Arrive by specified time
	 * - LT: Last train (Japan only)
	 */
	private const TIME_LIMITS = [
		'depart'     => 'D',
		'arrive'     => 'A',
		'last_train' => 'LT'
	];

	/**
	 * Search query string
	 */
	protected ?string $search_query = null;

	/**
	 * Search type (location or business)
	 */
	protected ?string $search_type = null;

	/**
	 * Search sort type
	 * 0: Relevance, 1: Distance, 2: Rating
	 */
	protected int $sort_type = 0;

	/**
	 * Search results page number
	 */
	protected int $page = 1;

	/**
	 * Starting point for directions
	 */
	protected ?string $origin = null;

	/**
	 * Destination point for directions
	 */
	protected ?string $destination = null;

	/**
	 * Travel mode for directions
	 */
	protected string $travel_mode = self::DEFAULTS['travel_mode'];

	/**
	 * Map style/view type
	 */
	protected string $style = self::DEFAULTS['style'];

	/**
	 * Traffic display flag
	 */
	protected bool $show_traffic = false;

	/**
	 * Direction for birds eye view (0, 90, 180, 270)
	 */
	protected ?int $direction = null;

	/**
	 * Scene ID for birds eye view
	 */
	protected ?string $scene = null;

	/**
	 * Route options for directions
	 */
	protected array $route_options = [
		'route_type'   => 0,  // 0: Quickest time, 1: Shortest distance
		'show_traffic' => 0   // 0: No traffic, 1: Show traffic
	];

	/**
	 * Time limit type for transit directions
	 */
	protected ?string $limit_type = null;

	/**
	 * Route time for transit directions (YYYYMMDDhhmm)
	 */
	protected ?string $route_time = null;

	/**
	 * Collection points for custom map data
	 */
	protected array $collection_points = [];

	/**
	 * Embed mode flag
	 */
	protected bool $is_embed = false;

	/**
	 * Embed width in pixels
	 */
	protected int $embed_width = 600;

	/**
	 * Embed height in pixels
	 */
	protected int $embed_height = 450;

	/**
	 * Set a basic location search
	 *
	 * @param string $query Location to search for (address or place name)
	 *
	 * @return self
	 */
	public function search( string $query ): self {
		$this->search_query = $query;
		$this->search_type  = 'where1';

		return $this;
	}

	/**
	 * Set a business category search
	 *
	 * @param string $query     Business category or name to search
	 * @param int    $sort_type Sort type (0: Relevance, 1: Distance, 2: Rating)
	 * @param int    $page      Results page number
	 *
	 * @return self
	 */
	public function business_search( string $query, int $sort_type = 0, int $page = 1 ): self {
		$this->search_query = $query;
		$this->search_type  = 'ss';
		$this->sort_type    = min( max( 0, $sort_type ), 2 );
		$this->page         = max( 1, $page );

		return $this;
	}

	/**
	 * Set the starting point for directions
	 *
	 * @param string $address Starting location
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
	 * @param string $address Destination location
	 *
	 * @return self
	 */
	public function to( string $address ): self {
		$this->destination = $address;

		return $this;
	}

	/**
	 * Set the map style/view type
	 *
	 * @param string $style Map style ('road', 'satellite', 'ordnance-survey', 'birds-eye')
	 *
	 * @return self
	 */
	public function style( string $style ): self {
		if ( array_key_exists( $style, self::MAP_STYLES ) ) {
			$this->style = $style;  // Store the style key, not the value
		} else {
			$this->style = 'road';  // Default to road if invalid style
		}

		return $this;
	}

	/**
	 * Set birds eye view parameters
	 *
	 * @param string|null $scene_id  Scene ID
	 * @param int|null    $direction View direction in degrees (0, 90, 180, 270)
	 *
	 * @return self
	 */
	public function birds_eye( ?string $scene_id = null, ?int $direction = null ): self {
		if ( $scene_id ) {
			$this->scene = $scene_id;
		}
		if ( $direction !== null ) {
			$valid_directions = [ 0, 90, 180, 270 ];
			$this->direction  = in_array( $direction, $valid_directions ) ? $direction : 0;
		}

		return $this;
	}

	/**
	 * Set traffic display
	 *
	 * @param bool $show Whether to show traffic information
	 *
	 * @return self
	 */
	public function show_traffic( bool $show = true ): self {
		$this->show_traffic = $show;

		return $this;
	}

	/**
	 * Set the travel mode for directions
	 *
	 * @param string $mode Travel mode ('driving', 'walking', 'transit')
	 *
	 * @return self
	 */
	public function travel_mode( string $mode ): self {
		$this->travel_mode = array_key_exists( $mode, self::TRAVEL_MODES )
			? $mode
			: self::DEFAULTS['travel_mode'];

		return $this;
	}

	/**
	 * Set route options for directions
	 *
	 * @param bool $shortest_distance Use shortest distance instead of quickest time
	 * @param bool $show_traffic      Show traffic conditions on route
	 *
	 * @return self
	 */
	public function route_options( bool $shortest_distance = false, bool $show_traffic = false ): self {
		$this->route_options = [
			'route_type'   => $shortest_distance ? 1 : 0,
			'show_traffic' => $show_traffic ? 1 : 0
		];

		return $this;
	}

	/**
	 * Set transit timing options
	 *
	 * @param string      $limit_type Time limit type ('depart', 'arrive', 'last_train')
	 * @param string|null $time       Time in YYYYMMDDhhmm format
	 *
	 * @return self
	 */
	public function transit_time( string $limit_type, ?string $time = null ): self {
		if ( isset( self::TIME_LIMITS[ $limit_type ] ) ) {
			$this->limit_type = self::TIME_LIMITS[ $limit_type ];
			$this->route_time = $time;
		}

		return $this;
	}

	/**
	 * Add a point to the collections
	 *
	 * @param float       $lat   Latitude
	 * @param float       $lng   Longitude
	 * @param string      $title Optional title
	 * @param string      $notes Optional notes
	 * @param string|null $url   Optional reference URL
	 * @param string|null $photo Optional photo URL
	 *
	 * @return self
	 */
	public function add_point(
		float $lat,
		float $lng,
		string $title = '',
		string $notes = '',
		?string $url = null,
		?string $photo = null
	): self {
		$this->collection_points[] = compact( 'lat', 'lng', 'title', 'notes', 'url', 'photo' );

		return $this;
	}

	/**
	 * Configure for embed usage
	 *
	 * @param int $width  Width in pixels (default: 600)
	 * @param int $height Height in pixels (default: 450)
	 *
	 * @return self
	 */
	public function as_embed( int $width = 600, int $height = 450 ): self {
		$this->is_embed     = true;
		$this->embed_width  = max( 0, $width );
		$this->embed_height = max( 0, $height );

		return $this;
	}

	/**
	 * Generate the Bing Maps URL
	 *
	 * Creates the appropriate URL based on set parameters.
	 * Prioritizes different modes in this order:
	 * 1. Directions
	 * 2. Search
	 * 3. Collections
	 * 4. Basic map view
	 *
	 * @return string|null The generated URL or null if invalid
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

		// Check if we have collection points
		if ( ! empty( $this->collection_points ) ) {
			return $this->get_collections_url();
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
	/**
	 * Generate a coordinates-based URL
	 *
	 * @return string The generated coordinates URL
	 */
	private function get_coordinates_url(): string {
		$params = [
			'cp'  => "{$this->latitude}~{$this->longitude}",
			'lvl' => $this->zoom
		];

		// Only add style parameter if it's not the default road view
		if ( $this->style !== 'road' ) {
			$style_value = self::MAP_STYLES[ $this->style ];
			if ( ! empty( $style_value ) ) {
				$params['style'] = $style_value;
			}
		}

		if ( $this->scene ) {
			$params['scene'] = $this->scene;
		}

		if ( $this->direction !== null ) {
			$params['dir'] = $this->direction;
		}

		if ( $this->show_traffic ) {
			$params['trfc'] = 1;
		}

		return self::BASE_URL . '?' . http_build_query( $params );
	}

	/**
	 * Generate a search URL
	 *
	 * @return string The generated search URL
	 */
	private function get_search_url(): string {
		$params = [];

		if ( $this->search_type === 'ss' ) {
			$params['ss'] = $this->search_query;
			if ( $this->sort_type !== 0 ) {
				$params['ss'] .= "~sst.{$this->sort_type}";
			}
			if ( $this->page > 1 ) {
				$params['ss'] .= "~pg.{$this->page}";
			}
		} else {
			$params['where1'] = $this->search_query;
		}

		if ( $this->style !== 'road' ) {
			$style_value = self::MAP_STYLES[ $this->style ];
			if ( ! empty( $style_value ) ) {
				$params['style'] = $style_value;
			}
		}

		if ( $this->show_traffic ) {
			$params['trfc'] = 1;
		}

		return self::BASE_URL . '?' . http_build_query( $params );
	}

	/**
	 * Generate a directions URL
	 *
	 * @return string The generated directions URL
	 */
	private function get_directions_url(): string {
		// Build origin and destination strings
		$origin      = "adr.{$this->origin}";
		$destination = "adr.{$this->destination}";

		$params = [
			'rtp'  => "{$origin}~{$destination}",
			'mode' => self::TRAVEL_MODES[ $this->travel_mode ] ?? 'D',
			'rtop' => implode( '~', [
				$this->route_options['route_type'],
				$this->route_options['show_traffic'],
				0
			] )
		];

		if ( $this->limit_type && $this->route_time ) {
			$params['limit'] = $this->limit_type;
			$params['time']  = $this->route_time;
		}

		if ( $this->style !== 'road' ) {
			$style_value = self::MAP_STYLES[ $this->style ];
			if ( ! empty( $style_value ) ) {
				$params['style'] = $style_value;
			}
		}

		return self::BASE_URL . '?' . http_build_query( $params );
	}

	/**
	 * Generate a collections URL
	 *
	 * @return string The generated collections URL
	 */
	private function get_collections_url(): string {
		$points = [];
		foreach ( $this->collection_points as $point ) {
			$point_str = "point.{$point['lat']}_{$point['lng']}";
			if ( $point['title'] ) {
				$point_str .= "_{$point['title']}";
			}
			if ( $point['notes'] ) {
				$point_str .= "_{$point['notes']}";
			}
			if ( $point['url'] ) {
				$point_str .= "_{$point['url']}";
			}
			if ( $point['photo'] ) {
				$point_str .= "_{$point['photo']}";
			}
			$points[] = $point_str;
		}

		$params = [ 'sp' => implode( '~', $points ) ];

		if ( $this->style !== 'road' && $this->style !== self::DEFAULTS['style'] ) {
			$params['style'] = self::MAP_STYLES[ $this->style ];
		}

		return self::BASE_URL . '?' . http_build_query( $params );
	}

	/**
	 * Get embed HTML code
	 *
	 * Generates an iframe embed code for the map.
	 * Only works if as_embed() has been called.
	 *
	 * @return string|null HTML iframe code or null if invalid
	 */
	public function get_embed(): ?string {
		if ( ! $this->is_embed ) {
			return null;
		}

		$params = [
			'w' => $this->embed_width,   // Add width parameter
			'h' => $this->embed_height,  // Add height parameter
		];

		// For location view
		if ( $this->latitude !== null && $this->longitude !== null ) {
			$params['cp']  = "{$this->latitude}~{$this->longitude}";
			$params['lvl'] = $this->zoom;
		}

		// For search
		if ( $this->search_query ) {
			$params['where1'] = $this->search_query;
		}

		// Style
		if ( $this->style !== 'road' ) {
			$style_value = self::MAP_STYLES[ $this->style ];
			if ( ! empty( $style_value ) ) {
				$params['style'] = $style_value;
			}
		}

		// Traffic
		if ( $this->show_traffic ) {
			$params['trfc'] = 1;
		}

		// Birds eye parameters
		if ( $this->scene ) {
			$params['scene'] = $this->scene;
		}
		if ( $this->direction !== null ) {
			$params['dir'] = $this->direction;
		}

		$url = 'https://www.bing.com/maps/embed?' . http_build_query( $params );

		return sprintf(
			'<iframe width="%d" height="%d" frameborder="0" style="border:0" src="%s" allowfullscreen></iframe>',
			$this->embed_width,
			$this->embed_height,
			esc_url( $url )
		);
	}

	/**
	 * Reset all properties to their default values
	 *
	 * @return self
	 */
	public function reset(): self {
		$this->search_query = null;
		$this->search_type  = null;
		$this->sort_type    = 0;
		$this->page         = 1;

		$this->origin      = null;
		$this->destination = null;
		$this->travel_mode = self::DEFAULTS['travel_mode'];

		$this->style        = self::DEFAULTS['style'];
		$this->show_traffic = false;
		$this->direction    = null;
		$this->scene        = null;

		$this->route_options = [
			'route_type'   => 0,
			'show_traffic' => 0
		];

		$this->limit_type = null;
		$this->route_time = null;

		$this->collection_points = [];

		// Reset parent class properties
		$this->latitude  = null;
		$this->longitude = null;
		$this->zoom      = self::DEFAULTS['zoom'];

		$this->is_embed     = false;
		$this->embed_width  = 600;
		$this->embed_height = 450;

		return $this;
	}

	/**
	 * Validate required parameters based on current state
	 *
	 * @return bool True if the current state is valid
	 */
	protected function validate(): bool {
		// For coordinate-based URLs
		if ( $this->latitude !== null && $this->longitude !== null ) {
			return true;
		}

		// For search-based URLs
		if ( $this->search_query !== null ) {
			return true;
		}

		// For directions URLs
		if ( $this->origin !== null && $this->destination !== null ) {
			return true;
		}

		// For collections URLs
		if ( ! empty( $this->collection_points ) ) {
			return true;
		}

		return false;
	}

}