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

use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * A view helper for creating a link for an image/video popup.
 * Taken from fluid_styled_content, added grouping of image-popups for colorbox
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
class ClickEnlargeViewHelper extends \TYPO3\CMS\FluidStyledContent\ViewHelpers\Link\ClickEnlargeViewHelper
{

    /**
     * Render the view helper
     *
     * @return string
     */
    public function render()
    {
        return self::renderStatic(
                $this->arguments, $this->buildRenderChildrenClosure(), $this->renderingContext
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
            // Group images by content-element or by page
            $data = $renderingContext->getTemplateVariableContainer()->get('data');
            if (!empty($data['image_group'])) {
                $groupBy = $image->getProperty('uid_foreign');
            } else {
                $groupBy = $image->getProperty('pid');
            }
        }
        $configuration = self::getTypoScriptService()->convertPlainArrayToTypoScriptArray($arguments['configuration']);
        $content = $renderChildrenClosure();
        $configuration['enable'] = true;
        // Replace '{field:uid}' with the PID of the current page or with the ID of the current record
        $configuration['linkParams.']['ATagParams.']['dataWrap'] = str_replace('{field:uid}', $groupBy,
            $configuration['linkParams.']['ATagParams.']['dataWrap']);
        return self::getContentObjectRenderer()->imageLinkWrap($content, $image, $configuration);
    }

}
