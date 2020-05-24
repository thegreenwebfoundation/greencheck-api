<?php

namespace App\Greencheck;

use TGWF\Greencheck\SitecheckResult;

/**
 * Class LatestResult.
 */
class LatestResult
{
    /**
     * @var \DateTime
     */
    public $date;

    /**
     * @var
     */
    public $url;

    /**
     * @var
     */
    public $hostingProviderId;

    /**
     * @var
     */
    public $hostingProviderUrl;

    /**
     * @var
     */
    public $hostingProviderName;

    /**
     * @var
     */
    public $green;

    /**
     * @param SitecheckResult $result
     */
    public function setResult(SitecheckResult $result)
    {
        if ($result->isHostingProvider()) {
            $this->hostingProviderId = $result->getHostingProviderId();
            $this->hostingProviderUrl = $result->getHostingProvider()->getWebsite();
            $this->hostingProviderName = $result->getHostingProvider()->getNaam();
        } else {
            $this->hostingProviderId = false;
            $this->hostingProviderUrl = false;
            $this->hostingProviderName = false;
        }

        if ($result->getCheckedAt()) {
            $this->date = $result->getCheckedAt()->format('Y-m-d\TH:i:sP');
        } else {
            $this->date = (new \DateTime())->format('Y-m-d\TH:i:sP');
        }

        $this->url = $result->getCheckedUrl();
        $this->green = $result->isGreen();
    }
}
