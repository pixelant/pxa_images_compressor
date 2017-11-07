<?php

namespace Pixelant\PxaImagesCompressor\Utility;

/**
 * Main utility
 */
class MainUtility
{
    /**
     * Extension key
     */
    const EXT_KEY = 'pxa_images_compressor';

    /**
     * Name of field in DB to check if file was compressed
     */
    const DB_FIELD_NAME = 'tx_pxaimagescompressor_compressed';

    /**
     * Extension configuration
     *
     * @var array
     */
    protected static $extensionConfiguration;

    /**
     * Get extension configuration
     *
     * @return array
     */
    public static function getExtensionConfiguration()
    {
        if (self::$extensionConfiguration === null) {
            self::$extensionConfiguration = unserialize(
                $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][self::EXT_KEY]
            );
        }

        return self::$extensionConfiguration ?: [];
    }
}
