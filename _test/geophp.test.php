<?php
/*
 * Copyright (c) 2022 Mark C. Prins <mprins@users.sf.net>
 *
 * Permission to use, copy, modify, and distribute this software for any
 * purpose with or without fee is hereby granted, provided that the above
 * copyright notice and this permission notice appear in all copies.
 *
 * THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES
 * WITH REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF
 * MERCHANTABILITY AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR
 * ANY SPECIAL, DIRECT, INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES
 * WHATSOEVER RESULTING FROM LOSS OF USE, DATA OR PROFITS, WHETHER IN AN
 * ACTION OF CONTRACT, NEGLIGENCE OR OTHER TORTIOUS ACTION, ARISING OUT OF
 * OR IN CONNECTION WITH THE USE OR PERFORMANCE OF THIS SOFTWARE.
 */

use geoPHP\geoPHP;

/**
 * General tests for the geophp plugin
 *
 * @group plugin_geophp
 * @group plugin_dokuwikispatial
 * @group plugins
 */
class geophp_plugin_geophp_test extends DokuWikiTest {

    protected $pluginsEnabled = array('geophp');

    final public function setUp(): void
    {
        parent::setUp();

        global $conf;
        $conf['allowdebug'] = 1;
        $conf['cachetime']  = -1;

        if ($conf['allowdebug']) {
            touch(DOKU_TMP_DATA . 'cache/debug.log');
        }

        plugin_load('helper', 'geophp');
    }

    public function test_geophp_load(){
        $polygon = geoPHP::load('POLYGON((1 1,5 1,5 5,1 5,1 1),(2 2,2 3,3 3,3 2,2 2))','wkt');
        $this->assertEquals("Polygon",$polygon->geometryType());
        $this->assertEquals(15.0, $polygon->getArea());
    }

    final protected function tearDown(): void
    {
        parent::tearDown();

        global $conf;
        // try to get the debug log after running the test, print and clear
        if ($conf['allowdebug']) {
            print "\n";
            readfile(DOKU_TMP_DATA . 'cache/debug.log');
            unlink(DOKU_TMP_DATA . 'cache/debug.log');
        }
    }
}
