<?php

namespace TGWF\Greencheck;

use TGWF\Greencheck\Entity\Hostingprovider;

/**
 * Sitecheck Result class.
 *
 * The sitecheck result class is a storage container for the results the greencheck returns.
 *
 * @author Arend-Jan Tetteroo (aj@arendjantetteroo.nl)
 */
class SitecheckResult
{
    /**
     * @var string url, the checked url
     */
    private $checkedUrl;

    /**
     * @var \DateTime, the date the url was checked
     */
    private $checkedAt;

    /**
     * Is the checked url green or not.
     *
     * @var bool
     */
    private $green = false;

    /**
     * Is data available for the domain of the checked url.
     *
     * @var bool
     */
    private $data = true;

    /**
     * Is data available for the domain of the checked url.
     *
     * @var bool
     */
    private $cached = false;

    /**
     * Hosting provider id, null if no green hoster.
     *
     * @var int
     */
    private $idHostingProvider = null;

    /**
     * Hosting provider.
     *
     * @var Hostingprovider
     */
    private $hostingProvider = null;

    /**
     * Is this result powered by a green energy provider.
     *
     * @var bool
     */
    private $poweredby = false;

    /**
     * Energy provider.
     *
     * @todo remove?
     *
     * @var
     */
    private $energyProvider;

    /**
     * Organisation name for energy output.
     *
     * @var string
     */
    private $organisation;

    /**
     * Ip belonging to the checked url.
     *
     * @var array
     */
    private $ip;

    /**
     * Array describing how the result was matched.
     *
     * @var array
     */
    private $matchtype = [];

    private $calledfrom;

    /**
     * Setup the sitecheck result object.
     *
     * @param string $url
     * @param $ip
     * @throws \Exception
     */
    public function __construct($url, $ip)
    {
        $this->checkedUrl = $url;
        $this->ip = $ip;
        $this->checkedAt = new \DateTime('now');
    }

    /**
     * Rgister on what was matched.
     * @param $id
     * @param $type
     * @param string $identifier
     */
    public function setMatch($id, $type, $identifier = '')
    {
        $this->matchtype = ['id' => $id, 'type' => $type, 'identifier' => $identifier];
    }

    /**
     * return the match.
     */
    public function getMatch()
    {
        return $this->matchtype;
    }

    /**
     * Is the checked url green?
     *
     * @return bool
     */
    public function isGreen()
    {
        return $this->green;
    }

    /**
     * Get the checked url.
     *
     * @return string
     */
    public function getCheckedUrl()
    {
        return $this->checkedUrl;
    }

    /**
     * Get the checked url.
     *
     * @return \DateTime
     */
    public function getCheckedAt()
    {
        return $this->checkedAt;
    }

    /**
     * Get the checked url.
     *
     * @return \DateTime
     */
    public function setCheckedAt($date)
    {
        $this->checkedAt = $date;
    }

    /**
     * Set the green variable.
     *
     * @param bool $green
     */
    public function setGreen($green)
    {
        $this->green = $green;
    }

    /**
     * Return data variable.
     */
    public function isData()
    {
        return $this->data;
    }

    /**
     * Set the data variable.
     *
     * @param bool $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * Return cached variable.
     */
    public function isCached()
    {
        return $this->cached;
    }

    /**
     * Set the data variable.
     *
     * @param bool $cached
     */
    public function setCached($cached)
    {
        $this->cached = $cached;
    }

    /**
     * Set the hosting provider id.
     *
     * @param int $id
     */
    public function setHostingProviderId($id)
    {
        $this->idHostingProvider = $id;
    }

    /**
     * Get the hosting provider id.
     *
     * @return int
     */
    public function getHostingProviderId()
    {
        return $this->idHostingProvider;
    }

    /**
     * Is the checked url hosted by a hosting provider?
     *
     * @return bool
     */
    public function isHostingProvider()
    {
        return $this->idHostingProvider !== null;
    }

    /**
     * Set the hosting provider.
     *
     * @param Hostingprovider $hp
     */
    public function setHostingProvider($hp)
    {
        $this->hostingProvider = $hp;
    }

    /**
     * Get the hosting provider.
     *
     * @return Hostingprovider
     */
    public function getHostingProvider()
    {
        return $this->hostingProvider;
    }

    /**
     * Get the ip adress.
     *
     * @return string
     */
    public function getIpAddress($type = 'ipv4')
    {
        if (isset($this->ip[$type])) {
            return $this->ip[$type];
        }

        return $this->ip['ipv4'];
    }

    /**
     * Is the company of the checked url powered by a green energy provider?
     *
     * @return bool
     */
    public function isPoweredBy()
    {
        return $this->poweredby;
    }

    /**
     * @param $provider
     * @param $organisation
     */
    public function setPoweredBy($provider, $organisation)
    {
        $this->poweredby = true;
        $this->energyProvider = $provider;
        $this->organisation = $organisation;
    }

    /**
     * @return bool
     */
    public function getEnergyProviderId()
    {
        if ($this->isPoweredBy()) {
            return $this->energyProvider->getId();
        }

        return false;
    }

    /**
     * @return bool
     */
    public function getEnergyProvider()
    {
        if ($this->isPoweredBy()) {
            return $this->energyProvider;
        }

        return false;
    }

    /**
     * @return bool|string
     */
    public function getOrganisation()
    {
        if ($this->isPoweredBy()) {
            return $this->organisation;
        }

        return false;
    }

    /**
     * @param $called
     */
    public function setCalledFrom($called)
    {
        $this->calledfrom = $called;
    }

    /**
     * @param $key
     * @return bool
     */
    public function getCalledFrom($key)
    {
        if (isset($this->calledfrom[$key])) {
            return $this->calledfrom[$key];
        }

        return false;
    }
}
