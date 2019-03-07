<?php

namespace TGWF\Greencheck\Entity;

/**
 * TGWF\AdminBundle\Entity\Tryout
 *
 */
class Tryout
{
    /**
     * @var string $url
     *
     */
    private $url;

    /**
     * Set tld
     *
     * @param string $tld
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * Get tld
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }
    
    public function __toString()
    {
        return $this->url;
    }
}
