<?php

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA ;
 */

/**
 * @author Jean-Sébastien Conan <jean-sebastien.conan@vesperiagroup.com>
 */

require_once dirname(__FILE__) . '/../../tao/includes/raw_start.php';

common_ext_ExtensionsManager::singleton()->getExtensionById('taoQtiItem');

$dir = \taoItems_models_classes_ItemsService::singleton()->getDefaultItemDirectory();

// maybe it's a dirty way but it's quicker. too much modification would have been required in ItemUpdater
$adapter = $dir->getFileSystem()->getAdapter();
if (!$adapter instanceof \League\Flysystem\Local\LocalFilesystemAdapter) {
    throw new \Exception(__CLASS__ . ' can only handle local files');
}

$itemUpdater = new \oat\taoQtiItem\model\update\ItemFixGhostResponse($adapter->getPathPrefix());
$fixed = $itemUpdater->update(true);
echo "Fixed " . count($fixed) . " items\n";
