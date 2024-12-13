<?php
/**
 * MapKit Bing Maps Service
 *
 * @package     ArrayPress/MapKit
 * @copyright   Copyright (c) 2024, ArrayPress Limited
 * @license     GPL2+
 */

declare( strict_types=1 );

namespace ArrayPress\MapKit\Providers;

/**
 * Class Bing
 *
 * Bing Maps URL builder implementation.
 * Provides methods for building Bing Maps URLs with various parameters
 * including search queries, directions, and map styles.
 */
class Bing extends Base {

	/**
	 * Base URL for Bing Maps
	 *
	 * @var string
	 */
	private const BASE_URL = 'https://bing.com/maps/default.aspx';

	/**
	 * Starting point for directions
	 *
	 * @var string|null
	 */
	protected ?string $origin = null;

	/**
	 * Destination point for directions
	 *
	 * @var string|null
	 */
	protected ?string $destination = null;

	/**
	 * Travel mode for directions
	 *
	 * @var string
	 */
	protected string $travel_mode = 'driving';

	/**
	 * Search query for location search
	 *
	 * @var string|null
	 */
	protected ?string $search_query = null;

	/**
	 * Map style
	 *
	 * @var string
	 */
	protected string $style = 'r';

	/**
	 * Traffic display option
	 *
	 * @var bool
	 */
	protected bool $show_traffic = false;

	/**
	 * Direction (for bird's eye view)
	 *
	 * @var int|null
	 */
	protected ?int $direction = null;

	/**
	 * Scene ID (for bird's eye view)
	 *
	 * @var string|null
	 */
	protected ?string $scene = null;

	/**
	 * Route options for directions
	 *
	 * @var array
	 */
	protected array $route_options = [
		'route_type'   => 0,  // 0: Quickest time, 1: Shortest distance
		'show_traffic' => 0, // 0: No traffic, 1: Show traffic
	];

	/**
	 * Set a search query
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
	 * Set the map style
	 *
	 * @param string $style Map style ('road', 'aerial', 'aerial-labels', 'birds-eye', 'birds-eye-labels')
	 *
	 * @return self
	 */
	public function style( string $style ): self {
		$styles      = [
			'road'             => 'r',
			'aerial'           => 'a',
			'aerial-labels'    => 'h',
			'birds-eye'        => 'o',
			'birds-eye-labels' => 'b'
		];
		$this->style = $styles[ $style ] ?? 'r';

		return $this;
	}

	/**
	 * Set birds eye view parameters
	 *
	 * @param string|null $scene_id  Scene ID for bird's eye view
	 * @param int|null    $direction Direction in degrees (0, 90, 180, 270)
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
		$valid_modes       = [ 'driving' => 'D', 'walking' => 'W', 'transit' => 'T' ];
		$this->travel_mode = array_key_exists( $mode, $valid_modes ) ? $mode : 'driving';

		return $this;
	}

	/**
	 * Set route options for directions
	 *
	 * @param bool $shortest_distance Use shortest distance instead of quickest time
	 * @param bool $show_traffic      Show traffic on route
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
	 * Generate the Bing Maps URL
	 *
	 * @return string|null The generated URL or null if required parameters are missing
	 */
	public function get_url(): ?string {
		$params = [];

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
			'cp'    => "{$this->latitude}~{$this->longitude}",
			'lvl'   => $this->zoom,
			'style' => $this->style
		];

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
		$params = [
			'where1' => $this->search_query,
			'style'  => $this->style
		];

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
		$modes = [
			'driving' => 'D',
			'walking' => 'W',
			'transit' => 'T'
		];

		// Build origin and destination strings
		$origin      = "adr.{$this->origin}";
		$destination = "adr.{$this->destination}";

		$params = [
			'rtp'  => "{$origin}~{$destination}",
			'mode' => $modes[ $this->travel_mode ] ?? 'D',
			'rtop' => implode( '~', [
				$this->route_options['route_type'],
				$this->route_options['show_traffic'],
				0
			] )
		];

		if ( $this->style !== 'r' ) {
			$params['style'] = $this->style;
		}

		return self::BASE_URL . '?' . http_build_query( $params );
	}

}