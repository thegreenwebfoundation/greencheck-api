# If you can't look up websites

Because the greencheck libraries rely on DNS lookups, if the nameservers you are using can not find a path from a domain name to a IP address, you won't be able to see the content.

You'll see this, because you'll get an `Invalid url` error.

## Fixing this with Ubuntu

If you are using Ubuntu 18 upwards, you can see the name servers in use by the `/etc/resolv.conf`.

They usually should look something like:

```
nameserver 85.17.150.123
nameserver 83.149.80.123
nameserver 85.17.96.69
nameserver 127.0.0.53
search localdomain
```

You can add this by editing `/etc/resolv.conf` directly, but it's better to edit the files in `/etc/resolvconf/resolv.conf.d/`, which are used to generate the required `resolve.conf` file instead.

Once you have added the correct DNS server, call `resolvconf -u`, to regenerate the necessary `resolv.conf` file.
