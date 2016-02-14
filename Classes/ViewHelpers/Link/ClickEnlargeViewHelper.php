<?php

namespace Sonority\LibJqueryColorbox\ViewHelpers\Link;

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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Service\TypoScriptService;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;
use Sonority\LibJqueryColorbox\ContentObject\AlternativeContentObjectRenderer;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\FluidStyledContent\ViewHelpers\Link\ClickEnlargeViewHelper as FluidClickEnlargeViewHelper;

/**
 * A view helper for creating a link for an image/video popup.
 * Taken from fluid_styled_content, added video-popups for colorbox
 *
 * = Example =
 *
 * <code title="enlarge image on click">
 * <colorbox:link.clickEnlarge image="{image}" configuration="{settings.images.popup}"><img src=""></colorbox:link.clickEnlarge>
 * </code>
 *
 * <output>
 * <a href="url" onclick="javascript" target="thePicture"><img src=""></a>
 * </output>
 */
class ClickEnlargeViewHelper extends FluidClickEnlargeViewHelper
{

    /**
     * Render the view helper
     *
     * @return string
     */
    public function render()
    {
        return self::renderStatic(
            $this->arguments,
            $this->buildRenderChildrenClosure(),
            $this->renderingContext
        );
    }

    /**
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return string
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        $image = $arguments['image'];
        if ($image instanceof FileInterface) {
            self::getContentObjectRenderer()->setCurrentFile($image);
        }
        $configuration = self::getTypoScriptService()->convertPlainArrayToTypoScriptArray($arguments['configuration']);
        $content = $renderChildrenClosure();
        $configuration['enable'] = true;
        // Get the type of the current media-object, and if it's a video, use the alternative
        // ContentObjectRenderer which contains a modified version of imageLinkWrap()
        $type = $image instanceof FileReference ? $image->getProperty('type') : null;
        if ($type === '4') {
            // TODO:
            // Replace '{field:uid}' with the PID of the parent page (it is empty for some reason ...?)
            $configuration['linkParams.']['ATagParams.']['dataWrap'] = str_replace('{field:uid}', $image->getProperty('pid'), $configuration['linkParams.']['ATagParams.']['dataWrap']);
            // TODO:
            // Add another CSS-class to let the javascript handle this video-object with different parameters ('iframe:true;' in this case)
            $configuration['linkParams.']['ATagParams.']['dataWrap'] = str_replace('class="', 'class="lightbox-video ', $configuration['linkParams.']['ATagParams.']['dataWrap']);
            return self::getContentObjectRenderer()->imageLinkWrap($content, $image, $configuration);
        } else {
            return self::getContentObjectRenderer()->imageLinkWrap($content, $image, $configuration);
        }
    }

    /**
     * @return ContentObjectRenderer
     */
    protected static function getContentObjectRenderer()
    {
        return GeneralUtility::makeInstance(AlternativeContentObjectRenderer::class);
    }

}
