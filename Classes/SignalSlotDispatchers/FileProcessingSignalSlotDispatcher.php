<?php
declare(strict_types=1);

namespace Pixelant\PxaImagesCompressor\SignalSlotDispatchers;

use Pixelant\PxaImagesCompressor\Service\ImageCompressService;
use Pixelant\PxaImagesCompressor\Utility\ConfigurationUtility;
use TYPO3\CMS\Core\Resource\Driver\DriverInterface;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\ProcessedFile;
use TYPO3\CMS\Core\Resource\Service\FileProcessingService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Dispatch file processor signals
 *
 * @package Pixelant\PxaImageCompressor
 */
class FileProcessingSignalSlotDispatcher
{
    /**
     * File post processing signal
     *
     * @param FileProcessingService $pObj
     * @param DriverInterface $driver
     * @param ProcessedFile $processedFile
     * @param FileInterface $fileObject
     * @param string $context
     * @param array $configuration
     * @param string $signalName
     */
    public function handlePostFileProcess(
        FileProcessingService $pObj,
        DriverInterface $driver,
        ProcessedFile $processedFile,
        FileInterface $fileObject,
        string $context,
        array $configuration,
        string $signalName
    ): void {
        if (TYPO3_MODE === 'FE'
            && ConfigurationUtility::isCompressionEnabled()
            && !$this->isProcessedFileCompressed($processedFile)
        ) {
            /** @var ImageCompressService $imageCompressService */
            $imageCompressService = GeneralUtility::makeInstance(
                ImageCompressService::class,
                $processedFile
            );
            $imageCompressService->compress();
        }
    }

    /**
     * Check if current file was compressed
     *
     * @param ProcessedFile $processedFile
     * @return bool
     */
    protected function isProcessedFileCompressed(ProcessedFile $processedFile): bool
    {
        $properties = $processedFile->getProperties();

        return intval($properties[ConfigurationUtility::DB_FIELD_NAME] ?? 0) === 1;
    }
}
