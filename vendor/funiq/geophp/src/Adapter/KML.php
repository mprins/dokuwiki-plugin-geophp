<?php

namespace geoPHP\Adapter;

use geoPHP\Geometry\Collection;
use geoPHP\geoPHP;
use geoPHP\Geometry\Geometry;
use geoPHP\Geometry\GeometryCollection;
use geoPHP\Geometry\Point;
use geoPHP\Geometry\LineString;
use geoPHP\Geometry\Polygon;

/*
 * Copyright (c) Patrick Hayes
 * Copyright (c) 2010-2011, Arnaud Renevier
 *
 * This code is open-source and licenced under the Modified BSD License.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * PHP Geometry/KML encoder/decoder
 *
 * Mainly inspired/adapted from OpenLayers( http://www.openlayers.org )
 *   Openlayers/format/WKT.js
 *
 * @package    sfMapFishPlugin
 * @subpackage GeoJSON
 * @author     Camptocamp <info@camptocamp.com>
 */
class KML implements GeoAdapter
{

    /**
     * @var \DOMDocument
     */
    protected $xmlObject;

    private $namespace = false;

    private $nss = ''; // Name-space string. eg 'georss:'

    /**
     * Read KML string into geometry objects
     *
     * @param string $kml A KML string
     *
     * @return Geometry|GeometryCollection
     */
    public function read($kml)
    {
        return $this->geomFromText($kml);
    }

    public function geomFromText($text)
    {

        // Change to lower-case and strip all CDATA
        $text = mb_strtolower($text, mb_detect_encoding($text));
        $text = preg_replace('/<!\[cdata\[(.*?)\]\]>/s', '', $text);

        // Load into DOMDocument
        $xmlObject = new \DOMDocument();
        @$xmlObject->loadXML($text);
        if ($xmlObject === false) {
            throw new \Exception("Invalid KML: " . $text);
        }

        $this->xmlObject = $xmlObject;
        try {
            $geom = $this->geomFromXML();
        } catch (\Exception $e) {
            throw new \Exception("Cannot Read Geometry From KML. " . $e->getMessage());
        }

        return $geom;
    }

    protected function geomFromXML()
    {
        $geometries = [];
        $placemarkElements = $this->xmlObject->getElementsByTagName('placemark');
        if ($placemarkElements->length) {
            foreach ($placemarkElements as $placemark) {
                $data = [];
                /** @var Geometry|null $geometry */
                $geometry = null;
                foreach ($placemark->childNodes as $child) {
                    // Node names are all the same, except for MultiGeometry, which maps to GeometryCollection
                    $nodeName = $child->nodeName == 'multigeometry' ? 'geometrycollection' : $child->nodeName;
                    if (array_key_exists($nodeName, geoPHP::getGeometryList())) {
                        $function = 'parse' . geoPHP::getGeometryList()[$nodeName];
                        $geometry = $this->$function($child);
                    } elseif ($child->nodeType === 1) {
                        $data[$child->nodeName] = $child->nodeValue;
                    }
                }
                if ($geometry) {
                    if (count($data)) {
                        $geometry->setData($data);
                    }
                    $geometries[] = $geometry;
                }
            }
            return new GeometryCollection($geometries);
        } else {
            // The document does not have a placemark, try to create a valid geometry from the root element
            $nodeName = $this->xmlObject->documentElement->nodeName == 'multigeometry' ? 'geometrycollection' : $this->xmlObject->documentElement->nodeName;
            if (array_key_exists($nodeName, geoPHP::getGeometryList())) {
                $function = 'parse' . geoPHP::getGeometryList()[$nodeName];
                return $this->$function($this->xmlObject->documentElement);
            }
        }
        //return geoPHP::geometryReduce($geometries);
        return new GeometryCollection();
    }

    protected function childElements($xml, $nodeName = '')
    {
        $children = [];
        if ($xml && $xml->childNodes) {
            foreach ($xml->childNodes as $child) {
                if ($child->nodeName == $nodeName) {
                    $children[] = $child;
                }
            }
        }
        return $children;
    }

    protected function parsePoint($xml)
    {
        $coordinates = $this->extractCoordinates($xml);
        if (empty($coordinates)) {
            return new Point();
        }
        return new Point(
            $coordinates[0][0],
            $coordinates[0][1],
            (isset($coordinates[0][2]) ? $coordinates[0][2] : null),
            (isset($coordinates[0][3]) ? $coordinates[0][3] : null)
        );
    }

    protected function parseLineString($xml)
    {
        $coordinates = $this->extractCoordinates($xml);
        $pointArray = [];
        $hasZ = false;
        $hasM = false;
        foreach ($coordinates as $set) {
            $hasZ = $hasZ || (isset($set[2]) && $set[2]);
            $hasM = $hasM || (isset($set[3]) && $set[3]);
        }
        foreach ($coordinates as $set) {
            $pointArray[] = new Point(
                $set[0],
                $set[1],
                ($hasZ ? (isset($set[2]) ? $set[2] : 0) : null),
                ($hasM ? (isset($set[3]) ? $set[3] : 0) : null)
            );
        }
        return new LineString($pointArray);
    }

