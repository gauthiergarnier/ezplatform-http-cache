<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\PlatformHttpCacheBundle\PurgeClient;

use FOS\HttpCacheBundle\CacheManager;
use eZ\Publish\Core\MVC\ConfigResolverInterface;

/**
 * Purge client based on FOSHttpCacheBundle.
 */
class VarnishPurgeClient implements PurgeClientInterface
{
    public const INVALIDATE_TOKEN_NAME_PARAM = 'http_cache.varnish_invalidate_token_name';
    public const INVALIDATE_TOKEN_PARAM = 'http_cache.varnish_invalidate_token';

    /**
     * @var \FOS\HttpCacheBundle\CacheManager
     */
    private $cacheManager;

    /**
     * @var \eZ\Publish\Core\MVC\ConfigResolverInterface
     */
    private $configResolver;

    public function __construct(CacheManager $cacheManager, ConfigResolverInterface $configResolver)
    {
        $this->cacheManager = $cacheManager;
        $this->configResolver = $configResolver;
    }

    public function __destruct()
    {
        $this->cacheManager->flush();
    }

    public function purge($tags)
    {
        if (empty($tags)) {
            return;
        }

        // As key only support one tag being invalidated at a time, we loop.
        // These will be queued by FOS\HttpCache\ProxyClient\Varnish and handled on kernel.terminate.
        foreach (array_unique((array)$tags) as $tag) {
            if (is_numeric($tag)) {
                $tag = 'location-' . $tag;
            }

            $headers = [
                'key' => $tag,
                'Host' => empty($_SERVER['SERVER_NAME']) ? 'localhost' : $_SERVER['SERVER_NAME'],
            ];

            $headers = $this->addPurgeAuthHeader($headers);

            $this->cacheManager->invalidatePath(
                '/',
                $headers
            );
        }
    }

    public function purgeAll()
    {
        $headers = [
            'key' => 'ez-all',
            'Host' => empty($_SERVER['SERVER_NAME']) ? 'localhost' : $_SERVER['SERVER_NAME'],
        ];

        $headers = $this->addPurgeAuthHeader($headers);

        $this->cacheManager->invalidatePath(
            '/',
            $headers
        );
    }

    /**
     * Adds an Authentication header for Purge.
     *
     * @param array $headers
     * @return array
     */
    private function addPurgeAuthHeader(array $headers)
    {
        if ($this->configResolver->hasParameter(self::INVALIDATE_TOKEN_NAME_PARAM)
            && $this->configResolver->hasParameter(self::INVALIDATE_TOKEN_PARAM)
            && null !== ($tokenName = $this->configResolver->getParameter(self::INVALIDATE_TOKEN_NAME_PARAM))
            && null !== ($token = $this->configResolver->getParameter(self::INVALIDATE_TOKEN_PARAM))
        ) {
            $headers[$tokenName] = $token;
        }

        return $headers;
    }
}
