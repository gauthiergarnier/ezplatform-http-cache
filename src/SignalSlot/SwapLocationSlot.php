<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\PlatformHttpCacheBundle\SignalSlot;

use eZ\Publish\Core\SignalSlot\Signal;

/**
 * A slot handling SwapLocationSignal.
 */
class SwapLocationSlot extends AbstractContentSlot
{
    /**
     * @param \eZ\Publish\Core\SignalSlot\Signal\LocationService\SwapLocationSignal $signal
     */
    protected function generateTags(Signal $signal)
    {
        return [
            'content-' . $signal->content1Id,
            'path-' . $signal->location1Id,
            'location-' . $signal->parentLocation1Id,
            'parent-' . $signal->parentLocation1Id,
            'content-' . $signal->content2Id,
            'path-' . $signal->location2Id,
            'location-' . $signal->parentLocation2Id,
            'parent-' . $signal->parentLocation2Id,
        ];
    }

    protected function supports(Signal $signal)
    {
        return $signal instanceof Signal\LocationService\SwapLocationSignal;
    }
}
