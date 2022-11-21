<?php
// phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps
// phpcs:disable PSR1.Files.SideEffects
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
*
*/

use Composer\InstalledVersions;

require_once __DIR__ . '/vendor/autoload.php';

/**
 * DokuWiki Plugin geophp (Action Component).
 *
 * @author Mark Prins
 */
class action_plugin_geophp extends DokuWiki_Plugin {
    /**
     * plugin should use this method to register its handlers with the DokuWiki's event controller
     *
     * @param    $controller DokuWiki's event controller object. Also available as global $EVENT_HANDLER
     */
    final public function register(Doku_Event_Handler $controller): void {
        $controller->register_hook('PLUGIN_POPULARITY_DATA_SETUP', 'AFTER', $this, 'popularity');
    }

    /**
     * add popularity data for this plugin.
     *
     * @param Doku_Event $event
     *          the DokuWiki event
     */
    final public function popularity(Doku_Event $event): void {
        global $updateVersion;
        $geoPHP                                   = InstalledVersions::getPrettyVersion('funiq/geophp');
        $plugin_info                              = $this->getInfo();
        $event->data['geophp']['version']         = $plugin_info['date'];
        $event->data['geophp']['geophp']          = $geoPHP;
        $event->data['geophp']['dwversion']       = $updateVersion;
        $event->data['geophp']['combinedversion'] = $updateVersion . '_' . $plugin_info['date'] . '_' . $geoPHP;
    }
}