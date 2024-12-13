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
 * Class BingMaps
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
	private const BASE_URL = 'https://www.bing.com/maps';

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
	 * Travel mode for directions
	 *
	 * @var string
	 */
	private string $travel_mode = 'driving';

	/**
	 * Search query for location search
	 *
	 * @var string|null
	 */
	private ?string $search_query = null;

	/**
	 * Map style
	 *
	 * @var string
	 */
	private string $style = 'road';

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
	 * @param string $style Map style ('road', 'aerial', 'canvasDark', 'canvasLight')
	 *
	 * @return self
	 */
	public function style( string $style ): self {
		$valid_styles = [ 'road', 'aerial', 'canvasDark', 'canvasLight' ];
		$this->style  = in_array( $style, $valid_styles ) ? $style : 'road';

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
		$validModes        = [ 'driving', 'walking', 'transit' ];
		$this->travel_mode = in_array( $mode, $validModes ) ? $mode : 'driving';

		return $this;
	}

	/**
	 * Generate the Bing Maps URL
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
			'cp'    => "{$this->latitude}~{$this->longitude}",
			'lvl'   => $this->zoom,
			'style' => $this->style,
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
			'q'     => $this->search_query,
			'style' => $this->style,
		];

		return self::BASE_URL . '/search?' . http_build_query( $params );
	}

	/**
	 * Generate a directions URL
	 *
	 * @return string The generated directions URL
	 */
	private function get_directions_url(): string {
		$params = [
			'rtp'   => "pos.{$this->origin}~pos.{$this->destination}",
			'mode'  => $this->get_travel_mode_param(),
			'style' => $this->style,
		];

		return self::BASE_URL . '/directions?' . http_build_query( $params );
	}

	/**
	 * Get the travel mode parameter
	 *
	 * @return string The travel mode parameter
	 */
	private function get_travel_mode_param(): string {
		switch ( $this->travel_mode ) {
			case 'walking':
				return 'w';
			case 'transit':
				return 't';
			default:
				return 'd';
		}
	}

}