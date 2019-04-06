<?php

namespace TGWF\Greencheck\Entity;

/**
 * TGWF\Greencheck\Entity\GreencheckStats.
 */
class GreencheckStats
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $checkedThrough;

    /**
     * @var int
     */
    private $count;

    /**
     * @var int
     */
    private $ips;

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
     * Set checkedThrough.
     *
     * @param string $checkedThrough
     *
     * @return GreencheckStats
     */
    public function setCheckedThrough($checkedThrough)
    {
        $this->checkedThrough = $checkedThrough;

        return $this;
    }

    /**
     * Get checkedThrough.
     *
     * @return string
     */
    public function getCheckedThrough()
    {
        return $this->checkedThrough;
    }

    /**
     * Set count.
     *
     * @param int $count
     *
     * @return GreencheckStats
     */
    public function setCount($count)
    {
        $this->count = $count;

        return $this;
    }

    /**
     * Get count.
     *
     * @return int
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * Set ips.
     *
     * @param int $ips
     *
     * @return GreencheckStats
     */
    public function setIps($ips)
    {
        $this->ips = $ips;

        return $this;
    }

    /**
     * Get ips.
     *
     * @return int
     */
    public function getIps()
    {
        return $this->ips;
    }
}
