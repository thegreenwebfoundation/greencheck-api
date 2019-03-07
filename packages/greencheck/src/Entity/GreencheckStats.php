<?php

namespace TGWF\Greencheck\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TGWF\Greencheck\Entity\GreencheckStats
 */
class GreencheckStats
{
    /**
     * @var integer $id
     */
    private $id;

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
     * Set checkedThrough
     *
     * @param string $checkedThrough
     * @return GreencheckStats
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
     * @return GreencheckStats
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
     * @return GreencheckStats
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
