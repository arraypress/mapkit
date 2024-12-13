<?php
/**
 * MapKit OpenStreetMap Service
 *
 * @package     ArrayPress/MapKit
 * @copyright   Copyright (c) 2024, ArrayPress Limited
 * @license     GPL2+
 */

declare( strict_types=1 );

namespace ArrayPress\MapKit\Providers;

/**
 * Class OpenStreetMap
 *
 * OpenStreetMap URL builder implementation.
 * Provides methods for building OpenStreetMap URLs with various parameters
 * including markers, layers, and map display options.
 */
class OpenStreetMap extends Base {

	/**
	 * Base URL for OpenStreetMap
	 *
	 * @var string
	 */
	private const BASE_URL = 'https://www.openstreetmap.org/';

	/**
	 * Map layer type
	 *
	 * @var string
	 */
	private string $layer = 'standard';

	/**
	 * Whether to show the marker
	 *
	 * @var bool
	 */
	private bool $show_marker = true;

	/**
	 * Notes or description for the marker
	 *
	 * @var string|null
	 */
	private ?string $notes = null;

	/**
	 * Set the map layer
	 *
	 * @param string $layer Map layer ('standard', 'cycle', 'transport', 'humanitarian')
	 *
	 * @return self
	 */
	public function layer( string $layer ): self {
		$valid_layers = [ 'standard', 'cycle', 'transport', 'humanitarian' ];
		$this->layer  = in_array( $layer, $valid_layers ) ? $layer : 'standard';

		return $this;
	}

	/**
	 * Toggle marker visibility
	 *
	 * @param bool $show Whether to show the marker
	 *
	 * @return self
	 */
	public function show_marker( bool $show = true ): self {
		$this->show_marker = $show;

		return $this;
	}

	/**
	 * Add notes to the marker
	 *
	 * @param string $notes Notes or description
	 *
	 * @return self
	 */
	public function notes( string $notes ): self {
		$this->notes = $notes;

		return $this;
	}

	/**
	 * Generate the OpenStreetMap URL
	 *
	 * Generates a URL based on the set parameters including coordinates,
	 * zoom level, layer type, and marker options.
	 *
	 * @return string|null The generated URL or null if required parameters are missing
	 */
	public function get_url(): ?string {
		if ( ! $this->validate() ) {
			return null;
		}

		$params    = $this->build_url_parameters();
		$layerPath = $this->get_layer_path();

		return self::BASE_URL . $layerPath . '?' . http_build_query( $params );
	}

	/**
	 * Build URL parameters
	 *
	 * @return array Array of URL parameters
	 */
	private function build_url_parameters(): array {
		$params = [];

		if ( $this->show_marker ) {
			$params['mlat'] = $this->latitude;
			$params['mlon'] = $this->longitude;
		}

		$params['zoom'] = $this->zoom;

		// Center map on marker coordinates
		$params['lat'] = $this->latitude;
		$params['lon'] = $this->longitude;

		if ( $this->notes ) {
			$params['note'] = $this->notes;
		}

		return $params;
	}

	/**
	 * Get the layer-specific URL path
	 *
	 * @return string The URL path for the selected layer
	 */
	private function get_layer_path(): string {
		switch ( $this->layer ) {
			case 'cycle':
				return 'cyclemap';
			case 'transport':
				return 'transport';
			case 'humanitarian':
				return 'hot';
			default:
				return 'map';
		}
	}

	/**
	 * Get the share URL
	 *
	 * Returns a URL suitable for sharing that includes marker and notes
	 *
	 * @return string|null The share URL or null if required parameters are missing
	 */
	public function get_share_url(): ?string {
		if ( ! $this->validate() ) {
			return null;
		}

		$params           = $this->build_url_parameters();
		$params['layers'] = 'N';

		return self::BASE_URL . 'export/embed.html?' . http_build_query( $params );
	}

	/**
	 * Get the edit URL
	 *
	 * Returns a URL that allows editing the map location
	 *
	 * @return string|null The edit URL or null if required parameters are missing
	 */
	public function get_edit_url(): ?string {
		if ( ! $this->validate() ) {
			return null;
		}

		$params           = $this->build_url_parameters();
		$params['editor'] = 'id';

		return self::BASE_URL . 'edit?' . http_build_query( $params );
	}

}