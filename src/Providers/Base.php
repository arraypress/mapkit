<?php
/**
 * MapKit Base Service Class
 *
 * @package     ArrayPress/MapKit
 * @copyright   Copyright (c) 2024, ArrayPress Limited
 * @license     GPL2+
 */

declare( strict_types=1 );

namespace ArrayPress\MapKit\Providers;

/**
 * Abstract Class MapService
 *
 * Base class for all map service implementations.
 * Provides common functionality for coordinate handling,
 * zoom levels, and map types.
 */
abstract class Base {

	/**
	 * Latitude coordinate
	 *
	 * @var float|null
	 */
	protected ?float $latitude = null;

	/**
	 * Longitude coordinate
	 *
	 * @var float|null
	 */
	protected ?float $longitude = null;

	/**
	 * Map zoom level (1-20)
	 *
	 * @var int
	 */
	protected int $zoom = 12;

	/**
	 * Map display type (e.g., roadmap, satellite)
	 *
	 * @var string
	 */
	protected string $map_type = 'roadmap';

	/**
	 * Set the map coordinates
	 *
	 * @param float $latitude  Latitude coordinate (-90 to 90)
	 * @param float $longitude Longitude coordinate (-180 to 180)
	 *
	 * @return self
	 */
	public function coordinates( float $latitude, float $longitude ): self {
		$this->latitude  = max( - 90, min( 90, $latitude ) );
		$this->longitude = max( - 180, min( 180, $longitude ) );

		return $this;
	}

	/**
	 * Set the map zoom level
	 *
	 * @param int $level Zoom level (1-20)
	 *
	 * @return self
	 */
	public function zoom( int $level ): self {
		$this->zoom = max( 1, min( 20, $level ) );

		return $this;
	}

	/**
	 * Set the map display type
	 *
	 * @param string $type Map type (e.g., roadmap, satellite)
	 *
	 * @return self
	 */
	public function map_type( string $type ): self {
		$this->map_type = $type;

		return $this;
	}

	/**
	 * Generate the map service URL
	 *
	 * @return string|null The generated URL or null if required parameters are missing
	 */
	abstract public function get_url(): ?string;

	/**
	 * Validate required parameters
	 *
	 * @return bool True if all required parameters are set
	 */
	protected function validate(): bool {
		return isset( $this->latitude, $this->longitude );
	}

}