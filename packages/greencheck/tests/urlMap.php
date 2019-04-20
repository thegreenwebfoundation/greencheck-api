<?php
// A map with values to map and as last value the return value.
$map = [
    ['a.b.c',  ['ip' => false, 'ipv6' => false]],
    ['www.nonexistingurlblablalba.nl',  ['ip' => false, 'ipv6' => false]],

    ['www.iping.nl', ['ip' => '94.75.237.71','ipv6' => false]],
    ['www.cleanbits.nl', ['ip' => '94.75.237.71','ipv6' => false]],


    // use for testing subdomain handling
    ['no-www-registered.nl', ['ip' => '94.75.237.71','ipv6' => false]],
    ['www.no-www-registered.nl', ['ip' => '94.75.237.71','ipv6' => false]],
    ['blog.no-www-registered.nl', ['ip' => '194.75.237.69','ipv6' => false]],

    // outside a fixture range
    ['www.free.fr', ['ip' => '94.75.237.69','ipv6' => false]],
    // xs4all ip range
    ['www.xs4all.nl', ['ip' => '194.109.21.4','ipv6' => false]],

    ['www.ipv6.xs4all.nl', ['ip' => false,'ipv6' => '2001:888::18:0:0:0:80']],
    ['also.xs4all.hosted.nl', ['ip' => '194.109.21.4','ipv6' => false]],

    ['was.greenbutexpired.nl', ['ip' => '194.75.237.71','ipv6' => false]],
    // outside ip range for checkIp function
    ['www.nu.nl', ['ip' => '94.75.237.69','ipv6' => false]],
    // not a real ip address
    ['94.75.237.8912', ['ip' => '94.75.237.8912','ipv6' => false]],
    // Not green Ips but green AS
    ['www.netexpo.nl', ['ip' => '88.151.33.85','ipv6' => false]],
    ['www.ashoster.nl', ['ip' => '88.151.33.85','ipv6' => false]],
    // looking up a url with an ipv6 address
    ['webmail.mailplatform.eu', ['ip' => '92.243.6.32', 'ipv6' => '2001:4b98:dc0:41:216:3eff:fedd:3317']],
    // not an address
    ['null', ['ip' => false, 'ipv6' => false]],
    [null, ['ip' => false, 'ipv6' => false]]
];
?>