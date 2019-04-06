<?php

namespace TGWF\Greencheck\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use TGWF\Greencheck\Validator\Constraints as TGWFAssert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * TGWF\Greencheck\Entity\GreencheckIp.
 *
 * @Gedmo\Loggable
 * @TGWFAssert\IpRange
 * @ORM\Table(name="greencheck_ip")
 * @ORM\Entity(repositoryClass="TGWF\Greencheck\Repository\GreencheckIpRepository")
 */
class GreencheckIp
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
     * @Gedmo\Versioned
     * @ORM\Column(name="ip_start", type="decimal", precision=39, nullable=false)
     */
    protected $ipStartLong;

    /**
     * @var string
     *
     * @Assert\Ip(version="all_public")
     */
    protected $ipStart;

    /**
     * @var int
     *
     * @Gedmo\Versioned
     * @ORM\Column(name="ip_eind", type="decimal", precision=39, nullable=false)
     */
    protected $ipEindLong;

    /**
     * @var string
     *
     * @Assert\Ip(version="all_public")
     */
    protected $ipEind;

    /**
     * @var int
     *
     * @Gedmo\Versioned
     * @ORM\Column(name="active", type="boolean", nullable=false)
     */
    private $active;

    /**
     * @Gedmo\Versioned
     * @ORM\ManyToOne(targetEntity="Hostingprovider", inversedBy="iprecords")
     * @ORM\JoinColumn(name="id_hp", referencedColumnName="id")
     */
    protected $hostingprovider;

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
     * Set ipStart.
     *
     * @param string $ipStart
     */
    public function setIpStart($ipStart)
    {
        $this->ipStart = $ipStart;
        $this->ipStartLong = $this->inet_ptod($ipStart);

        return $this;
    }

    /**
     * Get ipStart.
     *
     * @return string
     */
    public function getIpStart()
    {
        if (is_null($this->ipStart)) {
            $this->ipStart = $this->inet_dtop($this->ipStartLong);
        }

        return $this->ipStart;
    }

    /**
     * Set ipEind.
     *
     * @param string $ipEind
     */
    public function setIpEind($ipEind)
    {
        $this->ipEind = $ipEind;
        $this->ipEindLong = $this->inet_ptod($ipEind);

        return $this;
    }

    /**
     * Get ipEind.
     *
     * @return string
     */
    public function getIpEind()
    {
        if (is_null($this->ipEind)) {
            $this->ipEind = $this->inet_dtop($this->ipEindLong);
        }

        return $this->ipEind;
    }

    /**
     * Set ipStartLong.
     *
     * @param int $ipStartLong
     *
     * @return GreencheckIp
     */
    public function setIpStartLong($ipStartLong)
    {
        $this->ipStartLong = $ipStartLong;

        return $this;
    }

    /**
     * Get ipStartLong.
     *
     * @return int
     */
    public function getIpStartLong()
    {
        return $this->ipStartLong;
    }

    /**
     * Set ipEindLong.
     *
     * @param int $ipEindLong
     *
     * @return GreencheckIp
     */
    public function setIpEindLong($ipEindLong)
    {
        $this->ipEindLong = $ipEindLong;

        return $this;
    }

    /**
     * Get ipEindLong.
     *
     * @return int
     */
    public function getIpEindLong()
    {
        return $this->ipEindLong;
    }

    /**
     * Set active.
     *
     * @param bool $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * Get active.
     *
     * @return bool
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Is active.
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * Set hostingprovider.
     *
     * @param TGWF\Greencheck\Entity\Hostingprovider $hostingprovider
     */
    public function setHostingprovider(Hostingprovider $hostingprovider)
    {
        $this->hostingprovider = $hostingprovider;
    }

    /**
     * Get hostingprovider.
     *
     * @return TGWF\Greencheck\Entity\Hostingprovider
     */
    public function getHostingprovider()
    {
        return $this->hostingprovider;
    }

    /**
     * Check that the end ip is greater than or equals the start ip.
     *
     * @return bool
     */
    public function isValidIpRange()
    {
        if ($this->ipStartLong > $this->ipEindLong) {
            return false;
        }

        return true;
    }

    /**
     * Convert an IP address from presentation to decimal(39,0) format suitable for storage in MySQL.
     *
     * @param string $ip_address An IP address in IPv4, IPv6 or decimal notation
     *
     * @return string The IP address in decimal notation
     */
    public function inet_ptod($ip_address)
    {
        // IPv4 address
        if (false === strpos($ip_address, ':') && false !== strpos($ip_address, '.')) {
            $ip_address = '::'.$ip_address;
        }

        // IPv6 address
        if (false !== strpos($ip_address, ':')) {
            $network = inet_pton($ip_address);
            $parts = unpack('N*', $network);

            foreach ($parts as &$part) {
                if ($part < 0) {
                    $part = bcadd((string) $part, '4294967296');
                }

                if (!is_string($part)) {
                    $part = (string) $part;
                }
            }

            $decimal = $parts[4];
            $decimal = bcadd($decimal, bcmul($parts[3], '4294967296'));
            $decimal = bcadd($decimal, bcmul($parts[2], '18446744073709551616'));
            $decimal = bcadd($decimal, bcmul($parts[1], '79228162514264337593543950336'));

            return $decimal;
        }

        // Decimal address
        return $ip_address;
    }

    /**
     * Convert an IP address from decimal format to presentation format.
     *
     * @param string $decimal An IP address in IPv4, IPv6 or decimal notation
     *
     * @return string The IP address in presentation format
     */
    public function inet_dtop($decimal)
    {
        // IPv4 or IPv6 format
        if (false !== strpos($decimal, ':') || false !== strpos($decimal, '.')) {
            return $decimal;
        }

        // Decimal format
        $parts = [];
        $parts[1] = bcdiv($decimal, '79228162514264337593543950336', 0);
        $decimal = bcsub($decimal, bcmul($parts[1], '79228162514264337593543950336'));
        $parts[2] = bcdiv($decimal, '18446744073709551616', 0);
        $decimal = bcsub($decimal, bcmul($parts[2], '18446744073709551616'));
        $parts[3] = bcdiv($decimal, '4294967296', 0);
        $decimal = bcsub($decimal, bcmul($parts[3], '4294967296'));
        $parts[4] = $decimal;

        foreach ($parts as &$part) {
            if (1 == bccomp($part, '2147483647')) {
                $part = bcsub($part, '4294967296');
            }

            $part = (int) $part;
        }

        $network = pack('N4', $parts[1], $parts[2], $parts[3], $parts[4]);
        $ip_address = inet_ntop($network);

        // Turn IPv6 to IPv4 if it's IPv4
        if (preg_match('/^::\d+.\d+.\d+.\d+$/', $ip_address)) {
            return substr($ip_address, 2);
        }

        return $ip_address;
    }
}
