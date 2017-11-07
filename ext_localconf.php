<?php
defined('TYPO3_MODE') || die('Access denied.');

call_user_func(
    function () {
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
    }
);
