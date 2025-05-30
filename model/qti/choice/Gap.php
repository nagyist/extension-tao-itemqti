<?php

/*
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
 * Copyright (c) 2013 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 *
 */

namespace oat\taoQtiItem\model\qti\choice;

use oat\taoQtiItem\model\qti\choice\Gap;
use oat\taoQtiItem\model\qti\choice\Choice;
use oat\taoQtiItem\model\qti\exception\QtiModelException;

/**
 * A Gap
 *
 * @access public
 * @author Sam, <sam@taotesting.com>
 * @package taoQTI

 */
class Gap extends Choice
{
    /**
     * the QTI tag name as defined in QTI standard
     *
     * @access protected
     * @var string
     */
    protected static $qtiTagName = 'gap';

    protected function getUsedAttributes()
    {
        return  [
            'oat\\taoQtiItem\\model\\qti\\attribute\\Required'
        ];
    }

    public function setContent($content)
    {
        throw new QtiModelException('A QTI Gap has no content');
    }

    public function getContent()
    {
        throw new QtiModelException('A QTI Gap has no content');
    }
}
