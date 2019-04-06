<?php

namespace TGWF\Greencheck\Entity;

/**
 * TGWF\Greencheck\Entity\DatacenterHostingprovider.
 */
class DatacenterHostingprovider
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var bool
     */
    private $approved;

    /**
     * @var \DateTime
     */
    private $created_at;

    /**
     * @var \DateTime
     */
    private $approved_at;

    /**
     * @var Datacenter
     */
    private $datacenter;

    /**
     * @var Hostingprovider
     */
    private $hostingprovider;

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
     * Set approved.
     *
     * @param bool $approved
     *
     * @return DatacenterHostingprovider
     */
    public function setApproved($approved)
    {
        $this->approved = $approved;

        return $this;
    }

    /**
     * Get approved.
     *
     * @return bool
     */
    public function getApproved()
    {
        return $this->approved;
    }

    /**
     * Set created_at.
     *
     * @param \DateTime $createdAt
     *
     * @return DatacenterHostingprovider
     */
    public function setCreatedAt($createdAt)
    {
        $this->created_at = $createdAt;

        return $this;
    }

    /**
     * Get created_at.
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * Set approved_at.
     *
     * @param \DateTime $approvedAt
     *
     * @return DatacenterHostingprovider
     */
    public function setApprovedAt($approvedAt)
    {
        $this->approved_at = $approvedAt;

        return $this;
    }

    /**
     * Get approved_at.
     *
     * @return \DateTime
     */
    public function getApprovedAt()
    {
        return $this->approved_at;
    }

    /**
     * Set datacenter.
     *
     * @param Datacenter $datacenter
     *
     * @return DatacenterHostingprovider
     */
    public function setDatacenter(Datacenter $datacenter = null)
    {
        $this->datacenter = $datacenter;

        return $this;
    }

    /**
     * Get datacenter.
     *
     * @return Datacenter
     */
    public function getDatacenter()
    {
        return $this->datacenter;
    }

    /**
     * Set hostingprovider.
     *
     * @param Hostingprovider $hostingprovider
     *
     * @return DatacenterHostingprovider
     */
    public function setHostingprovider(Hostingprovider $hostingprovider = null)
    {
        $this->hostingprovider = $hostingprovider;

        return $this;
    }

    /**
     * Get hostingprovider.
     *
     * @return Hostingprovider
     */
    public function getHostingprovider()
    {
        return $this->hostingprovider;
    }
}
