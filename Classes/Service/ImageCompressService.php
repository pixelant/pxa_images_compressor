<?php

namespace Pixelant\PxaImagesCompressor\Service;

use Pixelant\PxaImagesCompressor\Utility\MainUtility;
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\Imaging\GraphicalFunctions;
use TYPO3\CMS\Core\Resource\ProcessedFile;
use TYPO3\CMS\Core\Resource\ProcessedFileRepository;
use TYPO3\CMS\Core\Utility\CommandUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Compressor main class
 * Use to compress processed files
 *
 * @package Pixelant\PxaImagesCompressor\Service
 */
class ImageCompressService
{
    /**
     * @var ProcessedFile
     */
    protected $processedFile;

    /**
     * Settings
     *
     * @var array
     */
    protected $settings;

    /**
     * Allowed extension to process
     *
     * @var string
     */
    protected $allowedExtension = 'jpg,jpeg,png';

    /**
     * DB field in DB to update
     *
     * @var string
     */
    protected $dbField;

    /**
     * @var DatabaseConnection
     */
    protected $dataBaseConnection;

    /**
     * Init
     *
     * @param ProcessedFile $processedFile
     * @param array $settings
     * @param string $dbField
     */
    public function __construct(ProcessedFile $processedFile, array $settings, $dbField = '')
    {
        $this->processedFile = $processedFile;
        $this->settings = $settings;
        $this->dbField = $dbField ?: MainUtility::DB_FIELD_NAME;
        $this->dataBaseConnection = $GLOBALS['TYPO3_DB'];
    }

    /**
     * Perform compress on file
     *
     * @return bool True if success
     */
    public function compress()
    {
        if (GeneralUtility::inList($this->allowedExtension, $this->processedFile->getExtension())) {
            // If processed file wasn't created
            // This means that TYPO3 will use original file, but we still need to compress it
            if ($this->processedFile->getIdentifier() === $this->getSourceFile()->getIdentifier()) {
                try {
                    $tempFile = $this->getSourceFile()->getForLocalProcessing(true);
                } catch (\Exception $e) {
                    return false;
                }

                $imageDimensions = $this->getGraphicalFunctionsObject()->getImageDimensions($tempFile);
                $task = $this->processedFile->getTask();

                $this->processedFile->setName($task->getTargetFileName());
                $this->processedFile->updateProperties(
                    [
                        'width' => $imageDimensions[0],
                        'height' => $imageDimensions[1],
                        'size' => filesize($tempFile),
                        'checksum' => $task->getConfigurationChecksum()
                    ]
                );
                $this->processedFile->updateWithLocalFile($tempFile);

                /** @var $processedFileRepository ProcessedFileRepository */
                $processedFileRepository = GeneralUtility::makeInstance(ProcessedFileRepository::class);
                $processedFileRepository->update($this->processedFile);
            }

            switch ($this->processedFile->getExtension()) {
                case 'jpg':
                case 'jpeg':
                    $command = 'jpegoptim --strip-all';

                    // Append compression level if needed
                    $jpgCompressingLevel = 100 - (int)$this->settings['jpgCompressingLevel'];
                    if ($jpgCompressingLevel < 100) {
                        $command .= ' --max=' . $jpgCompressingLevel;
                    }
                    // Append name of file

                    $command .= ' ' . $this->processedFile->getForLocalProcessing(false);
                    break;
                case 'png':
                    // Run clean command
                    $command = 'optipng ' . $this->processedFile->getForLocalProcessing(false);
                    break;
                default:
                    throw new \UnexpectedValueException(
                        $this->processedFile->getExtension() . ' extension can\'t be compressed.',
                        1510055827582
                    );
            }

            if (isset($command)) {
                CommandUtility::exec($command, $output, $result);

                // Success, update field
                if ($result === 0) {
                    $this->dataBaseConnection->exec_UPDATEquery(
                        'sys_file_processedfile',
                        'uid=' . $this->processedFile->getUid(),
                        [
                            $this->dbField => 1
                        ]
                    );

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Source file
     *
     * @return \TYPO3\CMS\Core\Resource\File
     */
    protected function getSourceFile()
    {
        return $this->processedFile->getOriginalFile();
    }

    /**
     * @return GraphicalFunctions
     */
    protected function getGraphicalFunctionsObject()
    {
        static $graphicalFunctionsObject;

        if ($graphicalFunctionsObject === null) {
            /** @var GraphicalFunctions $graphicalFunctionsObject */
            $graphicalFunctionsObject = GeneralUtility::makeInstance(GraphicalFunctions::class);
        }

        return $graphicalFunctionsObject;
    }
}
