<?php

namespace TGWF\PublicSuffix;

/**
 * @author Alan Dix
 *
 * Version 0.9
 * Copyright (c) 2012
 *
 * see http://www.alandix.com/code/public-suffix/
 *
 * Licensed under the MIT license:
 * http://www.opensource.org/licenses/mit-license.php
 */

// memory based store as example.
// suitable for batch processing of many domains, or for persistent servers
// for more dynamic use should use an SQL backend, memcache, or something like that

class RuleStoreSLD
{
    public $tldIndex = [];

    //
    // note tld may be '*'
    //
    public function addRule($rule)
    {
        $this->tldIndex[$rule['tld']][] = $rule;
    }
    
    public function getRules($tld)
    {
        if (isset($this->tldIndex[$tld])) {
            return $this->tldIndex[$tld];
        }
        return array();
    }
}
