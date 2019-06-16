# A high level view of how the Green Web Foundatin Greenchecks work

We use The Green Web Foundation (referred to as the TGWF from now on) "greencheck" library to tell if a domain is listed as 'green' or not.

## Life before TGWF

Parts of the TGWF greencheck came from an earlier project, cleanbits.nl, a platform designed in part to allow owners of websites that run on fossil fuels to 'offset' their emissions from running the server.

There is list of domains and their status as offset/compensated in TGWF as a result.

## Pseudo code for checking a domain

This pseudo code outlines how the greencheck library works.

- accept a full url, and pull out the domain name from the request
  - if domain is in the list of already compensated ones, where the company has taken a decision to offset emissions, or use green power even if the underlying infra is not, mark it as green and return
- get IP
- look up IP in recorded green IP ranges (green hosters can have a list of green IPs)
  - if there is an matching IP in the green IP list given by orgs that have registered them, mark it as green and return, the name of the hosting organisation associated with the IP range
- look up IP in ASN ranges to work out which ASN it refers to (green hosters can have a list of green ASNs)
  - if the IP belong to an ASN which is entirely using green power, mark it as green, and return the name of the hosting provider associated with the ASN

## How are hosting providers marked as running on green power?

Because green hosting has been relatively rare, it's been possible to build a database manually over the last 10 years of green infrastructure providers.

To get the smilng green face, green infrastructure providers sign up at admin.thegreenwebfoundation, and provide evidence of running on green power - either certificates of origin for power, or offsets, or through some back and forth with the TGWF staff to work out what information would be relevant in a given region.

Once a request for 'green' status is approved, the domains, IP ranges and sometimes the ASNs owned by the green infrastructure providers are marked as green.

In addition, any sites that are from from the same IP ranges or ASNs are also marked as green, as they are assumed to be running from the same datacentres, and unless otherwise claimsed hosted by the same provider.

This way, you only need to track a number of 'upstream' hosting companies or providers or the internet to end up with a decent amount of coverage of the web.
