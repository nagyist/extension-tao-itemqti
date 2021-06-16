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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 */

declare(strict_types=1);

namespace oat\taoQtiItem\model\import;

use core_kernel_classes_Class;
use core_kernel_classes_Resource;
use helpers_TimeOutHelper;
use oat\oatbox\filesystem\File;
use oat\oatbox\reporting\Report;
use oat\oatbox\reporting\ReportInterface;
use oat\oatbox\service\ConfigurableService;
use oat\taoQtiItem\model\import\Metadata\MetadataResolver;
use oat\taoQtiItem\model\import\Parser\CsvParser;
use oat\taoQtiItem\model\import\Parser\CsvSeparatorTrait;
use oat\taoQtiItem\model\import\Parser\Exception\InvalidImportException;
use oat\taoQtiItem\model\import\Parser\Exception\InvalidMetadataException;
use oat\taoQtiItem\model\import\Parser\ParserInterface;
use oat\taoQtiItem\model\import\Template\ItemsQtiTemplateRender;
use oat\taoQtiItem\model\qti\ImportService;
use tao_models_classes_dataBinding_GenerisFormDataBinder;
use tao_models_classes_dataBinding_GenerisFormDataBindingException;
use taoItems_models_classes_ItemsService;
use Throwable;
use Zend\ServiceManager\ServiceLocatorAwareInterface;

class CsvItemImportHandler extends ConfigurableService
{
    use CsvSeparatorTrait;

    /**
     * @throws InvalidImportException
     */
    public function import(
        File $uploadedFile,
        TemplateInterface $template,
        core_kernel_classes_Class $class
    ): ItemImportResult {
        helpers_TimeOutHelper::setTimeOutLimit(helpers_TimeOutHelper::LONG);

        $logger = $this->getLogger();
        $logger->debug('Tabular import: CSV parsing started');

        $itemValidatorResults = $this->getParser()->parseFile($uploadedFile, $template);

        $logger->debug('Tabular import: CSV parsing finished');

        $successReportsImport = 0;
        $importService = $this->getItemImportService();
        $templateProcessor = $this->getTemplateProcessor();
        $errorReportsImport = count($itemValidatorResults->getErrorReports());
        $xmlItems = $templateProcessor->processResultSet($itemValidatorResults, $template);

        foreach ($xmlItems as $lineNumber => $xmlItem) {
            try {
                $metaData = $this->getMetadataResolver()->resolve($class, $xmlItem->getMetadata());

                $itemImportReport  = $importService->importQTIFile($xmlItem->getItemXML(), $class, true);
                $this->importMetadata($metaData, $itemImportReport);

                if (Report::TYPE_SUCCESS === $itemImportReport->getType()) {
                    $itemValidatorResults->setFirstItem($itemImportReport->getData());

                    $logger->debug(sprintf('Tabular import: successful import of item from line %s', $lineNumber));

                    $successReportsImport++;
                } else {
                    $logger->debug(sprintf(
                            'Tabular import: failed import of item from line %s due to %s',
                            $lineNumber,
                            $itemImportReport->getMessage())
                    );

                    $error = new InvalidImportException();
                    $error->addError($lineNumber, $itemImportReport->getMessage());

                    $itemValidatorResults->addErrorReport($lineNumber, $error);
                    $errorReportsImport++;
                }
                unset($itemImportReport);
            } catch (InvalidMetadataException $exception) {
                $logger->debug(sprintf(
                        'Tabular import: failed import of item from line %s due to %s',
                        $lineNumber,
                        $exception->getMessage())
                );
                $error = new InvalidImportException();
                $error->addError($lineNumber, $exception->getMessage());
                if (isset($itemImportReport)) {
                    $this->rollbackItem($itemImportReport, $lineNumber);
                }

                $itemValidatorResults->addErrorReport($lineNumber, $error);
                $errorReportsImport++;
            } catch (Throwable $exception) {
                $logger->error(sprintf(
                        'Tabular import: failed import of item from line %s due to %s',
                        $lineNumber,
                        $exception->getMessage())
                );

                if (isset($itemImportReport)){
                    $this->rollbackItem($itemImportReport, $lineNumber);
                }

                $errorReportsImport++;

                $error = new InvalidImportException();
                $error->addError($lineNumber, $exception->getMessage(), '');

                $itemValidatorResults->addErrorReport($lineNumber, $error);
            }
        }

        helpers_TimeOutHelper::reset();

        $logger->debug(sprintf(
                'Tabular import: successful import %s items from %s',
                $successReportsImport,
                count($xmlItems)
        ));

        $itemValidatorResults->setTotalSuccessfulImport($successReportsImport);

        return $itemValidatorResults;
    }

    private function getParser(): ParserInterface
    {
        /** @var CsvParser $parser */
        $parser = $this->getServiceLocator()->get(CsvParser::class);
        $parser->setCsvSeparator($this->getCsvSeparator());

        return $parser;
    }

    private function getItemImportService(): ImportService
    {
        return $this->getServiceLocator()->get(ImportService::SERVICE_ID);
    }

    private function getItemService(): taoItems_models_classes_ItemsService
    {
        return $this->getServiceLocator()->get(taoItems_models_classes_ItemsService::class);
    }

    private function getTemplateProcessor(): ItemsQtiTemplateRender
    {
        return $this->getServiceLocator()->get(ItemsQtiTemplateRender::class);
    }

    private function getMetadataResolver(): MetadataResolver
    {
        return $this->getServiceManager()->get(MetadataResolver::class);
    }

    /**
     * @throws tao_models_classes_dataBinding_GenerisFormDataBindingException
     */
    private function importMetadata(array $metaData, ReportInterface $itemImportReport): void
    {
        $itemRdf  = $itemImportReport->getData();
        $binder   = new tao_models_classes_dataBinding_GenerisFormDataBinder($itemRdf);
        $binder->bind($metaData);
    }

    private function rollbackItem(ReportInterface $itemImportReport, int $lineNumber): void
    {
        $item = $itemImportReport->getData();

        if ($item instanceof core_kernel_classes_Resource) {
            $this->getItemService()->deleteResource($item);

            $this->getLogger()->warning(
                sprintf(
                    'Tabular import: line `%s` rollback item with uri `%s` and label %s',
                    $lineNumber,
                    $item->getUri(),
                    $item->getLabel()
                )
            );
        }
    }
}