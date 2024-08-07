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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA ;
 */

declare(strict_types=1);

namespace oat\taoQtiItem\test\unit\model\qti\validator;

use common_exception_Error;
use oat\generis\test\TestCase;
use oat\tao\model\featureFlag\FeatureFlagChecker;
use oat\taoQtiItem\model\qti\Item;
use oat\taoQtiItem\model\qti\validator\ItemIdentifierValidator;

class ItemIdentifierValidatorTest extends TestCase
{
    /** @var Item $item */
    private $item;

    protected function setUp(): void
    {
        $this->item = $this->createMock(Item::class);
        $this->featureFlagChecker = $this->createMock(FeatureFlagChecker::class);
        $this->subject = new ItemIdentifierValidator(
            $this->featureFlagChecker,
            '/^[a-zA-Z_]{1}[a-zA-Z0-9_\.-]*$/u',
        );
    }

    public function testValidationSuccess(): void
    {
        $this->featureFlagChecker->expects($this->once())
            ->method('isEnabled')
            ->willReturn(false);

        $this->item->expects($this->once())
            ->method('getAttributeValue')
            ->willReturn('some-fake-id-64228-217055');

        $this->subject->validate($this->item);
    }

    public function testValidationSuccessWithDifferentPattern(): void
    {
        $this->featureFlagChecker->expects($this->once())
            ->method('isEnabled')
            ->willReturn(false);

        $this->item->expects($this->once())
            ->method('getAttributeValue')
            ->willReturn('some-fake-id-64228-217055-not-allowed-dots');

        $this->subject->validate($this->item);
    }

    public function testValidationFailureThrowsException(): void
    {
        $this->featureFlagChecker->expects($this->once())
            ->method('isEnabled')
            ->willReturn(false);

        $this->expectException(common_exception_Error::class);

        $this->item->expects($this->once())
            ->method('getAttributeValue')
            ->willReturn('$some.fake.id.64228.217055');

        $this->subject->validate($this->item);
    }

    public function testValidationFailureWithDifferentPatternThrowsException(): void
    {
        $this->featureFlagChecker->expects($this->once())
            ->method('isEnabled')
            ->willReturn(false);

        $this->expectException(common_exception_Error::class);

        $this->item->expects($this->once())
            ->method('getAttributeValue')
            ->willReturn('some.fake.id.64228.217055!not.allowed.dots');

        $this->subject->validate($this->item);
    }

    public function testValidationSuccessWithFeatureFlag()
    {
        $this->featureFlagChecker->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        $this->item->expects($this->once())
            ->method('getAttributeValue')
            ->willReturn('999999999');

        $this->subject->validate($this->item);
    }
}
