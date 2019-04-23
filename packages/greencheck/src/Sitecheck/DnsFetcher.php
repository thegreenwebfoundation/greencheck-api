<?php

namespace TGWF\Greencheck\Sitecheck;

class DnsFetcher
{

    /**
     * @param $url
     * @return array
     */
    public function getIpAddressesForUrl($url)
    {
        // Initialize as false
        $result = [];
        $result['ip'] = false;
        $result['ipv6'] = false;

        try {
            $dns4 = dns_get_record($url, DNS_A);
            if (false !== $dns4 && is_countable($dns4) && count($dns4) > 0) {
                $result['ip'] = $dns4[0]['ip'];
                $result['ipv6'] = false;
            } else {
                $result['ip'] = false;
                // Ignore dns warnings
                $dns6 = @dns_get_record($url, DNS_AAAA);
                if (false !== $dns6 && is_countable($dns6) && count($dns6) > 0) {
                    $result['ipv6'] = $dns6[0]['ipv6'];
                } else {
                    $result['ipv6'] = false;
                }
            }
        } catch(\Exception $e) {
            // some dns error occurred, ignore it
        }

        return $result;
    }

}