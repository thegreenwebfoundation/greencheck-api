<?php

namespace TGWF\Greencheck\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TGWF\GreencheckAdminBundle\Entity\Greencheck.
 *
 * @ORM\Table(name="greencheck_by",indexes={
 *  @ORM\Index(name="greencheck_by_datum", columns={"datum"}),
 *  @ORM\Index(name="checked_through", columns={"checked_through"}),
 *  @ORM\Index(name="checked_by", columns={"checked_by"})
 *  }
 * )
 * @ORM\Entity
 */
class GreencheckBy
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
     * @var \DateTime
     *
     * @ORM\Column(name="datum", type="datetime", nullable=false)
     */
    private $datum;

    /**
     * @var int
     *
     * @ORM\Column(name="checked_by", type="string",length=40, nullable=false)
     */
    private $checkedBy;

    /**
     * @var string
     *
     * @ORM\Column(name="checked_through", type="string", nullable=false)
     */
    private $checkedThrough;

    /**
     * @var string
     *
     * @ORM\Column(name="checked_browser", type="string", length=255, nullable=false)
     */
    private $checkedBrowser;

    public function __construct()
    {
        $date = (new \DateTime('now'));
        $date->setTime($date->format('H'), $date->format('i'), '0');
        $this->setDatum($date);
    }

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
     * Set checkedBy.
     *
     * @param int $checkedBy
     */
    public function setCheckedBy($checkedBy)
    {
        $this->checkedBy = sha1($checkedBy);
    }

    /**
     * Get checkedBy.
     *
     * @return int
     */
    public function getCheckedBy()
    {
        return $this->checkedBy;
    }

    /**
     * Set checkedThrough.
     *
     * @param string $checkedThrough
     */
    public function setCheckedThrough($checkedThrough)
    {
        $this->checkedThrough = $checkedThrough;
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
     * Set checkedBrowser.
     *
     * @param string $checkedBrowser
     */
    public function setCheckedBrowser($checkedBrowser)
    {
        $this->checkedBrowser = $checkedBrowser;
    }

    /**
     * Get checkedBrowser.
     *
     * @return string
     */
    public function getCheckedBrowser()
    {
        return $this->checkedBrowser;
    }
}
