<?php

namespace TGWF\Greencheck\Entity;

/**
 * TGWF\Greencheck\Entity\GreencheckDaily.
 */
class GreencheckDaily
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $datum;

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
     * Set datum.
     *
     * @param \DateTime $datum
     *
     * @return GreencheckDaily
     */
    public function setDatum($datum)
    {
        $this->datum = $datum;

        return $this;
    }

    /**
     * Get datum.
     *
     * @return \DateTime
     */
    public function getDatum()
    {
        return $this->datum;
    }

    /**
     * Set checkedThrough.
     *
     * @param string $checkedThrough
     *
     * @return GreencheckDaily
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
     * @return GreencheckDaily
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
     * @return GreencheckDaily
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
