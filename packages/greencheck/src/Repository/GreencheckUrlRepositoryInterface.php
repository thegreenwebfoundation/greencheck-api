<?php

namespace TGWF\Greencheck\Repository;

use TGWF\Greencheck\Entity\GreencheckUrl;

/**
 * GreencheckUrlRepository.
 */
interface GreencheckUrlRepositoryInterface
{
    /**
     * Check if the given url is compensated.
     */
    public function checkUrl(string $url): ?GreencheckUrl;
}