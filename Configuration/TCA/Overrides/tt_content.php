<?php

defined('TYPO3_MODE') or die();

call_user_func(
    function ($extKey) {

    $fieldLanguageFilePrefix = 'LLL:EXT:' . $extKey . '/Resources/Private/Language/locallang_db.xlf:';

    // Define field(s)
    $additionalColumns = [
        'image_group' => [
            'exclude' => true,
            'label' => $fieldLanguageFilePrefix . 'tt_content.image_group',
            'displayCond' => 'FIELD:image_zoom:>:0',
            'config' => [
                'type' => 'check',
                'default' => 0,
                'items' => [
                    ['LLL:EXT:lang/locallang_core.xml:labels.enabled', 1]
                ]
            ]
        ]
    ];
    // Update fields when image_zoom changes
    $GLOBALS['TCA']['tt_content']['ctrl']['requestUpdate'] .= ',image_zoom';
    // Add custom fields to the backend-forms
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette(
        'tt_content', 'imagelinks', 'image_group;' . $fieldLanguageFilePrefix . 'tt_content.image_group'
    );
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('tt_content', $additionalColumns, 1);
}, 'lib_jquery_colorbox'
);
