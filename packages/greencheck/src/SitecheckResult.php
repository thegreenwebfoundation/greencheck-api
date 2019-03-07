<?php
namespace TGWF\Greencheck;

use TGWF\Greencheck\Entity\Hostingprovider;

/**
 * Sitecheck Result class
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
    protected $_checkedUrl = null;

    /**
     * @var datetime date, the date the url was checked
     */
    protected $_checkedAt = null;

    /**
     * Is the checked url green or not
     * @var boolean
     */
    protected $_green = false;

    /**
     * Is data available for the domain of the checked url
     * @var boolean
     */
    protected $_data = true;

    /**
     * Is data available for the domain of the checked url
     * @var boolean
     */
    protected $_cached = false;

    /**
     * Hosting provider id, null if no green hoster
     * @var int
     */
    protected $_idHostingProvider = null;
    
    /**
     * Hosting provider
     * @var Zend_Db_Table_Row
     */
    protected $_HostingProvider = null;

    /**
     * Is this result powered by a green energy provider
     * @var type
     */
    protected $_poweredby = false;
    
    /**
     * Energy provider
     * @var Zend_Db_Table_Row
     */
    protected $_EnergyProvider = null;

    /**
     * Organisation name for energy output
     * @var String
     */
    protected $_organisation = null;
    
    /**
     * Ip belonging to the checked url
     * @var string
     */
    protected $_ip = null;

    /**
     * Array describing how the result was matched
     *
     * @var array
     */
    protected $_matchtype = array();

    /**
     * Setup the sitecheck result object
     *
     * @param  string $url
     * @return void
     */
    public function __construct($url, $ip)
    {
        $this->_checkedUrl = $url;
        $this->_ip = $ip;
        $this->_checkedAt  = new \DateTime('now');
    }

    /**
     * Rgister on what was matched
     */
    public function setMatch($id, $type, $identifier = '')
    {
        $this->_matchtype = array('id' => $id, 'type' => $type, 'identifier' => $identifier);
    }

    /**
     * return the match
     */
    public function getMatch()
    {
        return $this->_matchtype;
    }

    /**
     * Is the checked url green?
     * @return boolean
     */
    public function isGreen()
    {
        return $this->_green;
    }

    /**
     * Get the checked url
     * @return string
     */
    public function getCheckedUrl()
    {
        return $this->_checkedUrl;
    }

    /**
     * Get the checked url
     * @return string
     */
    public function getCheckedAt()
    {
        return $this->_checkedAt;
    }

    /**
     * Get the checked url
     * @return string
     */
    public function setCheckedAt($date)
    {
        $this->_checkedAt = $date;
    }

    /**
     * Set the green variable
     * @param boolean $green
     */
    public function setGreen($green)
    {
        $this->_green = $green;
    }

    /**
     * Return data variable
     */
    public function isData()
    {
        return $this->_data;
    }

    /**
     * Set the data variable
     * @param boolean $data
     */
    public function setData($data)
    {
        $this->_data = $data;
    }

    /**
     * Return cached variable
     */
    public function isCached()
    {
        return $this->_cached;
    }

    /**
     * Set the data variable
     * @param boolean $cached
     */
    public function setCached($cached)
    {
        $this->_cached = $cached;
    }

    /**
     * Set the hosting provider id
     * @param int $id
     */
    public function setHostingProviderId($id)
    {
        $this->_idHostingProvider = $id;
    }

    /**
     * Get the hosting provider id
     * @return int
     */
    public function getHostingProviderId()
    {
        return $this->_idHostingProvider;
    }

    /**
     * Is the checked url hosted by a hosting provider?
     * @return boolean
     */
    public function isHostingProvider()
    {
        if (!is_null($this->_idHostingProvider)) {
            return true;
        }
        return false;
    }

    /**
     * Set the hosting provider
     * @param Hostingprovider $hp
     */
    public function setHostingProvider($hp)
    {
        $this->_HostingProvider = $hp;
    }

    /**
     * Get the hosting provider
     * @return Hostingprovider
     */
    public function getHostingProvider()
    {
        return $this->_HostingProvider;
    }

    /**
     * Get the ip adress
     * @return string
     */
    public function getIpAddress($type = 'ipv4')
    {
        if (isset($this->_ip[$type])) {
            return $this->_ip[$type];
        }
        return $this->_ip['ipv4'];
    }
    
    /**
     * Is the company of the checked url powered by a green energy provider?
     * @return boolean
     */
    public function isPoweredBy()
    {
        return $this->_poweredby;
    }
    
    public function setPoweredBy($provider, $organisation)
    {
        $this->_poweredby       = true;
        $this->_EnergyProvider  = $provider;
        $this->_organisation    = $organisation;
    }
    
    public function getEnergyProviderId()
    {
        if ($this->isPoweredBy()) {
            return $this->_EnergyProvider->getId();
        }
        return false;
    }
    
    public function getEnergyProvider()
    {
        if ($this->isPoweredBy()) {
            return $this->_EnergyProvider;
        }
        return false;
    }
    
    public function getOrganisation()
    {
        if ($this->isPoweredBy()) {
            return $this->_organisation;
        }
        return false;
    }
    
    public function setCalledFrom($called)
    {
        $this->calledfrom = $called;
    }
    
    public function getCalledFrom($key)
    {
        if (isset($this->calledfrom[$key])) {
            return $this->calledfrom[$key];
        }
        return false;
    }
}
