<?php

namespace TGWF\Greencheck\Entity;

/**
 * TGWF\Greencheck\Entity\Tld.
 */
class Tld
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $tld;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set tld.
     *
     * @param string $tld
     *
     * @return Tld
     */
    public function setTld($tld)
    {
        $this->tld = $tld;

        return $this;
    }

    /**
     * Get tld.
     *
     * @return string
     */
    public function getTld()
    {
        return $this->tld;
    }
}
