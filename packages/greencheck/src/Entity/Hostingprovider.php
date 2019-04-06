<?php

namespace TGWF\Greencheck\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * TGWF\Greencheck\Entity\Hostingproviders.
 *
 * @Gedmo\Loggable
 * @ORM\Table(name="hostingproviders")
 * @ORM\Entity
 */
class Hostingprovider implements \ArrayAccess
{
    const MODEL_COMPENSATION = 'compensatie';
    const MODEL_GREENENERGY = 'groeneenergie';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string
     *
     * @Gedmo\Versioned
     * @ORM\Column(name="naam", type="string", length=255, nullable=false)
     */
    protected $naam;

    /**
     * @var string
     *
     * @Gedmo\Versioned
     * @ORM\Column(name="website", type="string", length=255, nullable=false)
     */
    protected $website;

    /**
     * @var string
     *
     * @Gedmo\Versioned
     * @ORM\Column(name="model", type="string", nullable=false)
     */
    protected $model;

    /**
     * @var string
     *
     * @Gedmo\Versioned
     * @ORM\Column(name="countrydomain", type="string", nullable=false)
     */
    protected $countrydomain;

    /**
     * @var string
     *
     * @Gedmo\Versioned
     * @ORM\Column(name="customer", type="boolean", nullable=false)
     */
    protected $customer = false;

    /**
     * @var string
     *
     * @Gedmo\Versioned
     * @ORM\Column(name="icon", type="string", length=50, nullable=false)
     */
    protected $icon = '';

    /**
     * @var string
     *
     * @Gedmo\Versioned
     * @ORM\Column(name="iconurl", type="string", length=255, nullable=false)
     */
    protected $iconurl = '';

    /**
     * @var bool
     *
     * @Gedmo\Versioned
     * @ORM\Column(name="showonwebsite", type="boolean", nullable=false)
     */
    protected $showonwebsite;

    /**
     * @ORM\OneToMany(targetEntity="HostingproviderCertificate", mappedBy="hostingprovider")
     */
    protected $certificates;

    /**
     * @ORM\OneToMany(targetEntity="GreencheckAs", mappedBy="hostingprovider")
     */
    protected $asnumbers;

    /**
     * @ORM\OneToMany(targetEntity="GreencheckIp", mappedBy="hostingprovider")
     */
    protected $iprecords;

    /**
     * @ORM\OneToMany(targetEntity="Greencheck", mappedBy="hostingprovider")
     */
    protected $greencheckrecords;

    /**
     * \\ORM\OneToMany(targetEntity="DatacenterHostingprovider",mappedBy="hostingprovider").
     **/
    protected $datacenters;

    /**
     * @var bool
     *
     * @ORM\Column(name="partner", type="string")
     */
    private $partner;

