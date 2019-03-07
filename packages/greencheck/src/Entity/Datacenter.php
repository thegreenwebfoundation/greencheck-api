<?php

namespace TGWF\Greencheck\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TGWF\Greencheck\Entity\Datacenter
 */
class Datacenter
{
    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var string $naam
     */
    private $naam;

    /**
     * @var string $website
     */
    private $website;

    /**
     * @var string $model
     */
    private $model;

    /**
     * @var string $countrydomain
     */
    private $countrydomain;

    /**
     * @var boolean $showonwebsite
     */
    private $showonwebsite;

    /**
     * @var float $pue
     */
    private $pue;

    /**
     * @var boolean $mja3
     */
    private $mja3;

    /**
     * @var boolean $greengrid
     */
    private $greengrid;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    private $hostingproviders;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    private $certificates;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->hostingproviders = new \Doctrine\Common\Collections\ArrayCollection();
        $this->certificates = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
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
     * Set naam
     *
     * @param string $naam
     * @return Datacenter
     */
    public function setNaam($naam)
    {
        $this->naam = $naam;
    
        return $this;
    }

    /**
     * Get naam
     *
     * @return string
     */
    public function getNaam()
    {
        return $this->naam;
    }

    /**
     * Set website
     *
     * @param string $website
     * @return Datacenter
     */
    public function setWebsite($website)
    {
        $this->website = $website;
    
        return $this;
    }

    /**
     * Get website
     *
     * @return string
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * Set model
     *
     * @param string $model
     * @return Datacenter
     */
    public function setModel($model)
    {
        $this->model = $model;
    
        return $this;
    }

    /**
     * Get model
     *
     * @return string
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Set countrydomain
     *
     * @param string $countrydomain
     * @return Datacenter
     */
    public function setCountrydomain($countrydomain)
    {
        $this->countrydomain = $countrydomain;
    
        return $this;
    }

    /**
     * Get countrydomain
     *
     * @return string
     */
    public function getCountrydomain()
    {
        return $this->countrydomain;
    }

    /**
     * Set showonwebsite
     *
     * @param boolean $showonwebsite
     * @return Datacenter
     */
    public function setShowonwebsite($showonwebsite)
    {
        $this->showonwebsite = $showonwebsite;
    
        return $this;
    }

    /**
     * Get showonwebsite
     *
     * @return boolean
     */
    public function getShowonwebsite()
    {
        return $this->showonwebsite;
    }

    /**
     * Set pue
     *
     * @param float $pue
     * @return Datacenter
     */
    public function setPue($pue)
    {
        $this->pue = $pue;
    
        return $this;
    }

    /**
     * Get pue
     *
     * @return float
     */
    public function getPue()
    {
        return $this->pue;
    }

    /**
     * Set mja3
     *
     * @param boolean $mja3
     * @return Datacenter
     */
    public function setMja3($mja3)
    {
        $this->mja3 = $mja3;
    
        return $this;
    }

    /**
     * Get mja3
     *
     * @return boolean
     */
    public function getMja3()
    {
        return $this->mja3;
    }

    /**
     * Set greengrid
     *
     * @param boolean $greengrid
     * @return Datacenter
     */
    public function setGreengrid($greengrid)
    {
        $this->greengrid = $greengrid;
    
        return $this;
    }

    /**
     * Get greengrid
     *
     * @return boolean
     */
    public function getGreengrid()
    {
        return $this->greengrid;
    }

    /**
     * Add hostingproviders
     *
     * @param TGWF\Greencheck\Entity\DatacenterHostingprovider $hostingproviders
     * @return Datacenter
     */
    public function addHostingprovider(\TGWF\Greencheck\Entity\DatacenterHostingprovider $hostingproviders)
    {
        $this->hostingproviders[] = $hostingproviders;
    
        return $this;
    }

    /**
     * Remove hostingproviders
     *
     * @param TGWF\Greencheck\Entity\DatacenterHostingprovider $hostingproviders
     */
    public function removeHostingprovider(\TGWF\Greencheck\Entity\DatacenterHostingprovider $hostingproviders)
    {
        $this->hostingproviders->removeElement($hostingproviders);
    }

    /**
     * Get hostingproviders
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getHostingproviders()
    {
        return $this->hostingproviders;
    }

    /**
     * Add certificates
     *
     * @param TGWF\Greencheck\Entity\DatacenterCertificate $certificates
     * @return Datacenter
     */
    public function addCertificate(\TGWF\Greencheck\Entity\DatacenterCertificate $certificates)
    {
        $this->certificates[] = $certificates;
    
        return $this;
    }

    /**
     * Remove certificates
     *
     * @param TGWF\Greencheck\Entity\DatacenterCertificate $certificates
     */
    public function removeCertificate(\TGWF\Greencheck\Entity\DatacenterCertificate $certificates)
    {
        $this->certificates->removeElement($certificates);
    }

    /**
     * Get certificates
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getCertificates()
    {
        return $this->certificates;
    }
}
