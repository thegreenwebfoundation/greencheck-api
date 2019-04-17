<?php

namespace TGWF\Greencheck\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TGWF\GreencheckAdminBundle\Entity\Greencheck.
 *
 * @ORM\Table(name="greencheck",indexes={
 *  @ORM\Index(name="green", columns={"green"}),
 *  @ORM\Index(name="url", columns={"url"}),
 *  @ORM\Index(name="datum", columns={"datum"}),
 *  }
 * )
 * @ORM\Entity
 */
class Greencheck
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
     * @var int
     *
     * @ORM\Column(name="id_greencheck", type="integer", nullable=false)
     */
    private $idGreencheck;

    /**
     * @var int
     *
     * @ORM\Column(name="id_hp", type="integer", nullable=true)
     */
    private $idHp;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", nullable=false)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=255, nullable=false)
     */
    private $url;

    /**
     * @var int
     *
     * @ORM\Column(name="ip", type="integer", nullable=false)
     */
    private $ip;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="datum", type="datetime", nullable=false)
     */
    private $datum;

    /**
     * @var string
     *
     * @ORM\Column(name="green", type="string", nullable=false)
     */
    private $green;

    /**
     * @var string
     *
     * @ORM\Column(name="tld", type="string", length=64, nullable=true)
     */
    private $tld;

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
     * Set idHp.
     *
     * @param int $idHp
     */
    public function setIdHp($idHp)
    {
        $this->idHp = $idHp;
    }

    /**
     * Get idHp.
     *
     * @return int
     */
    public function getIdHp()
    {
        return $this->idHp;
    }

    /**
     * Set idGreencheck.
     *
     * @param int $idGreencheck
     */
    public function setIdGreencheck($idGreencheck)
    {
        $this->idGreencheck = $idGreencheck;
    }

    /**
     * Get idGreencheck.
     *
     * @return int
     */
    public function getIdGreencheck()
    {
        return $this->idGreencheck;
    }

    /**
     * Set type.
     *
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Get type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set url.
     *
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * Get url.
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set ip.
     *
     * @param int $ip
     */
    public function setIp($ip)
    {
        $this->ip = $ip;
    }

    /**
     * Get ip.
     *
     * @return int
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * Set datum.
     *
     * @param \DateTime $datum
     */
    public function setDatum($datum)
    {
        $this->datum = $datum;
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
     * Set green.
     *
     * @param string $green
     */
    public function setGreen($green)
    {
        $this->green = $green;
    }

    /**
     * Get green.
     *
     * @return string
     */
    public function getGreen()
    {
        return $this->green;
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
}
