# MapKit Library for PHP

A comprehensive PHP library for generating URLs and embeds for various map services including Google Maps, Bing Maps, and Apple Maps. Provides a fluent interface for building map URLs with support for coordinates, search, directions, custom views, and more.

## Features

- 🗺️ **Multi-Service Support**: Generate URLs for Google Maps, Bing Maps, and Apple Maps
- 📍 **Location Handling**: Coordinate-based locations and place searches
- 🚗 **Direction Support**: Complex routing with waypoints and travel modes
- 🎨 **View Customization**: Multiple map types and layer options
- 🌍 **Street View**: Support for Google Street View and Bing Bird's Eye view
- 📱 **Embed Generation**: Create embeddable map iframes
- 🌐 **Internationalization**: Language and region preferences
- 🚦 **Traffic & Transit**: Real-time traffic and public transportation overlays
- 📊 **Collections**: Support for multiple location markers and points of interest

## Requirements

- PHP 7.4 or later
- WordPress 6.7.1 or later

## Installation

Install via Composer:

```bash
composer require arraypress/mapkit
```

## Basic Usage

```php
use ArrayPress\MapKit\Client;

// Initialize client
$mapkit = new Client();

// Generate URLs for all services at once
$urls = $mapkit->get_all_urls( 40.7484, -73.9857, 15 ); // Empire State Building

// Or use specific services
$google_url = $mapkit->google()
    ->coordinates( 40.7484, -73.9857 )
    ->zoom( 15 )
    ->get_url();

$bing_url = $mapkit->bing()
    ->coordinates( 40.7484, -73.9857 )
    ->zoom( 15 )
    ->get_url();

$apple_url = $mapkit->apple()
    ->coordinates( 40.7484, -73.9857 )
    ->zoom( 15 )
    ->get_url();
```

## Google Maps Features

### Basic Maps

```php
$google = $mapkit->google();

// Simple map view
$url = $google->coordinates( 40.7484, -73.9857 )
    ->zoom( 15 )
    ->get_url();

// Satellite view
$url = $google->coordinates( 40.7484, -73.9857)
    ->zoom( 18 )
    ->basemap( 'satellite' )
    ->get_url();

// Show traffic
$url = $google->coordinates( 40.7484, -73.9857 )
    ->layer( 'traffic' )
    ->get_url();
```

### Search

```php
// Basic search
$url = $google->search( 'Empire State Building' )
    ->get_url();

// Search with Place ID
$url = $google->search( 'Empire State Building', 'ChIJaXQRs6lZwokRY6EFpJnhNNE' )
    ->get_url();

// Localized search
$url = $google->search( 'Empire State Building' )
    ->language( 'es' )
    ->region( 'US' )
    ->get_url();
```

### Directions

```php
// Basic directions
$url = $google->from( 'Times Square' )
    ->to( 'Empire State Building' )
    ->get_url();

// Complex routing
$url = $google->from( 'Times Square' )
    ->to( 'Empire State Building' )
    ->waypoints( [ 'Madison Square Garden'] )
    ->travel_mode( 'walking' )
    ->avoid( [ 'highways', 'tolls' ] )
    ->get_url();

// Transit directions
$url = $google->from( 'Grand Central' )
    ->to( 'Central Park' )
    ->travel_mode( 'transit' )
    ->get_url();
```

### Street View

```php
// Basic Street View
$url = $google->coordinates( 40.7484, -73.9857 )
    ->street_view()
    ->get_url();

// Customized view
$url = $google->coordinates( 40.7484, -73.9857 )
    ->street_view( null, 180, 20, 90 ) // heading, pitch, FOV
    ->get_url();
```

### Embedded Maps

```php
// Basic embed
$embed = $google->coordinates( 40.7484, -73.9857 )
    ->zoom( 15 )
    ->as_embed()
    ->get_embed();

// Custom size embed
$embed = $google->coordinates( 40.7484, -73.9857 )
    ->zoom( 15 )
    ->as_embed( 800, 600 )
    ->get_embed();
```

## Bing Maps Features

### Basic Maps

```php
$bing = $mapkit->bing();

// Road view
$url = $bing->coordinates( 40.7484, -73.9857 )
    ->style( 'road' )
    ->get_url();

// Aerial view
$url = $bing->coordinates( 40.7484, -73.9857 )
    ->style( 'satellite' )
    ->get_url();

// Bird's eye view
$url = $bing->coordinates( 40.7484, -73.9857 )
    ->style( 'birds-eye' )
    ->birds_eye( null, 180 )
    ->get_url();
```

### Search

```php
// Location search
$url = $bing->search( 'Empire State Building' )
    ->get_url();

// Business search
$url = $bing->business_search( 'restaurants near Times Square' )
    ->get_url();

// Sorted business search
$url = $bing->business_search( 'restaurants', 2 ) // Sort by rating
    ->get_url();
```

### Directions

```php
// Basic directions
$url = $bing->from( 'Times Square' )
    ->to( 'Empire State Building' )
    ->get_url();

// Transit directions with timing
$url = $bing->from( 'Grand Central' )
    ->to( 'Central Park' )
    ->travel_mode( 'transit' )
    ->transit_time( 'depart', '202403151430' )
    ->get_url();

// Route with traffic
$url = $bing->from( 'Times Square' )
    ->to( 'Empire State Building' )
    ->route_options( false, true ) // Show traffic
    ->get_url();
```

### Collections

```php
// Single point
$url = $bing->add_point(
    40.7484, 
    -73.9857,
    'Empire State Building',
    'Iconic NYC landmark'
)->get_url();

// Multiple points
$url = $bing->add_point( 40.7484, -73.9857, 'Empire State Building' )
    ->add_point( 40.7580, -73.9855, 'Times Square' )
    ->get_url();
```

## Apple Maps Features

### Basic Maps

```php
$apple = $mapkit->apple();

// Standard view
$url = $apple->coordinates( 40.7484, -73.9857 )
    ->map_type( 'standard' )
    ->get_url();

// Satellite view
$url = $apple->coordinates( 40.7484, -73.9857 )
    ->map_type( 'satellite' )
    ->get_url();

// Hybrid view
$url = $apple->coordinates( 40.7484, -73.9857 )
    ->map_type( 'hybrid' )
    ->get_url();
```

### Search and Directions

```php
// Search
$url = $apple->search( 'Empire State Building' )
    ->get_url();

// Directions
$url = $apple->from( 'Times Square' )
    ->to( 'Empire State Building' )
    ->transport_type( 'walking' )
    ->get_url();
```

### Additional Features

```php
// Add pin
$url = $apple->coordinates( 40.7484, -73.9857 )
    ->add_pin( 40.7484, -73.9857)
    ->get_url();

// Set locale
$url = $apple->coordinates( 40.7484, -73.9857 )
    ->language( 'es' )
    ->region( 'US' )
    ->get_url();
```

## Advanced Usage

### Method Chaining

All service builders support method chaining for a fluent interface:

```php
$url = $mapkit->google()
    ->coordinates( 40.7484, -73.9857 )
    ->zoom( 15 )
    ->basemap( 'satellite' )
    ->layer( 'traffic' )
    ->language( 'en' )
    ->region( 'US' )
    ->get_url();
```

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request. For major changes, please open an issue first to discuss what you would like to change.

## License

Licensed under the GPLv2 or later license.

## Support

For more information and support:
- [Issue Tracker](https://github.com/arraypress/mapkit/issues)