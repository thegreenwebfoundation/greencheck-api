<?php

namespace TGWF\Greencheck\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * TGWF\GreencheckAdminBundle\Entity\GreencheckAs
 *
 * @Gedmo\Loggable
 * @ORM\Table(name="greencheck_as",indexes={@ORM\Index(name="asn", columns={"asn"})})
 * @ORM\Entity(repositoryClass="TGWF\Greencheck\Repository\GreencheckAsRepository")
 */
class GreencheckAs
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
     * @var integer $asn
     *
     * @ORM\Column(name="asn", type="integer", nullable=false)
     * @Assert\Range(
     *      min = "1",
     *      max = "65536",
     *      minMessage = "An Autonomous System number is at least 1",
     *      maxMessage = "An Autonomous System number is a maximum of 65535"
     * )
     * @Gedmo\Versioned
     */
    private $asn;

    /**
     * @var integer $active
     *
     * @ORM\Column(name="active", type="boolean", nullable=false)
     * @Gedmo\Versioned
     */
    private $active;

    /**
     * @Gedmo\Versioned
     * @ORM\ManyToOne(targetEntity="Hostingprovider", inversedBy="asnumbers")
     * @ORM\JoinColumn(name="id_hp", referencedColumnName="id")
     */
    protected $hostingprovider;

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
     * Set asn
     *
     * @param integer $asn
     */
    public function setAsn($asn)
    {
        $this->asn = $asn;
    }

    /**
     * Get asn
     *
     * @return integer
     */
    public function getAsn()
    {
        return $this->asn;
    }

    /**
     * Set active
     *
     * @param boolean $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * Get active
     *
     * @return boolean
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set hostingprovider
     *
     * @param Hostingprovider $hostingprovider
     */
    public function setHostingprovider(Hostingprovider $hostingprovider)
    {
        $this->hostingprovider = $hostingprovider;
    }

    /**
     * Get hostingprovider
     *
     * @return Hostingprovider
     */
    public function getHostingprovider()
    {
        return $this->hostingprovider;
    }
}
