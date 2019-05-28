<?php
defined('TYPO3_MODE') || die('Access denied.');

(function () {
    /** @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher $signalSlotDispatcher */
    $signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
        TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class
    );
    // Post processing
    $signalSlotDispatcher->connect(
        \TYPO3\CMS\Core\Resource\ResourceStorage::class,
        \TYPO3\CMS\Core\Resource\Service\FileProcessingService::SIGNAL_PostFileProcess,
        \Pixelant\PxaImagesCompressor\SignalSlotDispatchers\FileProcessingSignalSlotDispatcher::class,
        'handlePostFileProcess'
    );

    // Register a node for site configuration
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1558962900678] = [
        'nodeName' => 'pxaOptimizerStatus',
        'priority' => 40,
        'class' => \Pixelant\PxaImagesCompressor\Form\Element\OptimizerStatus::class
    ];
})();
