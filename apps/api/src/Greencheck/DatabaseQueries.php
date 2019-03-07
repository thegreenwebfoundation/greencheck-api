<?php

namespace App\Greencheck;

use Doctrine\DBAL\Connection;
use Symfony\Bridge\Doctrine\RegistryInterface;

class DatabaseQueries
{
    /** @var Connection */
    private $connection;

    public function __construct(RegistryInterface $doctrine)
    {
        $this->connection = $doctrine->getConnection();
    }

    /**
     * Get one hostingproviders.
     *
     * @return string
     */
    public function getHostingProvider($id)
    {
        $query = 'SELECT hostingproviders.id,naam,website,countrydomain,model,hostingprovider_certificates.url as certurl, valid_from, valid_to, mainenergytype,energyprovider, partner 
                  FROM hostingproviders LEFT JOIN hostingproviders_stats ON hostingproviders.id = hostingproviders_stats.id_hp LEFT JOIN hostingprovider_certificates ON hostingproviders.id = hostingprovider_certificates.id_hp 
                  WHERE showonwebsite = 1 AND hostingproviders.id=?';

        return $this->connection->fetchAll($query, [$id]);
    }

    /**
     * Get all hostingproviders.
     *
     * @return string
     */
    public function getHostingProviders()
    {
        $query = 'SELECT hostingproviders.id,naam,website,countrydomain,model,green_checks,green_domains,hostingprovider_certificates.url as certurl, valid_from, valid_to, mainenergytype, energyprovider,partner 
                  FROM hostingproviders LEFT JOIN hostingproviders_stats ON hostingproviders.id = hostingproviders_stats.id_hp LEFT JOIN hostingprovider_certificates ON hostingproviders.id = hostingprovider_certificates.id_hp 
                  WHERE showonwebsite = 1 ORDER BY countrydomain,naam';

        return $this->connection->fetchAll($query);
    }

    /**
     * Get all datacenters that have hostingproviders associated.
     *
     * @return string
     */
    public function getDatacenter($id)
    {
        $query = "SELECT datacenters.id,datacenters_hostingproviders.hostingprovider_id, naam,website,countrydomain,model,pue,mja3,city,country,dcc.url as cert_url,dcc.valid_from as cert_valid_from,dcc.valid_to as cert_valid_to,dcc.mainenergytype as cert_main_energy,dcc.energyprovider as cert_energyprovider,dcclass.classification 
                  FROM datacenters LEFT JOIN datacenters_locations ON datacenters.id = datacenters_locations.id_dc LEFT JOIN datacenter_certificates dcc ON datacenters.id = dcc.id_dc LEFT JOIN datacenters_classifications dcclass ON datacenters.id = dcclass.id_dc,datacenters_hostingproviders 
                  WHERE showonwebsite = 1 and datacenters_hostingproviders.datacenter_id = datacenters.id and datacenters.id = $id";
        $result = $this->connection->fetchAll($query);
        $dcs = $data = [];
        foreach ($result as $row) {
            $id = $row['id'];
            if (!isset($dcs[$id])) {
                $dcs[$id] = $row;
            }
            $dcs[$id]['hostingproviders'][] = $row['hostingprovider_id'];
            unset($dcs[$id]['hostingprovider_id']);
        }
        foreach ($dcs as $row) {
            $data[] = $row;
        }

        return $data;
    }

    /**
     * Get all datacenters that have hostingproviders associated.
     *
     * @return string
     */
    public function getDatacenters()
    {
        $query = 'SELECT datacenters.id,datacenters_hostingproviders.hostingprovider_id, naam,website,countrydomain,model,pue,mja3,city,country,dcc.url as cert_url,dcc.valid_from as cert_valid_from,dcc.valid_to as cert_valid_to,dcc.mainenergytype as cert_main_energy,dcc.energyprovider as cert_energyprovider,dcclass.classification 
                  FROM datacenters LEFT JOIN datacenters_locations ON datacenters.id = datacenters_locations.id_dc LEFT JOIN datacenter_certificates dcc ON datacenters.id = dcc.id_dc LEFT JOIN datacenters_classifications dcclass ON datacenters.id = dcclass.id_dc,datacenters_hostingproviders 
                  WHERE showonwebsite = 1 and datacenters_hostingproviders.datacenter_id = datacenters.id';
        $result = $this->connection->fetchAll($query);
        $dcs = $data = [];
        foreach ($result as $row) {
            $id = $row['id'];
            if (!isset($dcs[$id])) {
                $dcs[$id] = $row;
            }
            $dcs[$id]['hostingproviders'][] = $row['hostingprovider_id'];
            unset($dcs[$id]['hostingprovider_id']);
        }
        foreach ($dcs as $row) {
            $data[] = $row;
        }

        return $data;
    }

