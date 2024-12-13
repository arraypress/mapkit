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
 * Class Google
 *
 * Google Maps URL builder implementation.
 * Provides methods for building Google Maps URLs with various parameters
 * including search, directions, map display, and Street View panoramas.
 */
class Google extends Base {

	/**
	 * Base URL for Google Maps
	 *
	 * @var string
	 */
	private const BASE_URL = 'https://www.google.com/maps';

	/**
	 * Search query for location search
	 *
	 * @var string|null
	 */
	private ?string $query = null;

	/**
	 * Place ID for the search query
	 *
	 * @var string|null
	 */
	private ?string $query_place_id = null;

	/**
	 * Starting point for directions
	 *
	 * @var string|null
	 */
	private ?string $origin = null;

	/**
	 * Place ID for the origin
	 *
	 * @var string|null
	 */
	private ?string $origin_place_id = null;

	/**
	 * Destination point for directions
	 *
	 * @var string|null
	 */
	private ?string $destination = null;

	/**
	 * Place ID for the destination
	 *
	 * @var string|null
	 */
	private ?string $destination_place_id = null;

	/**
	 * Travel mode for directions
	 *
	 * @var string
	 */
	private string $travel_mode = 'driving';

	/**
	 * Waypoints for directions
	 *
	 * @var array
	 */
	private array $waypoints = [];

	/**
	 * Place IDs for waypoints
	 *
	 * @var array
	 */
	private array $waypoint_place_ids = [];

	/**
	 * Features to avoid in directions
	 *
	 * @var array
	 */
	private array $avoid = [];

	/**
	 * Whether to launch navigation mode
	 *
	 * @var bool
	 */
	private bool $navigate = false;

	/**
	 * Map type (roadmap, satellite, terrain)
	 *
	 * @var string
	 */
	private string $basemap = 'roadmap';

	/**
	 * Map layer (none, transit, traffic, bicycling)
	 *
	 * @var string
	 */
	private string $layer = 'none';

	/**
	 * Street View panorama ID
	 *
	 * @var string|null
	 */
	private ?string $pano = null;

	/**
	 * Street View heading (compass direction)
	 *
	 * @var int|null
	 */
	private ?int $heading = null;

	/**
	 * Street View pitch (vertical angle)
	 *
	 * @var int|null
	 */
	private ?int $pitch = null;

	/**
	 * Street View field of view
	 *
	 * @var int|null
	 */
	private ?int $fov = null;

	/**
	 * Set a search query
	 *
	 * @param string      $query    Location or business name to search for
	 * @param string|null $place_id Optional place ID for more accurate results
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
	 * @param string|null $place_id Optional place ID for more accurate results
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
	 * @param string|null $place_id Optional place ID for more accurate results
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
	 * @param string $mode Travel mode ('driving', 'walking', 'bicycling', 'transit', 'two-wheeler')
	 *
	 * @return self
	 */
	public function travel_mode( string $mode ): self {
		$valid_modes       = [ 'driving', 'walking', 'bicycling', 'transit', 'two-wheeler' ];
		$this->travel_mode = in_array( $mode, $valid_modes ) ? $mode : 'driving';

		return $this;
	}

	/**
	 * Add waypoints to the route
	 *
	 * @param array $waypoints Array of addresses/locations
	 * @param array $place_ids Optional array of place IDs matching waypoints
	 *
	 * @return self
	 */
	public function waypoints( array $waypoints, array $place_ids = [] ): self {
		$this->waypoints = array_slice( $waypoints, 0, 9 ); // Maximum 9 waypoints
		if ( ! empty( $place_ids ) ) {
			$this->waypoint_place_ids = array_slice( $place_ids, 0, count( $this->waypoints ) );
		}

		return $this;
	}

	/**
	 * Set features to avoid in directions
	 *
	 * @param array $features Features to avoid ('tolls', 'highways', 'ferries')
	 *
	 * @return self
	 */
	public function avoid( array $features ): self {
		$valid_features = [ 'tolls', 'highways', 'ferries' ];
		$this->avoid    = array_intersect( $features, $valid_features );

		return $this;
	}

	/**
	 * Enable turn-by-turn navigation mode
	 *
	 * @param bool $enable Whether to enable navigation mode
	 *
	 * @return self
	 */
	public function navigate( bool $enable = true ): self {
		$this->navigate = $enable;

		return $this;
	}

	/**
	 * Set the map display type
	 *
	 * @param string $type Map type ('roadmap', 'satellite', 'terrain')
	 *
	 * @return self
	 */
	public function basemap( string $type ): self {
		$valid_types   = [ 'roadmap', 'satellite', 'terrain' ];
		$this->basemap = in_array( $type, $valid_types ) ? $type : 'roadmap';

		return $this;
	}

	/**
	 * Set the map layer
	 *
	 * @param string $type Layer type ('none', 'transit', 'traffic', 'bicycling')
	 *
	 * @return self
	 */
	public function layer( string $type ): self {
		$valid_types = [ 'none', 'transit', 'traffic', 'bicycling' ];
		$this->layer = in_array( $type, $valid_types ) ? $type : 'none';

		return $this;
	}

	/**
	 * Set Street View panorama parameters
	 *
	 * @param string|null $pano_id Panorama ID
	 * @param int|null    $heading Compass heading (degrees)
	 * @param int|null    $pitch   Vertical angle (degrees)
	 * @param int|null    $fov     Field of view (degrees)
	 *
	 * @return self
	 */
	public function street_view( ?string $pano_id = null, ?int $heading = null, ?int $pitch = null, ?int $fov = null ): self {
		$this->pano    = $pano_id;
		$this->heading = $heading !== null ? max( - 180, min( 360, $heading ) ) : null;
		$this->pitch   = $pitch !== null ? max( - 90, min( 90, $pitch ) ) : null;
		$this->fov     = $fov !== null ? max( 10, min( 100, $fov ) ) : null;

		return $this;
	}

	/**
	 * Generate the Google Maps URL
	 *
	 * @return string|null The generated URL or null if required parameters are missing
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

		return self::BASE_URL . '/search?' . http_build_query( $params );
	}

	/**
	 * Generate a directions URL
	 *
	 * @return string The generated directions URL
	 */
	private function get_directions_url(): string {
		$params = [ 'api' => '1' ];

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

		if ( $this->travel_mode !== 'driving' ) {
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

		return self::BASE_URL . '/dir?' . http_build_query( $params );
	}

	/**
	 * Generate a basic map URL
	 *
	 * @return string The generated map URL
	 */
	private function get_map_url(): string {
		$params = [
			'api'        => '1',
			'map_action' => 'map',
			'center'     => "{$this->latitude},{$this->longitude}",
			'zoom'       => $this->zoom,
			'basemap'    => $this->basemap
		];

		if ( $this->layer !== 'none' ) {
			$params['layer'] = $this->layer;
		}

		return self::BASE_URL . '/@?' . http_build_query( $params );
	}

	/**
	 * Generate a Street View panorama URL
	 *
	 * @return string The generated Street View URL
	 */
	private function get_street_view_url(): string {
		$params = [
			'api'        => '1',
			'map_action' => 'pano'
		];

		if ( $this->pano ) {
			$params['pano'] = $this->pano;
		} elseif ( $this->validate() ) {
			$params['viewpoint'] = "{$this->latitude},{$this->longitude}";
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

		return self::BASE_URL . '/@?' . http_build_query( $params );
	}
}