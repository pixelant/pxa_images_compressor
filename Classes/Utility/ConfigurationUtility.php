<?php
declare(strict_types=1);

namespace Pixelant\PxaImagesCompressor\Utility;

use TYPO3\CMS\Core\Site\Entity\Site;

/**
 * Class ConfigurationUtility
 * @package Pixelant\PxaImagesCompressor\Utility
 */
class ConfigurationUtility
{
    /**
     * DB field name that mark processed file as compressed
     */
    const DB_FIELD_NAME = 'tx_pxaimagescompressor_compressed';

    /**
     * Check if compression enabled
     *
     * @return bool
     */
    public static function isCompressionEnabled(): bool
    {
        $configuration = static::getConfiguration();

        return boolval($configuration['pxaDisableImageCompressing'] ?? false) === false;
    }

    /**
     * Get compression level for jpg
     *
     * @return int
     */
    public static function getJpgCompressingLevel(): int
    {
        $configuration = static::getConfiguration();

        return (int)($configuration['pxaJpgCompressingLevel'] ?? 0);
    }

    /**
     * Site configuration
     *
     * @return array
     */
    protected static function getConfiguration(): array
    {
        /** @var Site $site */
        $site = $GLOBALS['TYPO3_REQUEST']->getAttribute('site');
        return $site->getConfiguration();
    }
}
