<?php

namespace TGWF\PublicSuffix;

use Predis\Client;

/**
 * @author Arend-Jan Tetteroo
 */
class RedisRuleStoreSLD extends RuleStoreSLD
{
    protected $redis;
    
    /**
     * Create the rulestore
     *
     * @param Client $redis Redis client
     */
    public function __construct(Client $redis)
    {
        $this->redis = $redis;
    }

    /**
     * @param $manageSLD
     */
    public function save($manageSLD)
    {
        $timestamp = time();
        foreach ($this->tldIndex as $tld => $rules) {
            $this->redis->set('public_suffix_'.$tld, serialize(array('rules' => $rules)));
        }
        $meta = array('timestamp' =>$timestamp);
        $meta['line_ct'] = $manageSLD->line_ct;
        $meta['rule_ct'] = $manageSLD->rule_ct;
        $meta['read_time'] = $manageSLD->read_time;
        $meta['parse_time'] = $manageSLD->parse_time;
        $this->redis->set('public_suffix_meta', json_encode($meta));
    }
    
    /**
     * Get meta information
     *
     * @return array
     */
    public function getMeta()
    {
        return json_decode($this->redis->get('public_suffix_meta'));
    }
    
    /**
     * Get the rules for this tld
     *
     * @param  [type] $tld [description]
     *
     * @return array
     */
    public function getRules($tld)
    {
        if (!array_key_exists($tld, $this->tldIndex)) {
            $tld_info = unserialize($this->redis->get('public_suffix_'.$tld));
            if (isset($tld_info) && isset($tld_info->rules)) {
                $this->tldIndex[$tld] = $tld_info->rules;
            } else {
                $this->tldIndex[$tld] = array();
            }
        }
        return $this->tldIndex[$tld];
    }
}
