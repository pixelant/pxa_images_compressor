<?php
declare(strict_types=1);

namespace Pixelant\PxaImagesCompressor\Form\Element;

use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;
use TYPO3\CMS\Core\Utility\CommandUtility;

/**
 * Class AvailableCommand
 * @package Pixelant\PxaImagesCompressor\Form\Element
 */
class OptimizerStatus extends AbstractFormElement
{

    /**
     * Handler for single nodes
     *
     * @return array As defined in initializeResultArray() of AbstractNode
     */
    public function render(): array
    {
        $result = $this->initializeResultArray();

        $errors = [];

        foreach (['jpegoptim --help', 'optipng --h'] as $command) {
            CommandUtility::exec($command, $output, $resultOfCommand);

            if ($resultOfCommand !== 0) {
                $errors[] = $this->sL('st.status.execute_status', [$command, $resultOfCommand]);
            }
        }

        if (empty($errors)) {
            $result['html'] = sprintf(
                '<p class="bg-success" style="padding: 15px;">%s</p>',
                $this->sL('st.status.command_ok')
            );
        } else {
            $result['html'] = sprintf(
                '<p class="bg-danger" style="padding: 15px;">%s</p>',
                implode('<br><br>', $errors)
            );
        }

        return $result;
    }

    /**
     * Translates a message.
     *
     * @param string $key
     * @param array $arguments
     * @return string
     */
    protected function sL(string $key, array $arguments = null): ?string
    {
        $label = $this->getLanguageService()->sL(
            'LLL:EXT:pxa_images_compressor/Resources/Private/Language/locallang_db.xlf:' . $key
        );

        return empty($arguments) ? $label : vsprintf($label, $arguments);
    }
}
