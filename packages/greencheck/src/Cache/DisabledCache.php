<?php

namespace TGWF\Greencheck\Cache;

use Doctrine\Common\Cache\CacheProvider;

class DisabledCache extends CacheProvider
{
    /**
     * {@inheritdoc}
     */
    protected function doFetch($id)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    protected function doContains($id)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    protected function doSave($id, $data, $lifeTime = 0)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function doDelete($id)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function doFlush()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function doGetStats()
    {
        return null;
    }
}
