# General course assignment

Build a map-based application, which lets the user see geo-based data on a map and filter/search through it in a meaningfull way. Specify the details and build it in your language of choice. The application should have 3 components:

1. Custom-styled background map, ideally built with [mapbox](http://mapbox.com). Hard-core mode: you can also serve the map tiles yourself using [mapnik](http://mapnik.org/) or similar tool.
2. Local server with [PostGIS](http://postgis.net/) and an API layer that exposes data in a [geojson format](http://geojson.org/).
3. The user-facing application (web, android, ios, your choice..) which calls the API and lets the user see and navigate in the map and shows the geodata. You can (and should) use existing components, such as the Mapbox SDK, or [Leaflet](http://leafletjs.com/).

## Data sources

- [Open Street Maps](https://www.openstreetmap.org/)

## My project

**Application description**: Aplikácia na vyhľadávanie zastávok, trás vozidiel verejnej dopravy alebo trás po vozovkách. 
*Podrobná dokumentácia sa nachádza v súbore `documentation.md`: [Zobraziť podrobnú dokumentáciu](./documentation.md)*

**Data source**: 
Open Street Map, Bratislava a okolie

**Technologies used**:
- [PostgreSQL v10.5](https://www.postgresql.org)
- [The Laravel Framework v5.7](https://laravel.com)
- [Open Street Map](https://openstreetmap.org)
- [OSM2PGSQL](https://github.com/openstreetmap/osm2pgsql)
- [PGRouting](https://pgrouting.org)
