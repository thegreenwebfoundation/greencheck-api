<?php

namespace TGWF\Greencheck\Entity;

use BadMethodCallException;
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
