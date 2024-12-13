<?php
/**
 * MapKit - A library for generating map service URLs
 *
 * @package     ArrayPress/MapKit
 * @copyright   Copyright (c) 2024, ArrayPress Limited
 * @license     GPL2+
 */

declare( strict_types=1 );

namespace ArrayPress\MapKit;

use ArrayPress\MapKit\Providers\Apple;
use ArrayPress\MapKit\Providers\Bing;
use ArrayPress\MapKit\Providers\Google;
use ArrayPress\MapKit\Providers\OpenStreetMap;
use ArrayPress\MapKit\Providers\Waze;
use ArrayPress\MapKit\Providers\Yandex;

/**
 * Class MapKit
 *
 * Primary class for generating URLs for various map services.
 * Provides a fluent interface for building URLs with coordinates,
 * markers, zoom levels, and other map-specific parameters.
 */
class MapKit {

	/**
	 * Create a new Google Maps URL builder
	 *
	 * @return Google Google Maps URL builder instance
	 */
	public function google(): Google {
		return new Google();
	}

	/**
	 * Create a new Apple Maps URL builder
	 *
	 * @return Apple Apple Maps URL builder instance
	 */
	public function apple(): Apple {
		return new Apple();
	}

	/**
	 * Create a new Bing Maps URL builder
	 *
	 * @return Bing Bing Maps URL builder instance
	 */
	public function bing(): Bing {
		return new Bing();
	}

	/**
	 * Create a new OpenStreetMap URL builder
	 *
	 * @return OpenStreetMap OpenStreetMap URL builder instance
	 */
	public function open_street_map(): OpenStreetMap {
		return new OpenStreetMap();
	}

	/**
	 * Create a new Waze URL builder
	 *
	 * @return Waze Waze URL builder instance
	 */
	public function waze(): Waze {
		return new Waze();
	}

	/**
	 * Create a new Yandex Maps URL builder
	 *
	 * @return Yandex Yandex Maps URL builder instance
	 */
	public function yandex(): Yandex {
		return new Yandex();
	}

	/**
	 * Get URLs for all supported map services for a given location
	 *
	 * @param float $latitude  Latitude coordinate
	 * @param float $longitude Longitude coordinate
	 * @param int   $zoom      Optional. Zoom level. Default 12.
	 *
	 * @return array Array of map URLs indexed by service name
	 */
	public function get_all_urls( float $latitude, float $longitude, int $zoom = 12 ): array {
		$urls = [];

		// Google Maps
		$urls['google'] = $this->google()
		                       ->coordinates( $latitude, $longitude )
		                       ->zoom( $zoom )
		                       ->get_url();

		// Apple Maps
		$urls['apple'] = $this->apple()
		                      ->coordinates( $latitude, $longitude )
		                      ->zoom( $zoom )
		                      ->get_url();

		// Bing Maps
		$urls['bing'] = $this->bing()
		                     ->coordinates( $latitude, $longitude )
		                     ->zoom( $zoom )
		                     ->get_url();

		// OpenStreetMap
		$urls['osm'] = $this->open_street_map()
		                    ->coordinates( $latitude, $longitude )
		                    ->zoom( $zoom )
		                    ->get_url();

		// Waze
		$urls['waze'] = $this->waze()
		                     ->coordinates( $latitude, $longitude )
		                     ->get_url();

		// Yandex Maps
		$urls['yandex'] = $this->yandex()
		                       ->coordinates( $latitude, $longitude )
		                       ->zoom( $zoom )
		                       ->get_url();

		return array_filter( $urls ); // Remove any null values
	}

}