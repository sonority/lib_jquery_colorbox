<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "lib_jquery_colorbox".
 *
 * Auto generated 14-02-2016 21:43
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = [
    'title' => 'JS Library: jQuery.colorbox',
    'description' => 'Provides the "Colorbox Plugin for jQuery". Group images/videos by record or by page in colorbox-popups.',
    'category' => 'misc',
    'version' => '0.0.3',
    'state' => 'beta',
    'uploadfolder' => false,
    'createDirs' => '',
    'clearcacheonload' => true,
    'author' => 'Stephan Kellermayr',
    'author_email' => 'stephan.kellermayr@gmail.com',
    'author_company' => 'sonority.at - MULTIMEDIA ART DESIGN',
    'constraints' => [
        'depends' => [
            'typo3' => '7.6.0-7.6.99',
            'fluid_styled_content' => '7.6.0-7.6.99',
        ],
        'conflicts' => [
        ],
        'suggests' => [
            'lib_jquery' => '0.0.1-',
        ]
    ]
];