    /**
     * Get all datacenters that have hostingproviders associated.
     *
     * @return string
     */
    public function getDatacentersForHostingProviders()
    {
        $query = 'SELECT datacenters.id,datacenters_hostingproviders.hostingprovider_id, naam,website,countrydomain,model,pue,mja3,city,country,dcc.url as cert_url,dcc.valid_from as cert_valid_from,dcc.valid_to as cert_valid_to,dcc.mainenergytype as cert_main_energy,dcc.energyprovider as cert_energyprovider,dcclass.classification 
                  FROM datacenters LEFT JOIN datacenters_locations ON datacenters.id = datacenters_locations.id_dc LEFT JOIN datacenter_certificates dcc ON datacenters.id = dcc.id_dc LEFT JOIN datacenters_classifications dcclass ON datacenters.id = dcclass.id_dc,datacenters_hostingproviders 
                  WHERE showonwebsite = 1 and datacenters_hostingproviders.datacenter_id = datacenters.id';
        $result = $this->connection->fetchAll($query);
        $dcs = [];
        foreach ($result as $row) {
            $hpid = $row['hostingprovider_id'];
            $dcid = $row['id'];
            unset($row['hostingprovider_id']);
            if (isset($row['cert_url'])) {
                if (isset($dcs[$hpid][$dcid])) {
                    $data = $dcs[$hpid][$dcid];
                } else {
                    $data = $row;
                }
                $data['certificates'][] = [
                    'cert_url' => $row['cert_url'],
                    'cert_valid_from' => $row['cert_valid_from'],
                    'cert_valid_to' => $row['cert_valid_to'],
                    'cert_main_energy' => $row['cert_main_energy'],
                    'cert_energyprovider' => $row['cert_energyprovider'],
                ];
                $row = $data;
            }
            unset($row['cert_url']);
            unset($row['cert_valid_from']);
            unset($row['cert_valid_to']);
            unset($row['cert_main_energy']);
            unset($row['cert_energyprovider']);

            if (isset($row['classification'])) {
                if (isset($dcs[$hpid][$dcid])) {
                    $data = $dcs[$hpid][$dcid];
                } else {
                    $data = $row;
                }
                $data['classifications'][$row['classification']] = $row['classification'];
                unset($data['classification']);
                $row = $data;
            }

            $dcs[$hpid][$dcid] = $row;
        }
        $data2 = [];
        foreach ($dcs as $hpid => $array) {
            foreach ($array as $dcid => $row) {
                if (!isset($row['certificates'])) {
                    $row['certificates'] = [];
                }
                if (!isset($row['classifications'])) {
                    $row['classifications'] = [];
                }
                $data2[$hpid][] = $row;
            }
        }

        return $data2;
    }

    /**
     * Get all hostingproviders.
     *
     * @return string
     */
    public function getHostingProvidersCountry($countrydomain)
    {
        $tldList = $this->getHostingProvidersTLD();
        $data = [];
        foreach ($tldList as $row) {
            $data[$row['tld']] = $row['countrydomain'];
        }
        if (!isset($data[$countrydomain])) {
            return [];
        }
        $countrydomain = $data[$countrydomain];

        $query = "SELECT id,naam,website,countrydomain,model 
                       FROM hostingproviders 
                       WHERE showonwebsite = 1 
                       AND countrydomain = '$countrydomain' 
                       ORDER BY naam";

        return $this->connection->fetchAll($query);
    }

    public function getHostingProvidersTLD()
    {
        $query = "SELECT countrydomain, CONCAT('.', LCASE(countrydomain) ) tld, countryname
                       FROM hostingproviders, country_iso
                       WHERE showonwebsite = 1 AND countrydomain = country_iso.iso GROUP BY countrydomain
                       ";

        return $this->connection->fetchAll($query);
    }

    public function getHostingProvidersTLDDirectory()
    {
        $query = "SELECT iso, CONCAT('.', LCASE(iso) ) tld, countryname
                       FROM country_iso
                       ORDER BY countryname
                       ";
        $rows = $this->connection->fetchAll($query);
        foreach ($rows as $row) {
            $tlds[$row['iso']] = $row;
        }

        $query = 'SELECT iso, hostingproviders.id, hostingproviders.naam, hostingproviders.website, hostingproviders.partner
                       FROM country_iso JOIN hostingproviders ON showonwebsite = 1 AND countrydomain = country_iso.iso
                       ORDER BY iso, hostingproviders.partner DESC, hostingproviders.naam
                       ';
        $rows = $this->connection->fetchAll($query);
        foreach ($rows as $row) {
            $tlds[$row['iso']]['providers'][] = $row;
        }

        return $tlds;
    }

