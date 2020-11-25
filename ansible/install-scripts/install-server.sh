# we need redis
apt install redis-server --yes

# we need mariadb
apt install mariadb-server --yes

# we need nginx
apt install nginx --yes


# we need certbot
apt install snapd --yes
snap install core; sudo snap refresh core
snap install --classic certbot
snap set certbot trust-plugin-with-root=ok


apt install git --yes


