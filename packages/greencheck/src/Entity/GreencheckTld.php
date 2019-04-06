<?php

namespace TGWF\Greencheck\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TGWF\Greencheck\Entity\GreencheckTld.
 *
 * @ORM\Table(name="greencheck_tld")
 * @ORM\Entity(repositoryClass="TGWF\Greencheck\Repository\GreencheckTldRepository")
 */
class GreencheckTld
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="tld", type="string", length=5, nullable=false)
     */
    private $tld;

    /**
     * @var string
     *
     * @ORM\Column(name="toplevel", type="string", length=64, nullable=false)
     */
    private $toplevel;

    /**
     * @var int
     *
     * @ORM\Column(name="checked_domains", type="integer", nullable=false)
     */
    private $checkedDomains;

    /**
     * @var int
     *
     * @ORM\Column(name="green_domains", type="integer", nullable=false)
     */
    private $greenDomains;

    /**
     * @var int
     *
     * @ORM\Column(name="hps", type="integer", nullable=false)
     */
    private $hps;

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
     * Set tld.
     *
     * @param string $tld
     */
    public function setTld($tld)
    {
        $this->tld = $tld;
    }

    /**
     * Get tld.
     *
     * @return string
     */
    public function getTld()
    {
        return $this->tld;
    }

    /**
     * Set toplevel.
     *
     * @param string $toplevel
     */
    public function setToplevel($toplevel)
    {
        $this->toplevel = $toplevel;
    }

    /**
     * Get tld.
     *
     * @return string
     */
    public function getToplevel()
    {
        return $this->toplevel;
    }

    /**
     * Set checkedDomains.
     *
     * @param int $checkedDomains
     */
    public function setCheckedDomains($checkedDomains)
    {
        $this->checkedDomains = $checkedDomains;
    }

    /**
     * Get checkedDomains.
     *
     * @return int
     */
    public function getCheckedDomains()
    {
        return $this->checkedDomains;
    }

    /**
     * Set greenDomains.
     *
     * @param int $greenDomains
     */
    public function setGreenDomains($greenDomains)
    {
        $this->greenDomains = $greenDomains;
    }

    /**
     * Get greenDomains.
     *
     * @return int
     */
    public function getGreenDomains()
    {
        return $this->greenDomains;
    }

    /**
     * Set hps.
     *
     * @param int $hps
     */
    public function setHps($hps)
    {
        $this->hps = $hps;
    }

    /**
     * Get hps.
     *
     * @return int
     */
    public function getHps()
    {
        return $this->hps;
    }
}
