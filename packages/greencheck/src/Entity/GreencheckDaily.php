<?php

namespace TGWF\Greencheck\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TGWF\Greencheck\Entity\GreencheckDaily
 */
class GreencheckDaily
{
    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var \DateTime $datum
     */
    private $datum;

    /**
     * @var string $checkedThrough
     */
    private $checkedThrough;

    /**
     * @var integer $count
     */
    private $count;

    /**
     * @var integer $ips
     */
    private $ips;


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
     * Set datum
     *
     * @param \DateTime $datum
     * @return GreencheckDaily
     */
    public function setDatum($datum)
    {
        $this->datum = $datum;
    
        return $this;
    }

    /**
     * Get datum
     *
     * @return \DateTime
     */
    public function getDatum()
    {
        return $this->datum;
    }

    /**
     * Set checkedThrough
     *
     * @param string $checkedThrough
     * @return GreencheckDaily
     */
    public function setCheckedThrough($checkedThrough)
    {
        $this->checkedThrough = $checkedThrough;
    
        return $this;
    }

    /**
     * Get checkedThrough
     *
     * @return string
     */
    public function getCheckedThrough()
    {
        return $this->checkedThrough;
    }

    /**
     * Set count
     *
     * @param integer $count
     * @return GreencheckDaily
     */
    public function setCount($count)
    {
        $this->count = $count;
    
        return $this;
    }

    /**
     * Get count
     *
     * @return integer
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * Set ips
     *
     * @param integer $ips
     * @return GreencheckDaily
     */
    public function setIps($ips)
    {
        $this->ips = $ips;
    
        return $this;
    }

    /**
     * Get ips
     *
     * @return integer
     */
    public function getIps()
    {
        return $this->ips;
    }
}
