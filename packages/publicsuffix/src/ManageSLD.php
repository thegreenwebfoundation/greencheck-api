<?php

namespace TGWF\PublicSuffix;

/**
 * @author Arend-Jan Tetteroo
 *
 * Fixed some issues, original by Alan Dix
 *
 * @author Alan Dix
 *
 * Version 0.9
 * Copyright (c) 2012
 *
 * see http://www.alandix.com/code/public-suffix/
 *
 * Licensed under the MIT license:
 * http://www.opensource.org/licenses/mit-license.php
 *
 */
 
define('PUBLIC_SUFFIX_LIST', 'http://mxr.mozilla.org/mozilla-central/source/netwerk/dns/effective_tld_names.dat?raw=1');

define('PUBLIC_SUFFIX_EXCEPTION_FLAG', 1);
define('PUBLIC_SUFFIX_CATCH_ALL_FLAG', 2);
define('PUBLIC_SUFFIX_LONG_RULE_FLAG', 4);
define('PUBLIC_SUFFIX_EXAMPLE_FLAG', 8);

class ManageSLD
{
    public $rulestore = false;
    public $parse_to_store = false;
    public $wild_card_rules = false;
    public $errors = array();
    
    public $line_ct;  // count of number of lines of input read and how many
    public $rule_ct;  // valid rules found
    
    public $read_time;
    public $parse_time;
    
    public function __construct($rulestore = false)
    {
        $this->setStore($rulestore);
    }
    
    public function setStore($rulestore = false)
    {
        if ($rulestore) {
            $this->rulestore = $rulestore;
        } else {
            $this->rulestore = new RuleStoreSLD();   // defaut memory-based store
        }
    }
    
    public function parseToStore()
    {
        $this->parse_to_store = true;
    }
    
    public function parseToRuleset()
    {
        $this->parse_to_store = false;
    }
    
    public function error($mess)
    {
        $this->errors[] = $mess;
    }
    
    public function parseFile($sourceUrl=false)
    {
        if (! $sourceUrl) {
            $sourceUrl = PUBLIC_SUFFIX_LIST;
        }
        $starttime = time();
        $source = file_get_contents($sourceUrl);  // assumes PHP is allowing file access to URLs, otherwise use cURL
        $endtime = time();
        $this->read_time = $endtime - $starttime;
        if (! $source) {
            return false;
        } else {
            return $this->parse($source, $starttime);
        }
    }
    
    // returns either an array of rules, or save rules to rulestore and returns a count
    //
    // each rule is of the form:
    //           array( 'tld'=> $tld, 'pattern'=>$pattern, 'exception'=>$is_exception );
    
    public function parse($source)
    {
        $starttime = time();
        $lines = preg_split('/(\\r\\n|\\n|\\r)/', $source);  // try to get line counts right in case of \r\n
        $rules = array();
        $this->line_ct = 0;
        $this->rule_ct = 0;
        foreach ($lines as $line) {
            $this->line_ct ++;  // N.B. lines start at line 1 not zero!
            $comment_start = strpos($line, '//');
            if ($comment_start !== false) {
                $pattern = substr($line, 0, $comment_start);
            } else {
                $pattern = $line;
            }
            $pattern = trim($pattern);
            if (! $pattern) {
                continue;
            }
            if ($pattern{0}=='!') {
                $is_exception = true;
                $pattern = substr($pattern, 1);
            } else {
                $is_exception = false;
            }
            $pattern = ManageSLD::normalise($pattern);
            $tld = ManageSLD::getTLD($pattern);
            $rule = array( 'tld'=> $tld, 'pattern'=>$pattern, 'exception'=>$is_exception );
            
            if ($this->parse_to_store) {
                $this->rulestore->addRule($rule);
            } else {
                $rules[] = $rule;
            }
            $this->rule_ct ++;
        }
        
        $endtime = time();
        $this->parse_time = $endtime - $starttime;

        if ($this->parse_to_store) {
            return $this->rule_ct;
        } else {
            return $rules;
        }
    }

    // lookup domain using rules in store
    //
    // returns array( public_suffix, primary_label, secondary_part, registerable_domain, pattern, flags ) - see applyRules below for details
    //
    public function lookup($domain)
    {
        $domain = ManageSLD::normalise($domain);
        $tld = ManageSLD::getTLD($domain);

        $rules = $this->rulestore->getRules($tld);
        
        if (! $rules) {
            $rules = array();
        }
        $rules_wildcard_top = $this->getWildCardRules();
        if ($rules_wildcard_top) {
            $rules = array_merge($rules, $rules_wildcard_top);
        }
        
        return ManageSLD::applyRules($domain, $rules);
    }
    
    // make sure we only get the wildcard rules once (if they exist!)
    // would be very, very weird to have any rules of the form abc.*, but strictly format allows it
    public function getWildCardRules()
    {
        if ($this->wild_card_rules === false) {
            $this->wild_card_rules = $this->rulestore->getRules('*');
            if (! $this->wild_card_rules) {
                $this->wild_card_rules = array();
            }  // make sure not false!
        }
        return $this->wild_card_rules;
    }
    
