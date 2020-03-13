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
     * @var string (decimal format)
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
     * @var string (decimal format)
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
     * @var bool
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
     * @return GreencheckIp
     */
    public function setIpStart($ipStart)
    {
        $this->ipStart = $ipStart;
        $this->ipStartLong = GreencheckIp::convertIpPresentationToDecimal($ipStart);

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
            $this->ipStart = GreencheckIp::convertIpDecimalToPresentation($this->ipStartLong);
        }

        return $this->ipStart;
    }

    /**
     * Set ipEind.
     *
     * @param string $ipEind
     * @return GreencheckIp
     */
    public function setIpEind($ipEind)
    {
        $this->ipEind = $ipEind;
        $this->ipEindLong = self::convertIpPresentationToDecimal($ipEind);

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
            $this->ipEind = self::convertIpDecimalToPresentation($this->ipEindLong);
        }

        return $this->ipEind;
    }

    /**
     * Set ipStartLong.
     *
     * @param string $ipStartLong
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
     * @return string
     */
    public function getIpStartLong()
    {
        return $this->ipStartLong;
    }

    /**
     * Set ipEindLong.
     *
     * @param string $ipEindLong
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
     * @return string
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
     * @param Hostingprovider $hostingprovider
     */
    public function setHostingprovider(Hostingprovider $hostingprovider)
    {
        $this->hostingprovider = $hostingprovider;
    }

    /**
     * Get hostingprovider.
     *
     * @return Hostingprovider
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
     * @param string $ipPresentation An IP address in IPv4, IPv6 or decimal notation
     *
     * @return string The IP address in decimal notation
     */
    public static function convertIpPresentationToDecimal($ipPresentation)
    {
        // IPv4 address
        if (false === strpos($ipPresentation, ':') && false !== strpos($ipPresentation, '.')) {
            $ipPresentation = '::'.$ipPresentation;
        }

        // IPv6 address
        if (false !== strpos($ipPresentation, ':')) {
            $network = inet_pton($ipPresentation);
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
        return $ipPresentation;
    }

    /**
     * Convert an IP address from decimal format to presentation format.
     *
     * @param string $ipDecimal An IP address in IPv4, IPv6 or decimal notation
     *
     * @return string The IP address in presentation format
     */
    public static function convertIpDecimalToPresentation($ipDecimal)
    {
        // IPv4 or IPv6 format
        if (false !== strpos($ipDecimal, ':') || false !== strpos($ipDecimal, '.')) {
            return $ipDecimal;
        }

        // Decimal format
        $parts = [];
        $parts[1] = bcdiv($ipDecimal, '79228162514264337593543950336', 0);
        $ipDecimal = bcsub($ipDecimal, bcmul($parts[1], '79228162514264337593543950336'));
        $parts[2] = bcdiv($ipDecimal, '18446744073709551616', 0);
        $ipDecimal = bcsub($ipDecimal, bcmul($parts[2], '18446744073709551616'));
        $parts[3] = bcdiv($ipDecimal, '4294967296', 0);
        $ipDecimal = bcsub($ipDecimal, bcmul($parts[3], '4294967296'));
        $parts[4] = $ipDecimal;

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
