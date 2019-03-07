<?php

namespace TGWF\Greencheck\Sitecheck;

use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints\Ip;

use Zend\Validator\Hostname;

/**
 * Sitecheck validation class
 *
 * The sitecheck validation handles all url and ip validation actions
 *
 * @author Arend-Jan Tetteroo <aj@arendjantetteroo.nl>
 */
class Validator
{
    /**
     * Error messages from validation
     * @var array
     */
    protected $errorMessages = null;

    /**
     *
     * @var Zend\Validator\Hostname
     */
    protected $validator = null;

    /**
     * Ip's for checked url's to skip hostname checks on each call
     * @var array
     */
    protected $cleanurl = array();

    /**
     * Construct the sitecheck validator
     *
     */
    public function __construct()
    {
        $this->validator = new Hostname(Hostname::ALLOW_DNS | Hostname::ALLOW_IP);
    }

    /**
     * Check if the given url is a valid Hostname, and if so, check that it returns a valid ip adress
     *
     * @param string $url Url to validate
     * @param array  $ips Ip adresses belonging to this url
     *
     * @return boolean
     */
    public function validate($url, $ips)
    {
        if ($this->isUrlAValidPublicIpAddress($url)) {
            return true;
        }

        // If the given url has an ip adress, then it's a good url
        if ($ips['ipv4'] !== false || $ips['ipv6'] !== false) {
            return true;
        }

        $url = $this->getHostname($url);
        if ($this->validator->isValid($url)) {
            $this->errorMessages = array('invalidUrl' => 'No ip adress found for this url');
        } else {
            $this->errorMessages = $this->validator->getMessages();
        }
        return false;
    }

    /**
     * Return eventual validation errors
     *
     * @return array
     */
    public function getValidateErrors()
    {
        return $this->errorMessages;
    }

    /**
     * Check if the given url is a valid ip address
     *
     * @param string $ip The string to check for an ip adress
     *
     * @return boolean True if valid ip adress, false otherwise
     */
    public function isUrlAValidIpAddress($ip)
    {
        // The ip validator returns no errors for an empty string or null value, so return false for those
        if (is_null($ip) || $ip == "") {
            return false;
        }

        $validator = Validation::createValidator();
        if (method_exists($validator, 'validateValue')) {
            // sf 2.x validate
            $violations = $validator->validateValue($ip, new Ip(array('version' => 'all')));
        } else {
            $violations = $validator->validate($ip, new Ip(array('version' => 'all')));
        }

        if (count($violations) == 0) {
            //Public ip adress, return
            return true;
        }
        return false;
    }

    /**
     * Check if the given url is a valid public ip address
     *
     * @param string $ip The string to check for an ip adress
     *
     * @return boolean True if valid ip adress, false otherwise
     */
    public function isUrlAValidPublicIpAddress($ip)
    {
        // The ip validator returns no errors for an empty string or null value, so return false for those
        if (is_null($ip) || $ip == "") {
            return false;
        }

        $validator = Validation::createValidator();
        if (method_exists($validator, 'validateValue')) {
            // Sf 2.x
            $violations = $validator->validateValue($ip, new Ip(array('version' => 'all_public')));
        } else {
            // Sf 3
            $violations = $validator->validate($ip, new Ip(array('version' => 'all_public')));
        }

        if (count($violations) == 0) {
            //Public ip adress, return
            return true;
        }
        return false;
    }

    /**
     * Check if the given url is a valid ip address for ipv4 or ipv6
     *
     * @param string $ip   The string to check for an ip adress
     * @param string $type The type to check for, either '4' for ipv4 or '6' for ipv6
     *
     * @return boolean True if valid ip adress, false otherwise
     */
    public function isValidIpAddressForType($ip, $type = '4')
    {
        // The ip validator returns no errors for an empty string or null value, so return false for those
        if (is_null($ip) || $ip == "") {
            return false;
        }

        $validator = Validation::createValidator();
        if (method_exists($validator, 'validateValue')) {
            // Sf 2.x
            $violations = $validator->validateValue($ip, new Ip(array('version' => $type . '_public')));
        } else {
            // Sf 3.x
            $violations = $validator->validate($ip, new Ip(array('version' => $type . '_public')));
        }
        if (count($violations) == 0) {
            return true;
        }
        return false;
    }

    /**
     * Strip the url form it's protocol and uri and return the hostname
     *
     * @param string $url Url
     *
     * @return string
     */
    public function getHostname($url)
    {
        if (!isset($this->cleanurl[$url])) {
            if ($this->isUrlAValidPublicIpAddress($url)) {
                $this->cleanurl[$url] = $url;
                return $this->cleanurl[$url];
            }
            
            $parsed = parse_url($url);
            if (isset($parsed['path']) && !isset($parsed['host'])) {
                $this->cleanurl[$url] = $parsed['path'];
            } elseif (isset($parsed['host'])) {
                // Replace the [] from ipv6 literal adresses
                $this->cleanurl[$url] = str_replace('[', '', str_replace(']', '', $parsed['host']));
            } else {
                $this->cleanurl[$url] = false;
            }
        }
        return $this->cleanurl[$url];
    }
}