    // applyRules
    //
    //   takes rule set and applies the algorithm from http://publicsuffix.org/list/
    //
    //   Algorithm
    //     1. Match domain against all rules and take note of the matching ones.
    //     2. If no rules match, the prevailing rule is "*".
    //     3. If more than one rule matches, the prevailing rule is the one which is an exception rule.
    //     4. If there is no matching exception rule, the prevailing rule is the one with the most labels.
    //     5. If the prevailing rule is a exception rule, modify it by removing the leftmost label.
    //     6. The public suffix is the set of labels from the domain which directly match the labels of the prevailing rule (joined by dots).
    //     7. The registered or registrable domain is the public suffix plus one additional label.
    //
    //   returns array( public_suffix, primary_label, secondary_part, registerable_domain, pattern, flags )
    //
    //
    //   for example www.bham.ac.uk would return  ( 'ac.uk', 'bham' 'www', 'bham.ac.uk', 'ac.uk', 0 )
    //             www.developers.example.com would return ( 'com', 'example', 'www.developers', 'example.com', 0 )
    //
    //   flags as defined
    //
    //   note the registrable domain is $primary_label . '.' . public_suffix
    //
    public static function applyRules($domain, $rules)
    {
        $domain = ManageSLD::normalise($domain);
        $domain_parts = explode('.', $domain);
        $domain_len = count($domain_parts);
        $exception_sld = false;
        $exception_pattern = false;
        $exception_len = 0;
        $best_sld = false;
        $best_pattern = false;
        $best_len = 0;
        foreach ($rules as $rule) {
            $pattern_parts = explode('.', $rule['pattern']);
            $pattern_len = count($pattern_parts);
            $mismatch = false;
            for ($i=0; $i<$pattern_len; $i++) {
                $p = $pattern_parts[$pattern_len-$i-1];
                if ($p == '*') {
                    continue;
                } elseif ($i>$domain_len || $domain_parts[$domain_len-$i-1] != $p) {
                    $mismatch = true;
                    break;
                } else {
                    continue;
                }
            }
            if ($mismatch) {
                continue;
            }
            if ($rule['exception']) {
                if ($pattern_len > $exception_len) {
                    $sld_parts = array_slice($domain_parts, - ($pattern_len-1)); // may be greater than length of the domain
                    $exception_sld = implode('.', $sld_parts);
                    $exception_len = $pattern_len;
                    $exception_pattern = $rule['pattern'];
                }
            } else {
                if ($pattern_len > $best_len) {
                    $sld_parts = array_slice($domain_parts, - $pattern_len); // may be greater than length of the domain
                    $best_sld = implode('.', $sld_parts);
                    $best_len = $pattern_len;
                    $best_pattern = $rule['pattern'];
                }
            }
        }
        $flags = 0;
        if ($exception_sld) { // really feel this ought to be & ( $exception_len > $best_len ), but not what spec says
            $sld = $exception_sld;
            $len = $exception_len;
            $pattern = $exception_pattern;
            $flags |= PUBLIC_SUFFIX_EXCEPTION_FLAG;
        } elseif ($best_sld) { // really feel this ought to be & ( $exception_len > $best_len ), but not what spec says
            $sld = $best_sld;
            $len = $best_len;
            $pattern = $best_pattern;
        } else { // apply catch all '*' rule - see rule 2 at http://publicsuffix.org/list/
            $sld = ManageSLD::getTLD($domain);
            $len = 1;
            $pattern = '*';
            $flags |= PUBLIC_SUFFIX_CATCH_ALL_FLAG;
        }
        if ($len < $domain_len) {
            $flags |= PUBLIC_SUFFIX_LONG_RULE_FLAG;
        }
        $sld_len = strlen($sld);
        $dom_left = substr($domain, 0, -($sld_len+1)); // chop off tld from end
        list($secondary_part, $primary_label) = ManageSLD::splitLastDot($dom_left);
        
        if ($primary_label) {
            $registerable_domain = $primary_label . '.' . $sld;
        } else {
            $registerable_domain = '';
        }
        
        $tld = ManageSLD::getTLD($domain);
        if ($tld == 'example' || $sld == 'example.com') {
            $flags |= PUBLIC_SUFFIX_EXAMPLE_FLAG;
        }

        return array( $sld, $primary_label, $secondary_part, $registerable_domain, $pattern, $flags );
    }
    
    
    // ===================================================================
    // helper functions
    
    public static function normalise($domain)
    {
        $domain = trim($domain);
        $domain = trim($domain, '.');  // get rid of any leading (or trailing) dot
        $domain = strtolower($domain);
        return $domain;
    }
    
    public static function getTLD($domain)
    {
        list($rest, $tld) = ManageSLD::splitLastDot($domain);
        return $tld;
    }
    public static function splitLastDot($domain)
    {
        $dotpos = strrpos($domain, '.');
        if ($dotpos == false) {
            $last = $domain;
            $rest = false;
        } else {
            $last = substr($domain, $dotpos+1);
            $rest = substr($domain, 0, $dotpos);
        }
        return array( $rest, $last );
    }
    public static function splitFirstDot($domain)
    {
        $dotpos = strpos($domain, '.');
        if ($dotpos == false) {
            $first = $domain;
            $rest = false;
        } else {
            $first = substr($domain, 0, $dotpos);
            $rest = substr($domain, $dotpos+1);
        }
        return array( $first, $rest );
    }
}