    public function __construct()
    {
        $this->asnumbers = new ArrayCollection();
        $this->iprecords = new ArrayCollection();
        $this->greencheckrecords = new ArrayCollection();
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
     */
    public function setNaam($naam)
    {
        $this->naam = $naam;
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
     */
    public function setWebsite($website)
    {
        $this->website = $website;
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
     */
    public function setModel($model)
    {
        if (!in_array($model, [self::MODEL_COMPENSATION, self::MODEL_GREENENERGY])) {
            throw new \InvalidArgumentException('Invalid model');
        }
        $this->model = $model;
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
     * Set showonwebsite.
     *
     * @param bool $showonwebsite
     */
    public function setShowonwebsite($showonwebsite)
    {
        $this->showonwebsite = $showonwebsite;
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
     * Add asnumbers.
     *
     * @param TGWF\AdminBundle\Entity\GreencheckAs $asnumbers
     */
    public function addGreencheckAs(GreencheckAs $asnumbers)
    {
        $this->asnumbers[] = $asnumbers;
    }

    /**
     * Get asnumbers.
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getAsnumbers()
    {
        return $this->asnumbers;
    }

    /**
     * Add iprecords.
     *
     * @param TGWF\AdminBundle\Entity\GreencheckIp $iprecords
     */
    public function addGreencheckIp(GreencheckIp $iprecords)
    {
        $this->iprecords[] = $iprecords;
    }

    /**
     * Get iprecords.
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getIprecords()
    {
        $data = [];
        foreach ($this->iprecords as $ip) {
            $data[ip2long($ip->getIpStart())] = $ip;
        }
        ksort($data);

        return $data;
    }

    public function __toString()
    {
        return $this->naam;
    }

    /**
     * Set countrydomain.
     *
     * @param string $countrydomain
     */
    public function setCountrydomain($countrydomain)
    {
        $this->countrydomain = $countrydomain;
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
     * Add greencheckrecords.
     *
     * @param TGWF\AdminBundle\Entity\Greencheck $greencheckrecords
     */
    public function addGreencheck(Greencheck $greencheckrecords)
    {
        $this->greencheckrecords[] = $greencheckrecords;
    }

    /**
     * Get greencheckrecords.
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getGreencheckrecords()
    {
        return $this->greencheckrecords;
    }

    /**
     * Get asnumbersapprove.
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getAsnumbersapprove()
    {
        return $this->asnumbersapprove;
    }

    /**
     * Get iprecordsapprove.
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getIprecordsapprove()
    {
        return $this->iprecordsapprove;
    }

    /**
     * Add datacenter.
     *
     * @param TGWF\AdminBundle\Entity\Datacenter $datacenter
     */
    public function addDatacenter(Datacenter $datacenter)
    {
        $this->datacenters[] = $datacenter;
    }

    /**
     * Get datacenters.
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getDatacenters()
    {
        return $this->datacenters;
    }

    /**
     * Add datacenters.
     *
     * @param TGWF\AdminBundle\Entity\DatacenterHostingprovider $datacenters
     */
    public function addDatacenterHostingprovider(DatacenterHostingprovider $datacenters)
    {
        $this->datacenters[] = $datacenters;
    }

    /**
     * Set customer.
     *
     * @param bool $customer
     */
    public function setCustomer($customer)
    {
        $this->customer = $customer;
    }

    /**
     * Get customer.
     *
     * @return bool
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * Set icon.
     *
     * @param string $icon
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;
    }

    /**
     * Get icon.
     *
     * @return string
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * Set iconurl.
     *
     * @param string $iconurl
     */
    public function setIconurl($iconurl)
    {
        $this->iconurl = $iconurl;
    }

    /**
     * Get iconurl.
     *
     * @return string
     */
    public function getIconurl()
    {
        return $this->iconurl;
    }

    /**
     * Add certificates.
     *
     * @param TGWF\AdminBundle\Entity\Certificate $certificates
     *
     * @return Hostingprovider
     */
    public function addCertificate(HostingproviderCertificate $certificates)
    {
        $this->certificates[] = $certificates;

        return $this;
    }

    /**
     * Get certificates.
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getCertificates()
    {
        return $this->certificates;
    }

    /**
     * Remove certificates.
     *
     * @param TGWF\AdminBundle\Entity\Certificate $certificates
     */
    public function removeCertificate(HostingproviderCertificate $certificates)
    {
        $this->certificates->removeElement($certificates);
    }

    /**
     * Add asnumbers.
     *
     * @param TGWF\AdminBundle\Entity\GreencheckAs $asnumbers
     *
     * @return Hostingprovider
     */
    public function addAsnumber(GreencheckAs $asnumbers)
    {
        $this->asnumbers[] = $asnumbers;

        return $this;
    }

    /**
     * Remove asnumbers.
     *
     * @param TGWF\AdminBundle\Entity\GreencheckAs $asnumbers
     */
    public function removeAsnumber(GreencheckAs $asnumbers)
    {
        $this->asnumbers->removeElement($asnumbers);
    }

    /**
     * Add iprecords.
     *
     * @param TGWF\AdminBundle\Entity\GreencheckIp $iprecords
     *
     * @return Hostingprovider
     */
    public function addIprecord(GreencheckIp $iprecords)
    {
        $this->iprecords[] = $iprecords;

        return $this;
    }

    /**
     * Remove iprecords.
     *
     * @param GreencheckIp $iprecords
     */
    public function removeIprecord(GreencheckIp $iprecords)
    {
        $this->iprecords->removeElement($iprecords);
    }

    /**
     * Add greencheckrecords.
     *
     * @param Greencheck $greencheckrecords
     *
     * @return Hostingprovider
     */
    public function addGreencheckrecord(Greencheck $greencheckrecords)
    {
        $this->greencheckrecords[] = $greencheckrecords;

        return $this;
    }

    /**
     * Remove greencheckrecords.
     *
     * @param Greencheck $greencheckrecords
     */
    public function removeGreencheckrecord(Greencheck $greencheckrecords)
    {
        $this->greencheckrecords->removeElement($greencheckrecords);
    }

    /**
     * Remove datacenters.
     *
     * @param DatacenterHostingprovider $datacenters
     */
    public function removeDatacenter(DatacenterHostingprovider $datacenters)
    {
        $this->datacenters->removeElement($datacenters);
    }

    public function setEntity(Hostingprovider $hp)
    {
        $this->setNaam($hp->getNaam());
        $this->setWebsite($hp->getWebsite());
        $this->setModel($hp->getModel());
        $this->setCountrydomain($hp->getCountrydomain());
        $this->setCustomer($hp->getCustomer());
        $this->setIcon($hp->getIcon());
        $this->setIconurl($hp->getIconurl());
        $this->setShowonwebsite($hp->getShowonwebsite());
        $this->setPartner($hp->getPartner());
        $this->id = $hp->getId();
    }

    public function offsetExists($offset)
    {
        // In this example we say that exists means it is not null
        $value = $this->{"get$offset"}();

        return null !== $value;
    }

    public function offsetSet($offset, $value)
    {
        throw new BadMethodCallException('Array access of class '.get_class($this).' is read-only!');
    }

    public function offsetGet($offset)
    {
        return $this->{"get$offset"}();
    }

    public function offsetUnset($offset)
    {
        throw new BadMethodCallException('Array access of class '.get_class($this).' is read-only!');
    }

    /**
     * @return bool
     */
    public function getPartner()
    {
        return $this->partner;
    }

    /**
     * @param bool $partner
     */
    public function setPartner($partner)
    {
        $this->partner = $partner;
    }
}