    /**
     * Get latest hostingproviders.
     *
     * @return string
     */
    public function getLatestProviders()
    {
        $query = 'SELECT naam, website, countrydomain FROM hostingproviders WHERE showonwebsite = 1 ORDER BY id DESC LIMIT 50';

        return $this->connection->fetchAll($query);
    }

    public function getGreenList()
    {
        $query = 'SELECT * FROM greenlist WHERE last_checked >= DATE_SUB(NOW(),INTERVAL 1 HOUR)';

        return $this->connection->fetchAll($query);
    }

    /**
     * Get the unique users per checked_through type.
     *
     * @return array
     */
    public function getTotalStats()
    {
        $query = "SELECT 'total', MAX(count) as checks, MAX(ips) as users FROM `greencheck_stats_total`";
        $result = $this->connection->fetchAll($query);
        $data = [];
        foreach ($result as $row) {
            $data[] = $row;
        }

        return $data;
    }

    /**
     * Get the unique users per checked_through type.
     *
     * @return array
     */
    public function getUniqueUsers()
    {
        $query = 'SELECT checked_through, count as checks, ips as count FROM `greencheck_stats`';
        $result = $this->connection->fetchAll($query);
        $data = [];
        foreach ($result as $row) {
            $data[$row['checked_through']] = $row;
        }

        return $data;
    }

    public function getWeeklyStats()
    {
        $query = "SELECT STR_TO_DATE(concat(year,week,' Monday'), '%X%V %W') as datum, url_perc
                 FROM `greencheck_weekly`
                 WHERE year >= DATE_SUB(NOW(), INTERVAL 12 MONTH) order by datum desc limit 52";
        $result = $this->connection->fetchAll($query);
        foreach ($result as $row) {
            if (is_null($row['datum'])) {
                continue;
            }
            $data[] = [$row['datum'], (float) $row['url_perc']];
        }

        return array_reverse($data);
    }

    public function getDailyStats()
    {
        $query = "SELECT datum, checked_through, count
                 FROM `greencheck_daily`
                 WHERE (checked_through = 'api' OR checked_through='apisearch')
                 AND datum >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                 ORDER BY datum DESC
                 ";
        $result = $this->connection->fetchAll($query);
        $data = [];
        foreach ($result as $row) {
            $data[$row['datum']][$row['checked_through']] = $row;
        }

        return $data;
    }

    public function getTlds()
    {
        $query = 'SELECT COUNT(distinct(toplevel)) as count
                 FROM `greencheck_tld`';

        return $this->connection->fetchColumn($query);
    }

    public function getPercentage()
    {
        $query = 'SELECT url_perc
                 FROM `greencheck_weekly` ORDER BY year DESC, week DESC LIMIT 1';

        return $this->connection->fetchColumn($query);
    }

    /**
     * Get all domains we have data for.
     *
     * @todo This needs to be enhanced, hostingprovider domains say not everything about what they host
     *
     * @return string
     */
    public function getCountryDomains()
    {
        $query = 'SELECT DISTINCT (countrydomain) as domain FROM hostingproviders';
        $result = $this->connection->fetchAll($query);
        foreach ($result as $row) {
            if (is_object($row)) {
                $domain = strtolower($row->domain);
            } else {
                $domain = strtolower($row['domain']);
            }
            $domains[$domain] = $domain;
        }
        $domains['net'] = 'net';
        $domains['org'] = 'org';

        return $domains;
    }

    /**
     * Get all domains we have data for.
     *
     * @todo This needs to be enhanced, hostingprovider domains say not everything about what they host
     *
     * @return string
     */
    public function getHostingProvidersCount()
    {
        $query = 'SELECT COUNT(*) FROM hostingproviders';

        return $this->connection->fetchColumn($query);
    }

    /**
     * Get all domains we have data for.
     *
     * @todo This needs to be enhanced, hostingprovider domains say not everything about what they host
     *
     * @return string
     */
    public function getEnergyCompanies()
    {
        $query = 'SELECT COUNT(*) FROM greencheck_energy';

        return $this->connection->fetchColumn($query);
    }
}
