<?php

namespace TGWF\Greencheck\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * TGWF\Greencheck\Entity\Datacenter.
 */
class Datacenter
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $naam;

    /**
     * @var string
     */
    private $website;

    /**
     * @var string
     */
    private $model;

    /**
     * @var string
     */
    private $countrydomain;

    /**
     * @var bool
     */
    private $showonwebsite;

    /**
     * @var float
     */
    private $pue;

    /**
     * @var bool
     */
    private $mja3;

    /**
     * @var bool
     */
    private $greengrid;

    /**
     * @var ArrayCollection
     */
    private $hostingproviders;

    /**
     * @var ArrayCollection
     */
    private $certificates;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->hostingproviders = new ArrayCollection();
        $this->certificates = new ArrayCollection();
    }

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
     * Set naam.
     *
     * @param string $naam
     *
     * @return Datacenter
     */
    public function setNaam($naam)
    {
        $this->naam = $naam;

        return $this;
    }

    /**
     * Get naam.
     *
     * @return string
     */
    public function getNaam()
    {
        return $this->naam;
    }

    /**
     * Set website.
     *
     * @param string $website
     *
     * @return Datacenter
     */
    public function setWebsite($website)
    {
        $this->website = $website;

        return $this;
    }

    /**
     * Get website.
     *
     * @return string
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * Set model.
     *
     * @param string $model
     *
     * @return Datacenter
     */
    public function setModel($model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * Get model.
     *
     * @return string
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Set countrydomain.
     *
     * @param string $countrydomain
     *
     * @return Datacenter
     */
    public function setCountrydomain($countrydomain)
    {
        $this->countrydomain = $countrydomain;

        return $this;
    }

    /**
     * Get countrydomain.
     *
     * @return string
     */
    public function getCountrydomain()
    {
        return $this->countrydomain;
    }

    /**
     * Set showonwebsite.
     *
     * @param bool $showonwebsite
     *
     * @return Datacenter
     */
    public function setShowonwebsite($showonwebsite)
    {
        $this->showonwebsite = $showonwebsite;

        return $this;
    }

    /**
     * Get showonwebsite.
     *
     * @return bool
     */
    public function getShowonwebsite()
    {
        return $this->showonwebsite;
    }

    /**
     * Set pue.
     *
     * @param float $pue
     *
     * @return Datacenter
     */
    public function setPue($pue)
    {
        $this->pue = $pue;

        return $this;
    }

    /**
     * Get pue.
     *
     * @return float
     */
    public function getPue()
    {
        return $this->pue;
    }

    /**
     * Set mja3.
     *
     * @param bool $mja3
     *
     * @return Datacenter
     */
    public function setMja3($mja3)
    {
        $this->mja3 = $mja3;

        return $this;
    }

    /**
     * Get mja3.
     *
     * @return bool
     */
    public function getMja3()
    {
        return $this->mja3;
    }

    /**
     * Set greengrid.
     *
     * @param bool $greengrid
     *
     * @return Datacenter
     */
    public function setGreengrid($greengrid)
    {
        $this->greengrid = $greengrid;

        return $this;
    }

    /**
     * Get greengrid.
     *
     * @return bool
     */
    public function getGreengrid()
    {
        return $this->greengrid;
    }

    /**
     * Add hostingproviders.
     *
     * @param DatacenterHostingprovider $hostingproviders
     *
     * @return Datacenter
     */
    public function addHostingprovider(DatacenterHostingprovider $hostingproviders)
    {
        $this->hostingproviders[] = $hostingproviders;

        return $this;
    }

    /**
     * Remove hostingproviders.
     *
     * @param DatacenterHostingprovider $hostingproviders
     */
    public function removeHostingprovider(DatacenterHostingprovider $hostingproviders)
    {
        $this->hostingproviders->removeElement($hostingproviders);
    }

    /**
     * Get hostingproviders.
     *
     * @return Collection
     */
    public function getHostingproviders()
    {
        return $this->hostingproviders;
    }

    /**
     * Add certificates.
     *
     * @param DatacenterCertificate $certificates
     *
     * @return Datacenter
     */
    public function addCertificate(DatacenterCertificate $certificates)
    {
        $this->certificates[] = $certificates;

        return $this;
    }

    /**
     * Remove certificates.
     *
     * @param DatacenterCertificate $certificates
     */
    public function removeCertificate(DatacenterCertificate $certificates)
    {
        $this->certificates->removeElement($certificates);
    }

    /**
     * Get certificates.
     *
     * @return Collection
     */
    public function getCertificates()
    {
        return $this->certificates;
    }
}
