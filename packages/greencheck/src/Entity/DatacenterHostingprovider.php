<?php

namespace TGWF\Greencheck\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TGWF\Greencheck\Entity\DatacenterHostingprovider
 */
class DatacenterHostingprovider
{
    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var boolean $approved
     */
    private $approved;

    /**
     * @var \DateTime $created_at
     */
    private $created_at;

    /**
     * @var \DateTime $approved_at
     */
    private $approved_at;

    /**
     * @var TGWF\Greencheck\Entity\Datacenter
     */
    private $datacenter;

    /**
     * @var TGWF\Greencheck\Entity\Hostingprovider
     */
    private $hostingprovider;


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
     * Set approved
     *
     * @param boolean $approved
     * @return DatacenterHostingprovider
     */
    public function setApproved($approved)
    {
        $this->approved = $approved;
    
        return $this;
    }

    /**
     * Get approved
     *
     * @return boolean
     */
    public function getApproved()
    {
        return $this->approved;
    }

    /**
     * Set created_at
     *
     * @param \DateTime $createdAt
     * @return DatacenterHostingprovider
     */
    public function setCreatedAt($createdAt)
    {
        $this->created_at = $createdAt;
    
        return $this;
    }

    /**
     * Get created_at
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * Set approved_at
     *
     * @param \DateTime $approvedAt
     * @return DatacenterHostingprovider
     */
    public function setApprovedAt($approvedAt)
    {
        $this->approved_at = $approvedAt;
    
        return $this;
    }

    /**
     * Get approved_at
     *
     * @return \DateTime
     */
    public function getApprovedAt()
    {
        return $this->approved_at;
    }

    /**
     * Set datacenter
     *
     * @param TGWF\Greencheck\Entity\Datacenter $datacenter
     * @return DatacenterHostingprovider
     */
    public function setDatacenter(\TGWF\Greencheck\Entity\Datacenter $datacenter = null)
    {
        $this->datacenter = $datacenter;
    
        return $this;
    }

    /**
     * Get datacenter
     *
     * @return TGWF\Greencheck\Entity\Datacenter
     */
    public function getDatacenter()
    {
        return $this->datacenter;
    }

    /**
     * Set hostingprovider
     *
     * @param TGWF\Greencheck\Entity\Hostingprovider $hostingprovider
     * @return DatacenterHostingprovider
     */
    public function setHostingprovider(\TGWF\Greencheck\Entity\Hostingprovider $hostingprovider = null)
    {
        $this->hostingprovider = $hostingprovider;
    
        return $this;
    }

    /**
     * Get hostingprovider
     *
     * @return TGWF\Greencheck\Entity\Hostingprovider
     */
    public function getHostingprovider()
    {
        return $this->hostingprovider;
    }
}