    protected function parsePolygon($xml)
    {
        $components = [];

        /** @noinspection SpellCheckingInspection */
        $outerBoundaryIs = $this->childElements($xml, 'outerboundaryis');
        if (!$outerBoundaryIs) {
            return new Polygon();
        }
        $outerBoundaryElement = $outerBoundaryIs[0];
        /** @noinspection SpellCheckingInspection */
        $outerRingElement = @$this->childElements($outerBoundaryElement, 'linearring')[0];
        $components[] = $this->parseLineString($outerRingElement);

        if (count($components) != 1) {
            throw new \Exception("Invalid KML");
        }

        /** @noinspection SpellCheckingInspection */
        $innerBoundaryElementIs = $this->childElements($xml, 'innerboundaryis');
        foreach ($innerBoundaryElementIs as $innerBoundaryElement) {
            /** @noinspection SpellCheckingInspection */
            foreach ($this->childElements($innerBoundaryElement, 'linearring') as $innerRingElement) {
                $components[] = $this->parseLineString($innerRingElement);
            }
        }

        return new Polygon($components);
    }

    protected function parseGeometryCollection($xml)
    {
        $components = [];
        $geometryTypes = geoPHP::getGeometryList();
        foreach ($xml->childNodes as $child) {
            /** @noinspection SpellCheckingInspection */
            $nodeName = ($child->nodeName == 'linearring')
                    ? 'linestring'
                    : ($child->nodeName == 'multigeometry'
                            ? 'geometrycollection'
                            : $child->nodeName);
            if (array_key_exists($nodeName, $geometryTypes)) {
                $function = 'parse' . $geometryTypes[$nodeName];
                $components[] = $this->$function($child);
            }
        }
        return new GeometryCollection($components);
    }

    protected function extractCoordinates($xml)
    {
        $coordinateElements = $this->childElements($xml, 'coordinates');
        $coordinates = [];
        if (!empty($coordinateElements)) {
            $coordinateSets = explode(' ', preg_replace('/[\r\n\s\t]+/', ' ', $coordinateElements[0]->nodeValue));

            foreach ($coordinateSets as $setString) {
                $setString = trim($setString);
                if ($setString) {
                    $setArray = explode(',', $setString);
                    if (count($setArray) >= 2) {
                        $coordinates[] = $setArray;
                    }
                }
            }
        }
        return $coordinates;
    }


    /**
     * Serialize geometries into a KML string.
     *
     * @param Geometry $geometry
     * @param bool $namespace
     * @return string The KML string representation of the input geometries
     */
    public function write(Geometry $geometry, $namespace = false)
    {
        if ($namespace) {
            $this->namespace = $namespace;
            $this->nss = $namespace . ':';
        }
        return $this->geometryToKML($geometry);
    }

    /**
     * @param Geometry $geometry
     * @return string
     */
    private function geometryToKML($geometry)
    {
        $type = $geometry->geometryType();
        switch ($type) {
            case Geometry::POINT:
                /** @var Point $geometry */
                return $this->pointToKML($geometry);
            case Geometry::LINE_STRING:
                /** @var LineString $geometry */
                return $this->linestringToKML($geometry);
            case Geometry::POLYGON:
                /** @var Polygon $geometry */
                return $this->polygonToKML($geometry);
            case Geometry::MULTI_POINT:
            case Geometry::MULTI_LINE_STRING:
            case Geometry::MULTI_POLYGON:
            case Geometry::GEOMETRY_COLLECTION:
            /** @var Collection $geometry */
                return $this->collectionToKML($geometry);
        }
        return '';
    }

    /**
     * @param Point $geometry
     * @return string
     */
    private function pointToKML($geometry)
    {
        $str = '<' . $this->nss . "Point>\n<" . $this->nss . 'coordinates>';
        if ($geometry->isEmpty()) {
            $str .= "0,0";
        } else {
            $str .= $geometry->x() . ',' . $geometry->y() . ($geometry->hasZ() ? ',' . $geometry->z() : '');
        }
        return $str . '</' . $this->nss . 'coordinates></' . $this->nss . "Point>\n";
    }

    /**
     * @param LineString $geometry
     * @param string|boolean $type
     * @return string
     */
    private function linestringToKML($geometry, $type = false)
    {
        if (!$type) {
            $type = $geometry->geometryType();
        }

        $str = '<' . $this->nss . $type . ">\n";

        if (!$geometry->isEmpty()) {
            $str .= '<' . $this->nss . 'coordinates>';
            $i = 0;
            foreach ($geometry->getComponents() as $comp) {
                if ($i != 0) {
                    $str .= ' ';
                }
                $str .= $comp->x() . ',' . $comp->y();
                $i++;
            }

            $str .= '</' . $this->nss . 'coordinates>';
        }

        $str .= '</' . $this->nss . $type . ">\n";

        return $str;
    }

    /**
     * @param Polygon $geometry
     * @return string
     */
    public function polygonToKML($geometry)
    {
        $components = $geometry->getComponents();
        $str = '';
        if (!empty($components)) {
            /** @noinspection PhpParamsInspection */
            $str = '<' . $this->nss . 'outerBoundaryIs>' . $this->linestringToKML($components[0], 'LinearRing') . '</' . $this->nss . 'outerBoundaryIs>';
            foreach (array_slice($components, 1) as $comp) {
                $str .= '<' . $this->nss . 'innerBoundaryIs>' . $this->linestringToKML($comp) . '</' . $this->nss . 'innerBoundaryIs>';
            }
        }

        return '<' . $this->nss . "Polygon>\n" . $str . '</' . $this->nss . "Polygon>\n";
    }

    /**
     * @param Collection $geometry
     * @return string
     */
    public function collectionToKML($geometry)
    {
        $components = $geometry->getComponents();
        $str = '<' . $this->nss . "MultiGeometry>\n";
        foreach ($components as $component) {
            $subAdapter = new KML();
            $str .= $subAdapter->write($component);
        }

        return $str . '</' . $this->nss . "MultiGeometry>\n";
    }
}
