<?php

namespace TGWF\Greencheck\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TGWF\Greencheck\Entity\GreencheckTld
 *
 * @ORM\Table(name="greencheck_tld")
 * @ORM\Entity(repositoryClass="TGWF\Greencheck\Repository\GreencheckTldRepository")
 */
class GreencheckTld
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string $tld
     *
     * @ORM\Column(name="tld", type="string", length=5, nullable=false)
     */
    private $tld;

    /**
     * @var string $tld
     *
     * @ORM\Column(name="toplevel", type="string", length=64, nullable=false)
     */
    private $toplevel;

    /**
     * @var integer $checkedDomains
     *
     * @ORM\Column(name="checked_domains", type="integer", nullable=false)
     */
    private $checkedDomains;

    /**
     * @var integer $greenDomains
     *
     * @ORM\Column(name="green_domains", type="integer", nullable=false)
     */
    private $greenDomains;

    /**
     * @var integer $hps
     *
     * @ORM\Column(name="hps", type="integer", nullable=false)
     */
    private $hps;



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
     * Set tld
     *
     * @param string $tld
     */
    public function setTld($tld)
    {
        $this->tld = $tld;
    }

    /**
     * Get tld
     *
     * @return string
     */
    public function getTld()
    {
        return $this->tld;
    }

    /**
     * Set toplevel
     *
     * @param string $toplevel
     */
    public function setToplevel($toplevel)
    {
        $this->toplevel = $toplevel;
    }

    /**
     * Get tld
     *
     * @return string
     */
    public function getToplevel()
    {
        return $this->toplevel;
    }

    /**
     * Set checkedDomains
     *
     * @param integer $checkedDomains
     */
    public function setCheckedDomains($checkedDomains)
    {
        $this->checkedDomains = $checkedDomains;
    }

    /**
     * Get checkedDomains
     *
     * @return integer
     */
    public function getCheckedDomains()
    {
        return $this->checkedDomains;
    }

    /**
     * Set greenDomains
     *
     * @param integer $greenDomains
     */
    public function setGreenDomains($greenDomains)
    {
        $this->greenDomains = $greenDomains;
    }

    /**
     * Get greenDomains
     *
     * @return integer
     */
    public function getGreenDomains()
    {
        return $this->greenDomains;
    }

    /**
     * Set hps
     *
     * @param integer $hps
     */
    public function setHps($hps)
    {
        $this->hps = $hps;
    }

    /**
     * Get hps
     *
     * @return integer
     */
    public function getHps()
    {
        return $this->hps;
    }
}
