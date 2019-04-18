<?php


namespace TGWF\Greencheck\Sitecheck;


class DnsFetcher
{

    public function getIpAddressesForUrl($url)
    {
        $result = [];

        try {
            $dns4 = dns_get_record($url, DNS_A);
            if (is_countable($dns4) && count($dns4) > 0 && false !== $dns4) {
                $result['ip'] = $dns4[0]['ip'];
                $result['ipv6'] = false;
            } else {
                $result['ip'] = false;
                // Ignore dns warnings
                $dns6 = @dns_get_record($url, DNS_AAAA);
                if (is_countable($dns6) && count($dns6) > 0 && false !== $dns6) {
                    $result['ipv6'] = $dns6[0]['ipv6'];
                } else {
                    $result['ipv6'] = false;
                }
            }
        } catch(\Exception $e) {
            // some dns error occurred
            $result['ip'] = false;
            $result['ipv6'] = false;
        }

        return $result;
    }

}