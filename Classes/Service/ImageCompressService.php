<?php
declare(strict_types=1);

namespace Pixelant\PxaImagesCompressor\Service;

use Pixelant\PxaImagesCompressor\Utility\ConfigurationUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
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
     * Allowed extension to process
     *
     * @var string
     */
    protected $allowedExtension = 'jpg,jpeg,png';

    /**
     * Init
     *
     * @param ProcessedFile $processedFile
     */
    public function __construct(ProcessedFile $processedFile)
    {
        $this->processedFile = $processedFile;
    }

    /**
     * Perform compress on file
     *
     * @return bool True if success
     */
    public function compress()
    {
        if (GeneralUtility::inList($this->allowedExtension, strtolower($this->processedFile->getExtension()))) {
            // If processed file wasn't created
            // This means that TYPO3 will use original file, but we still need to compress it
            if ($this->processedFile->usesOriginalFile()) {
                try {
                    $tempFile = $this->processedFile->getOriginalFile()->getForLocalProcessing(true);
                } catch (\Exception $e) {
                    return false;
                }

                $imageDimensions = GeneralUtility::makeInstance(GraphicalFunctions::class)
                    ->getImageDimensions($tempFile);
                
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

            $command = $this->generateCompressCommand();
            CommandUtility::exec($command, $output, $result);

            // Success, update field
            if ($result === 0) {
                $this->markAsCompressed();

                return true;
            }
        }

        return false;
    }

    /**
     * Generate command for compressing
     *
     * @return string
     */
    protected function generateCompressCommand(): string
    {
        switch (strtolower($this->processedFile->getExtension())) {
            case 'jpg':
            case 'jpeg':
                $command = 'jpegoptim --strip-all';

                // Append compression level if needed
                $jpgCompressingLevel = 100 - ConfigurationUtility::getJpgCompressingLevel();
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

        return $command;
    }

    /**
     * Mark current processed file as compressed
     */
    protected function markAsCompressed(): void
    {
        GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('sys_file_processedfile')
            ->update(
                'sys_file_processedfile',
                [
                    ConfigurationUtility::DB_FIELD_NAME => 1
                ],
                ['uid' => $this->processedFile->getUid()],
                [
                    \PDO::PARAM_INT
                ]
            );
    }
}
