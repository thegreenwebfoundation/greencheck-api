<?php

namespace TGWF\Greencheck\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TGWF\Greencheck\Entity\Tld
 */
class Tld
{
    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var string $tld
     */
    private $tld;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set tld
     *
     * @param string $tld
     * @return Tld
     */
    public function setTld($tld)
    {
        $this->tld = $tld;
    
        return $this;
    }

    /**
     * Get tld
     *
     * @return string
     */
    public function getTld()
    {
        return $this->tld;
    }
}
