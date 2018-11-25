<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use function array_merge;
use function in_array;
use function json_decode;
use function json_encode;
use function uniqid;
use function view;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    // ROUTE HANDLERS

    public function showMap()
    {
        return view('map', ["center" => json_encode([17.10674, 48.14816]), "zoom" => 12, "tab" => "menu1"]);
    }

    public function searchBusStops(Request $request)
    {
        session()->flashInput($request->input());

        $request->validate([
            'text' => 'required',
            'distance' => 'required'
        ]);
        $data = $request->all();

        $layers = [];
        $response = $this->queryBusStationsNearby($data["text"], $data["distance"]);

        if (!$response) {
            return $this->showMap()->withErrors(['text' => 'Budova nenájdená']);
        }

        $buildings = $this->extractUniquePolygons($response, $by = "building_way");
        Log::info($buildings);
        $layers[] = $this->createLayerPolygons($buildings);

        $layers[] = $this->createLayerSymbol($response);


        $center = json_encode(json_decode($response[0]->building_center, true)["coordinates"]);


        return view('map', ["tab" => "menu1", "layers" => json_encode($layers), "center" => $center, "zoom" => 14]);
    }

    public function searchRoute(Request $request)
    {
        session()->flashInput($request->input());

        $request->validate([
            'from' => 'required',
            'to' => 'required'
        ]);
        $layer = $this->apiSearchRoute($request);

        if ($layer == "{}") {
            return $this->showMap()->withErrors(['from' => 'Budova nenájdená', 'to' => 'Budova nenájdená'])->with(["tab" => "menu2"]);
        }

        return view('map', ["tab" => "menu2", "layers" => json_encode($layer), "center" => json_encode([17.10674, 48.14816]), "zoom" => 12]);


    }

    public function searchBusRoutes(Request $request)
    {
        session()->flashInput($request->input());

        $request->validate([
            'street' => 'required',
        ]);

        $layer = $this->apiSearchBusRoutes($request);

        if ($layer == "{}") {
            return $this->showMap()->withErrors(['street' => 'Ulica nenájdená'])->with(["tab" => "menu3"]);
        }
        Log::info(json_encode($layer));

        return view('map', ["tab" => "menu3", "layers" => json_encode($layer), "center" => json_encode([17.10674, 48.14816]), "zoom" => 12]);


    }

    public function searchBusRoutesByNumber(Request $request)
    {
        session()->flashInput($request->input());

        $request->validate([
            'busNumber' => 'required',
        ]);

        $layer = $this->apiSearchBusRoutesByNumber($request);

        if ($layer == "{}") {
            return $this->showMap()->withErrors(['busNumber' => 'Číslo spoju nenájdené'])->with(["tab" => "menu4"]);
        }
        Log::info(json_encode($layer));

        return view('map', ["tab" => "menu4", "layers" => json_encode($layer), "center" => json_encode([17.10674, 48.14816]), "zoom" => 12]);


    }

    // GEOJSON builders

    private function createLayerBuildingNames($response)
    {
        return [
            "id" => "fromtonames",
            "type" => "symbol",
            "source" => [
                "type" => "geojson",
                "data" => [
                    "type" => "FeatureCollection",
                    "features" => [
                        [
                            "type" => "Feature",
                            "geometry" => json_decode($response[0]["centroid"]),
                            "properties" => [
                                "title" => $response[0]["name"],
                                "icon" => "star"
                            ]
                        ],
                        [
                            "type" => "Feature",
                            "geometry" => json_decode($response[1]["centroid"]),
                            "properties" => [
                                "title" => $response[1]["name"],
                                "icon" => "star"
                            ]
                        ]
                    ]
                ]
            ],
            "layout" => [
                "icon-image" => "{icon}-15",
                "icon-size" => 1.5,
                "text-field" => "{title}",
                "text-font" => ["Open Sans Regular"],
                "text-offset" => [0, -0.8],
                "text-anchor" => "bottom",
                "text-size" => 18,
                "icon-allow-overlap" => true

            ],
            "paint" => [
                "text-color" => "#000"
            ]
        ];

    }

    public function createLayerBusLines($response)
    {

        $result = [
            "id" => "busroutes",
            "type" => "line",
            "source" => [
                "type" => "geojson",
                "data" => [
                    "type" => "FeatureCollection",
                    "features" => []
                ]
            ],
            "layout" => [
                "line-join" => "round",
                "line-cap" => "round"
            ],
            "paint" => [
                "line-color" => "#099",
                "line-width" => 8,
                "line-opacity" => 0.3
            ]
        ];


        foreach ($response as $key => $busroute) {
            $result["source"]["data"]["features"][] = [
                "type" => "Feature",
                "geometry" => json_decode($busroute->geojson)
            ];
        }

        return $result;

    }

    public function createLayerBusLinesLabels($response)
    {
        $result = [];
        foreach ($response as $key => $busroute) {
            $result[] = [
                "id" => uniqid(),
                "type" => "symbol",
                "source" => [
                    "type" => "geojson",
                    "data" => [
                        "type" => "Feature",
                        "properties" => json_decode("{}"),
                        "geometry" => json_decode($busroute->geojson)
                    ]
                ],
                "layout" => [
                    "symbol-placement" => "line",
                    "text-font" => ["Open Sans Regular"],
                    "text-field" => $busroute->buslabel,
                    "text-size" => 18,
                    "text-allow-overlap" => false
                ],
                "paint" => [
                    "text-color" => '#000000'
                ]
            ];
        }

        return $result;
    }

    private function createLayerPolygons($postgisResponse)
    {
        $result = [
            "id" => uniqid(),
            "type" => "fill",
            "source" => [
                "type" => "geojson",
                "data" => [
                    "type" => "FeatureCollection",
                    "features" => []
                ]
            ],
            "layout" => json_decode("{}"),
            "paint" => [
                'fill-color' => '#DB7093',
                'fill-opacity' => 0.8
            ]
        ];


        foreach ($postgisResponse as $key => $amenity) {
            $result["source"]["data"]["features"][] = $this->addGeoJsonPolygon($amenity);
        }
        //$result["source"]["data"]["features"][] = $this->addGeoJsonPolygon($postgisResponse);


        Log::info("GeoJSON " . json_encode($result));
        return $result;
    }

    private function addGeoJsonPolygon($amenity)
    {
        return [
            "type" => "Feature",
            "geometry" => json_decode($amenity["geojson"])
        ];
    }

    public function createLayerLines($postgisResponse)
    {
        $result = [
            "id" => "route",
            "type" => "line",
            "source" => [
                "type" => "geojson",
                "data" => [
                    "type" => "Feature",
                    "properties" => json_decode("{}"),
                    "geometry" => [
                        "type" => "LineString",
                        "coordinates" => []
                    ]
                ]
            ],
            "layout" => [
                "line-join" => "round",
                "line-cap" => "round"
            ],
            "paint" => [
                "line-color" => "#DAA520",
                "line-width" => 8,
                "line-opacity" => 0.8
            ]
        ];

        foreach ($postgisResponse as $key => $row) {
            $result["source"]["data"]["geometry"]["coordinates"][] = json_decode($row->geojson)->coordinates;
        }

        return $result;
    }

    private function createLayerSymbol($postgisResponse)
    {
        $result = [
            "id" => "busstops",
            "type" => "symbol",
            "source" => [
                "type" => "geojson",
                "data" => [
                    "type" => "FeatureCollection",
                    "features" => []
                ]
            ],
            "layout" => [
                "icon-image" => "{icon}-15",
                "icon-size" => 1.3,
                "text-field" => "{title}",
                "text-font" => ["Open Sans Regular"],
                "text-offset" => [0, 0.6],
                "text-anchor" => "top",
                "text-size" => 18,
                "icon-allow-overlap" => true

            ],
            "paint" => [
                "text-color" => "#000"
            ]
        ];

        foreach ($postgisResponse as $key => $row) {
            $result["source"]["data"]["features"][] = $this->addGeoJsonBusStop($row);
        }


        Log::info("GeoJSON " . json_encode($result));
        return $result;
    }

    private function addGeoJsonBusStop($row)
    {
        return [
            "type" => "Feature",
            "geometry" => json_decode($row->geojson),
            "properties" => [
                "title" => $row->name,
                "icon" => "bus",
                "description" => "<a>Vzdialenosť: <strong>" . json_decode($row->distance) . "m</strong></a>"
            ]
        ];
    }

    //QUERY RESPONSE HELPERS

    private function extractUniquePolygons($response, $by = "way")
    {

        $added = [];
        $result = [];
        foreach ($response as $key => $row) {
            if (!in_array($row->$by, $added)) {
                $result[] = array(
                    "name" => $row->building_name,
                    "geojson" => $row->building_geojson,
                    "center" => $row->building_center
                    // TODO add other
                );
                $added[] = $row->$by;
            }
        }

        return $result;

    }

    private function extractFromToPolygons($response)
    {
        return [
            [
                "geojson" => $response->geojson_from,
                "centroid" => $response->centroid_from,
                "name" => $response->name_from
            ],
            [
                "geojson" => $response->geojson_to,
                "centroid" => $response->centroid_to,
                "name" => $response->name_to
            ]
        ];
    }

    // QUERIES

    public function queryBusStationsNearby($name, $distance)
    {
        $result = DB::select(
            "WITH buildings AS(
                    SELECT p.name, st_asgeojson(p.way) as geojson, p.way, 'building' AS type, st_asgeojson(st_centroid(p.way)) as center FROM planet_osm_polygon p
                    WHERE to_tsvector('simple', p.name) @@ plainto_tsquery('simple', :name)
                    --AND p.building IS NOT NULL
                    )
                    SELECT p.name, 
                           st_asgeojson(p.way) AS geojson,
                           p.way as bus_way,
                           'bus' AS type,
                           
                           b.name as building_name,
                           b.geojson as building_geojson,
                           b.way as building_way,
                           b.center as building_center,
                           
                           st_distance_sphere(p.way::geometry, b.way::geometry) AS distance 
                    FROM planet_osm_point p
                    CROSS JOIN buildings AS b
                    WHERE p.public_transport IS NOT NULL
                    AND p.operator = 'DPB'
                    AND  st_dwithin(p.way::geography, b.way::geography, :distance)
                    ;
        ", ['distance' => $distance, 'name' => $name]
        );

        return $result;

    }

    public function queryRouteFromAtoB($from, $to)
    {
        $result = DB::select(
            "WITH fromtable AS (
                                SELECT r.id, st_asgeojson(r.the_geom), st_asgeojson(p.way) as geojson, p.name as name, p.way FROM planet_osm_roads_vertices_pgr r
                                  CROSS JOIN planet_osm_polygon p
                                  CROSS JOIN planet_osm_roads roads
                                WHERE to_tsvector('simple', p.name) @@ plainto_tsquery('simple', :from)
                                      AND roads.highway IS NOT NULL
                                      AND st_touches(roads.way::geometry, r.the_geom)
                                ORDER BY st_distance(p.way::geometry, roads.way::geometry) LIMIT 1
                            ), totable AS (
                                SELECT r.id, st_asgeojson(r.the_geom), st_asgeojson(p.way) as geojson, p.name as name, p.way FROM planet_osm_roads_vertices_pgr r
                                  CROSS JOIN planet_osm_polygon p
                                  CROSS JOIN planet_osm_roads roads
                                WHERE to_tsvector('simple', p.name) @@ plainto_tsquery('simple', :to)
                                      AND roads.highway IS NOT NULL
                                      AND st_touches(roads.way::geometry, r.the_geom)
                                ORDER BY st_distance(p.way::geometry, roads.way::geometry) LIMIT 1
                            ), route AS (
                                SELECT * FROM pgr_dijkstra('
                                    SELECT osm_id AS id, source, target, st_length(way::geometry) AS cost
                                    FROM planet_osm_roads',
                                                           (SELECT id FROM fromtable), (SELECT id FROM totable), directed := False
                                )
                            )
                            SELECT st_asgeojson(v.the_geom) as geojson, 
                              fromtable.geojson as geojson_from, 
                              fromtable.name as name_from,
                              totable.geojson as geojson_to,
                              totable.name as name_to,
                              st_asgeojson(st_centroid(fromtable.way::geometry)) as centroid_from,
                              st_asgeojson(st_centroid(totable.way::geometry)) as centroid_to
                            FROM planet_osm_roads_vertices_pgr v
                              CROSS JOIN fromtable
                              CROSS JOIN totable
                              INNER JOIN route r ON r.node = v.id
                            ;
                          ", ['from' => "%" . $from . "%", 'to' => "%" . $to . "%"]
        );

        return $result;
    }

    public function queryBusRoutesByStreet($street)
    {
        $result = DB::select(
            "SELECT DISTINCT street.name, p.name AS buslabel, st_asgeojson(p.way) AS geojson FROM planet_osm_line p
                    JOIN planet_osm_line street ON st_intersects(street.way::geometry, p.way::geometry)
                    WHERE to_tsvector('simple', street.name) @@ plainto_tsquery('simple', :street)
                    AND p.operator = 'DPB'
                    ;
        ", ['street' => $street]
        );

        return $result;
    }

    public function queryBusRoutesByBusNumber($busNumber)
    {
        $result = DB::select(
            "SELECT l.name AS buslabel, st_asgeojson(l.way) AS geojson FROM planet_osm_line l
                    WHERE to_tsvector('simple', l.name) @@ plainto_tsquery('simple', :number)
                    AND l.operator = 'DPB'
                    ;
        ", ['number' => $busNumber . ':']
        );

        return $result;
    }

    // APIs

    public function apiSearchBusRoutes(Request $request)
    {
        if (!Input::has('street')) {
            return "{'error': 'missing arguments street'}";
        }
        $data = $request->all();

        $result = $this->queryBusRoutesByStreet($data["street"]);

        if (!$result) {
            return "{}";
        }

        $layers[] = $this->createLayerBusLines($result);
        $layers = array_merge($layers, $this->createLayerBusLinesLabels($result));


        return $layers;

    }

    public function apiSearchBusRoutesByNumber(Request $request)
    {
        if (!Input::has('busNumber')) {
            return "{'error': 'missing arguments busNumber'}";
        }
        $data = $request->all();

        $result = $this->queryBusRoutesByBusNumber($data["busNumber"]);

        if (!$result) {
            return "{}";
        }

        $layers[] = $this->createLayerBusLines($result);
        $layers = array_merge($layers, $this->createLayerBusLinesLabels($result));


        return $layers;

    }

    public function apiSearchRoute(Request $request)
    {

        if (!Input::has('from') || !Input::has('to')) {
            return "{'error': 'missing arguments from, to'}";
        }
        $data = $request->all();

        $result = $this->queryRouteFromAtoB($data["from"], $data["to"]);
        if (!$result) {
            return "{}";
        }

        $layers[] = $this->createLayerLines($result);
        $layers[] = $this->createLayerPolygons($this->extractFromToPolygons($result[0]));
        $layers[] = $this->createLayerBuildingNames($this->extractFromToPolygons($result[0]));
        Log::info(json_encode($layers));

        return $layers;
    }

    public function apiSearchBusStops(Request $request)
    {

        if (!Input::has('text') || !Input::has('distance')) {
            return "{'error': 'missing arguments text, distance'}";
        }
        $data = $request->all();

        $response = $this->queryBusStationsNearby($data["text"], $data["distance"]);
        if (!$response) {
            return "{}";
        }

        $buildings = $this->extractUniquePolygons($response, $by = "building_way");

        $layers = [];
        $layers[] = $this->createLayerPolygons($buildings);
        $layers[] = $this->createLayerSymbol($response);
        Log::info(json_encode($layers));

        return $layers;
    }
}
