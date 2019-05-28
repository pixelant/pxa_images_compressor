<?php

(function () {
    $ll = 'LLL:EXT:pxa_images_compressor/Resources/Private/Language/locallang_db.xlf:';
    $newFields = [
        'pxaDisableImageCompressing' => [
            'label' => $ll . 'st.disableCompressing',
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
                'items' => [
                    [
                        0 => '',
                        1 => '',
                        'labelChecked' => 'Enabled',
                        'labelUnchecked' => 'Disabled',
                        'invertStateDisplay' => true
                    ]
                ],
            ]
        ],
        'pxaJpgCompressingLevel' => [
            'label' => $ll . 'st.jpgCompressingLevel',
            'config' => [
                'type' => 'input',
                'eval' => 'int',
                'size' => 5,
                'default' => 25
            ]
        ],
        'pxaOptimizerStatus' => [
            'label' => $ll . 'st.optimizerStatus',
            'config' => [
                'type' => 'user',
                'renderType' => 'pxaOptimizerStatus'
            ]
        ]
    ];

    // Add new fields to site configuration
    $GLOBALS['SiteConfiguration']['site']['columns'] = $GLOBALS['SiteConfiguration']['site']['columns'] + $newFields;
    $GLOBALS['SiteConfiguration']['site']['types']['0']['showitem'] = str_replace(
        'base,',
        'base, --div--;' . $ll . 'st.tab, pxaDisableImageCompressing, pxaJpgCompressingLevel, pxaOptimizerStatus,',
        $GLOBALS['SiteConfiguration']['site']['types']['0']['showitem']
    );
})();
