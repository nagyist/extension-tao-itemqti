<?php

/*
 *
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
 * Copyright (c) 2021  (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\taoQtiItem\model\import\Parser\Exception;

class InvalidImportException extends AbstractImportException
{
    protected const LEVEL = 1;

    private $totalErrors = 0;

    public function addError(int $line, string $message, string $field = null): self
    {
        $this->totalErrors++;

        return $this->addMessage($line, $message, $this->getErrorLevel(), $field);
    }

    public function getTotalErrors(): int
    {
        return $this->totalErrors;
    }

    protected function getErrorLevel(): int
    {
        return static::LEVEL;
    }

    public function getErrors(): array
    {
        return $this->messages[$this->getErrorLevel()];
    }
}