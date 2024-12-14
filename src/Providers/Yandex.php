<?php
/**
 * MapKit Yandex Maps Service
 *
 * @package     ArrayPress/MapKit
 * @copyright   Copyright (c) 2024, ArrayPress Limited
 * @license     GPL2+
 */

declare( strict_types=1 );

namespace ArrayPress\MapKit\Providers;

use ArrayPress\MapKit\Abstracts\Provider;

/**
 * Class YandexMaps
 *
 * Yandex Maps URL builder implementation.
 * Provides methods for building Yandex Maps URLs with various parameters
 * including routes, markers, and map display options.
 */
class Yandex extends Provider {

	/**
	 * Base URL for Yandex Maps
	 *
	 * @var string
	 */
	private const BASE_URL = 'https://yandex.com/maps/';

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
	private string $travel_mode = 'auto';

	/**
	 * Map language
	 *
	 * @var string
	 */
	private string $language = 'en';

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
	 * Set the travel mode
	 *
	 * @param string $mode Travel mode ('auto', 'masstransit', 'pedestrian', 'bicycle')
	 *
	 * @return self
	 */
	public function travel_mode( string $mode ): self {
		$valid_modes       = [ 'auto', 'masstransit', 'pedestrian', 'bicycle' ];
		$this->travel_mode = in_array( $mode, $valid_modes ) ? $mode : 'auto';

		return $this;
	}

	/**
	 * Set the map language
	 *
	 * @param string $lang Language code (e.g., 'en', 'ru')
	 *
	 * @return self
	 */
	public function language( string $lang ): self {
		$this->language = $lang;

		return $this;
	}

	/**
	 * Generate the Yandex Maps URL
	 *
	 * @return string|null The generated URL or null if required parameters are missing
	 */
	public function get_url(): ?string {
		if ( isset( $this->origin, $this->destination ) ) {
			return $this->get_directions_url();
		}

		if ( $this->validate() ) {
			return $this->get_location_url();
		}

		return null;
	}

	/**
	 * Generate a location URL
	 *
	 * @return string The generated location URL
	 */
	private function get_location_url(): string {
		$params = [
			'lang' => $this->language,
			'll'   => "{$this->longitude},{$this->latitude}",
			'z'    => $this->zoom,
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
			'lang'  => $this->language,
			'rtext' => "{$this->origin}~{$this->destination}",
			'rtt'   => $this->travel_mode,
			'z'     => $this->zoom,
		];

		return self::BASE_URL . '?' . http_build_query( $params );
	}

	/**
	 * Get URL for embedding in an iframe
	 *
	 * @return string|null The embed URL or null if required parameters are missing
	 */
	public function get_embed_url(): ?string {
		$url = $this->get_url();
		if ( ! $url ) {
			return null;
		}

		return str_replace( 'yandex.com/maps/', 'yandex.com/map-widget/v1/', $url );
	}

}