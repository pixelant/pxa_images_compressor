<?php

namespace Pixelant\PxaImagesCompressor\SignalSlotDispatchers;

use Pixelant\PxaImagesCompressor\Service\ImageCompressService;
use Pixelant\PxaImagesCompressor\Utility\MainUtility;
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
        /** @noinspection PhpUnusedParameterInspection */ FileProcessingService $pObj,
        DriverInterface $driver,
        ProcessedFile $processedFile,
        FileInterface $fileObject,
        $context,
        array $configuration,
        $signalName
    ) {
        if (TYPO3_MODE === 'FE'
            && $this->isEnabled()
            && !$this->isProcessedFileCompressed($processedFile)
        ) {
            /** @var ImageCompressService $imageCompressService */
            $imageCompressService = GeneralUtility::makeInstance(
                ImageCompressService::class,
                $processedFile,
                MainUtility::getExtensionConfiguration()
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
    protected function isProcessedFileCompressed(ProcessedFile $processedFile)
    {
        $properties = $processedFile->getProperties();

        return isset($properties[MainUtility::DB_FIELD_NAME]) && (int)$properties[MainUtility::DB_FIELD_NAME] === 1;
    }

    /**
     * Check if compressing enabled
     *
     * @return bool
     */
    protected function isEnabled()
    {
        $settings = MainUtility::getExtensionConfiguration();

        return !array_key_exists('disableCompressing', $settings) || (int)$settings['disableCompressing'] === 0;
    }
}
