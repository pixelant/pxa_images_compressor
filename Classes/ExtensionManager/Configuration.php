<?php

namespace Pixelant\PxaImagesCompressor\ExtensionManager;

use Pixelant\PxaImagesCompressor\Utility\MainUtility;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageRendererResolver;
use TYPO3\CMS\Core\Utility\CommandUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Check available optimize libs in extension manager
 *
 * @package Pixelant\PxaImagesCompressor\ExtensionManager
 */
class Configuration
{
    /**
     * Returns an Extension Manager field for selecting domains.
     *
     * @param array $params
     * @param \TYPO3\CMS\Extensionmanager\ViewHelpers\Form\TypoScriptConstantsViewHelper $pObj
     * @return string
     */
    public function checkAvailableCommands(
        /** @noinspection PhpUnusedParameterInspection */ array $params,
        /** @noinspection PhpUnusedParameterInspection */ $pObj
    ) {
        $errors = [];

        CommandUtility::exec('jpegoptim --help', $output, $result);
        if ($result !== 0) {
            $errors[] = $this->sL('ext_conf.jpegoptim2_not_found');
        }
        unset($result);

        CommandUtility::exec('optipng --h', $output, $result);
        if ($result !== 0) {
            $errors[] = $this->sL('ext_conf.optipng_not_found');
        }

        if (empty($errors)) {
            $out = $this->renderMessages([$this->sL('ext_conf.command_ok')], FlashMessage::OK);
        } else {
            $out = $this->renderMessages($errors, FlashMessage::ERROR);
        }

        return implode(LF, $out);
    }

    /**
     * Render flash messages
     *
     * @param array $errors
     * @param $type
     * @return array
     */
    protected function renderMessages(array $errors, $type)
    {
        $messages = [];

        foreach ($errors as $error) {
            /** @var FlashMessage $flashMessage */
            $flashMessage = GeneralUtility::makeInstance(
                FlashMessage::class,
                $error,
                '',
                $type,
                true
            );
            switch (true) {
                case version_compare(TYPO3_version, '7.6.99', '<='):
                    $messages[] = $flashMessage->render();
                    break;
                case version_compare(TYPO3_version, '8.7', '<'):
                    $messages[] = $flashMessage->getMessageAsMarkup();
                    break;
                default:
                    $messages[] = GeneralUtility::makeInstance(FlashMessageRendererResolver::class)->resolve()->render(
                        [$flashMessage]
                    );
                    break;
            }
        }

        return $messages;
    }

    /**
     * Translates a message.
     *
     * @param string $key
     * @return string
     */
    protected function sL($key)
    {
        return $this->getLanguageService()->sL(
            'LLL:EXT:' . MainUtility::EXT_KEY . '/Resources/Private/Language/locallang_db.xlf:' . $key
        );
    }

    /**
     * @return \TYPO3\CMS\Lang\LanguageService
     */
    protected function getLanguageService()
    {
        return $GLOBALS['LANG'];
    }
}
