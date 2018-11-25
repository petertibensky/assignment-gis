@extends('layouts.main')

@section('content')

    <div id='map' style='width: 100%; height: 80%'></div>
    @include('navigation')

    <script>
        mapboxgl.accessToken = 'pk.eyJ1IjoicGV0ZXJ0aWJlbnNreSIsImEiOiJjam1xNW55NDMxNDcyM3Fxa2liZzR1eXk2In0.kBxubEsS4qHzhJF7ZjJtHQ';
        const map = new mapboxgl.Map({
            container: 'map',
            style: 'mapbox://styles/petertibensky/cjnylib2u1gu92sp92q35ezja',
            center: {!! $center !!},
            zoom: {!! $zoom !!}
        });
        
        // Add every layer
        @if(!empty($layers))
        map.on('load', function () {
            console.log("$layers");
            let layers = {!! $layers !!}

           layers.forEach(function (layer) {
                map.addLayer(layer);
                console.log(layer);
            })
        });
        
        // Description popup functions
        // Source: MapBox documentation: https://www.mapbox.com/mapbox-gl-js/example/popup-on-click/
        map.on('click', 'busstops', function (e) {
            let coordinates = e.features[0].geometry.coordinates.slice();
            let description = e.features[0].properties.description;
            while (Math.abs(e.lngLat.lng - coordinates[0]) > 180) {
                coordinates[0] += e.lngLat.lng > coordinates[0] ? 360 : -360;
            }
            new mapboxgl.Popup()
                .setLngLat(coordinates)
                .setHTML(description)
                .addTo(map);
        });
        map.on('mouseenter', 'busstops', function () {
            map.getCanvas().style.cursor = 'pointer';
        });
        map.on('mouseleave', 'busstops', function () {
            map.getCanvas().style.cursor = '';
        });
        @endif

        map.resize();
    </script>

@endsection