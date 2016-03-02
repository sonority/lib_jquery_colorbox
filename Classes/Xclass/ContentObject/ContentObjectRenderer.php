<?php

namespace Sonority\LibJqueryColorbox\Xclass\ContentObject;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Core\Resource\Rendering\RendererRegistry;

/**
 * This class contains all main TypoScript features.
 * This includes the rendering of TypoScript content objects (cObjects).
 * Is the backbone of TypoScript Template rendering.
 *
 * There are lots of functions you can use from your include-scripts.
 * The class is normally instantiated and referred to as "cObj".
 * When you call your own PHP-code typically through a USER or USER_INT cObject then it is this class that instantiates the object and calls the main method. Before it does so it will set (if you are using classes) a reference to itself in the internal variable "cObj" of the object. Thus you can access all functions and data from this class by $this->cObj->... from within you classes written to be USER or USER_INT content objects.
 */
class ContentObjectRenderer extends \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer
{

    /**
     * @var File Current file objects (during iterations over files)
     */
    protected $currentFile = null;

    /**
     * Wraps the input string in link-tags that opens the image in a new window.
     *
     * @param string $string String to wrap, probably an <img> tag
     * @param string|File|FileReference $imageFile The original image file
     * @param array $conf TypoScript properties for the "imageLinkWrap" function
     * @return string The input string, $string, wrapped as configured.
     * @see cImage()
     */
    public function imageLinkWrap($string, $imageFile, $conf)
    {
        $isVideo = null;
        $string = (string) $string;
        $enable = isset($conf['enable.']) ? $this->stdWrap($conf['enable'], $conf['enable.']) : $conf['enable'];
        if (!$enable) {
            return $string;
        }
        $content = (string) $this->typoLink($string, $conf['typolink.']);
        if (isset($conf['file.'])) {
            $imageFile = $this->stdWrap($imageFile, $conf['file.']);
        }

        if ($imageFile instanceof File) {
            $file = $imageFile;
        } elseif ($imageFile instanceof FileReference) {
            if ($imageFile->getProperty('type') === '4') {
                // Get the original file from the file reference
                $file = $imageFile->getOriginalFile();
                $imageFile = $this->getVideoFile($file);
                // Set video to true, so no thumbnail will be generated later
                if (!empty($imageFile)) {
                    // Manually set URL-scheme to get a correct typolink
                    $urlScheme = 'http:';
                    if ($this->getEnvironmentVariable('TYPO3_SSL')) {
                        $urlScheme = 'https:';
                    }
                    $url = $urlScheme . $imageFile;
                    $isVideo = true;
                } else {
                    return '';
                }
            } else {
                $file = $imageFile->getOriginalFile();
            }
        } else {
            if (MathUtility::canBeInterpretedAsInteger($imageFile)) {
                $file = ResourceFactory::getInstance()->getFileObject((int) $imageFile);
            } else {
                $file = ResourceFactory::getInstance()->getFileObjectFromCombinedIdentifier($imageFile);
            }
        }

        // Create imageFileLink if not created with typolink
        if ($content === $string) {
            $parameterNames = array('width', 'height', 'effects', 'bodyTag', 'title', 'wrap');
            $parameters = array();
            $sample = isset($conf['sample.']) ? $this->stdWrap($conf['sample'], $conf['sample.']) : $conf['sample'];
            if ($sample) {
                $parameters['sample'] = 1;
            }
            foreach ($parameterNames as $parameterName) {
                if (isset($conf[$parameterName . '.'])) {
                    $conf[$parameterName] = $this->stdWrap($conf[$parameterName], $conf[$parameterName . '.']);
                }
                if (isset($conf[$parameterName]) && $conf[$parameterName]) {
                    $parameters[$parameterName] = $conf[$parameterName];
                }
            }
            $parametersEncoded = base64_encode(serialize($parameters));
            $hmac = GeneralUtility::hmac(implode('|', array($file->getUid(), $parametersEncoded)));
            $params = '&md5=' . $hmac;
            foreach (str_split($parametersEncoded, 64) as $index => $chunk) {
                $params .= '&parameters' . rawurlencode('[') . $index . rawurlencode(']') . '=' . rawurlencode($chunk);
            }
            if (!$isVideo) {
                $url = $this->getTypoScriptFrontendController()->absRefPrefix . 'index.php?eID=tx_cms_showpic&file=' . $file->getUid() . $params;
                $directImageLink = isset($conf['directImageLink.']) ? $this->stdWrap($conf['directImageLink'],
                        $conf['directImageLink.']) : $conf['directImageLink'];
                if ($directImageLink) {
                    $imgResourceConf = array(
                        'file' => $imageFile,
                        'file.' => $conf
                    );
                    $url = $this->cObjGetSingle('IMG_RESOURCE', $imgResourceConf);
                    if (!$url) {
                        // If no imagemagick / gm is available
                        $url = $imageFile;
                    }
                }
            }
            // Create TARGET-attribute only if the right doctype is used
            $target = '';
            $xhtmlDocType = $this->getTypoScriptFrontendController()->xhtmlDoctype;
            if ($xhtmlDocType !== 'xhtml_strict' && $xhtmlDocType !== 'xhtml_11' && $xhtmlDocType !== 'xhtml_2') {
                $target = isset($conf['target.']) ? (string) $this->stdWrap($conf['target'], $conf['target.']) : (string) $conf['target'];
                if ($target === '') {
                    $target = 'thePicture';
                }
            }
            $a1 = '';
            $a2 = '';
            $conf['JSwindow'] = isset($conf['JSwindow.']) ? $this->stdWrap($conf['JSwindow'], $conf['JSwindow.']) : $conf['JSwindow'];
            if ($conf['JSwindow']) {
                if ($conf['JSwindow.']['altUrl'] || $conf['JSwindow.']['altUrl.']) {
                    $altUrl = isset($conf['JSwindow.']['altUrl.']) ? $this->stdWrap($conf['JSwindow.']['altUrl'],
                            $conf['JSwindow.']['altUrl.']) : $conf['JSwindow.']['altUrl'];
                    if ($altUrl) {
                        $url = $altUrl . ($conf['JSwindow.']['altUrl_noDefaultParams'] ? '' : '?file=' . rawurlencode($imageFile) . $params);
                    }
                }

                $processedFile = $file->process('Image.CropScaleMask', $conf);
                $JSwindowExpand = isset($conf['JSwindow.']['expand.']) ? $this->stdWrap($conf['JSwindow.']['expand'],
                        $conf['JSwindow.']['expand.']) : $conf['JSwindow.']['expand'];
                $offset = GeneralUtility::intExplode(',', $JSwindowExpand . ',');
                $newWindow = isset($conf['JSwindow.']['newWindow.']) ? $this->stdWrap($conf['JSwindow.']['newWindow'],
                        $conf['JSwindow.']['newWindow.']) : $conf['JSwindow.']['newWindow'];
                $onClick = 'openPic('
                    . GeneralUtility::quoteJSvalue($this->getTypoScriptFrontendController()->baseUrlWrap($url)) . ','
                    . '\'' . ($newWindow ? md5($url) : 'thePicture') . '\','
                    . GeneralUtility::quoteJSvalue('width=' . ($processedFile->getProperty('width') + $offset[0])
                        . ',height=' . ($processedFile->getProperty('height') + $offset[1]) . ',status=0,menubar=0')
                    . '); return false;';
                $a1 = '<a href="' . htmlspecialchars($url) . '"'
                    . ' onclick="' . htmlspecialchars($onClick) . '"'
                    . ($target !== '' ? ' target="' . $target . '"' : '')
                    . $this->getTypoScriptFrontendController()->ATagParams . '>';
                $a2 = '</a>';
                $this->getTypoScriptFrontendController()->setJS('openPic');
            } else {
                $conf['linkParams.']['parameter'] = $url;
                $string = $this->typoLink($string, $conf['linkParams.']);
            }
            if (isset($conf['stdWrap.'])) {
                $string = $this->stdWrap($string, $conf['stdWrap.']);
            }
            $content = $a1 . $string . $a2;
        }
        return $content;
    }

    /**
     * Get the source-URL from the rendered onlineMediaObject
     *
     * @param File $file The original image file
     * @return string The source URL
     */
    protected function getVideoFile(File $file)
    {
        // Get OnlineMediaHelper
        $fileRenderer = RendererRegistry::getInstance()->getRenderer($file);
        // Render the media according to the configured renderer
        $renderedOnlineMedia = $fileRenderer->render($file, 0, 0);
        // Get URL from generated code
        $src = [];
        preg_match_all('/src="(\/\/[^"]*)"/i', $renderedOnlineMedia, $src);
        if (is_array($src) && is_array($src[1]) && !empty($src[1][0])) {
            return $src[1][0];
        } else {
            return '';
        }
    }

}
