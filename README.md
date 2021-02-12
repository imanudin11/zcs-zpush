# zcs-zpush
This repository is source for integrating Zimbra Single Server and Z-Push + Zimbra Backend to achieve ActiveSync on Zimbra OSE.

# Integrating Zimbra and Z-push

Install dependecies

For CentOS 7

```bash
yum install epel-release -y
yum update -y
yum upgrade -y
yum install git php-cli php-soap php-process php-mbstring -y
```
For Ubuntu 16.04 and 18.04

```bash
apt update -y
apt upgrade -y
apt install git php-cli php-soap php-cgi php-mbstring php-curl -y
```
Clone repo

```bash
git clone https://github.com/rusnazip/zcs-zpush
cd zcs-zpush/
```

Create folder for log

```bash
mkdir /var/lib/z-push /var/log/z-push
chmod 755 /var/lib/z-push /var/log/z-push
chown zimbra:zimbra /var/lib/z-push /var/log/z-push
```

Save z-push folder on /opt/

```bash
cp -rvf z-push /opt/
```

Note : I use Asia/Yakutsk as my Timezone. Please open /opt/z-push/config.php and /opt/z-push/autodiscover/config.php and adjust/change Asia/Yakutsk to your Timezone

Create symlink

```bash
ln -sf /opt/z-push /opt/zimbra/jetty/webapps/
```

Save php script on /usr/bin

```bash
cp php-cgi-fix.sh /usr/bin/php-cgi-fix.sh
chmod +x /usr/bin/php-cgi-fix.sh
```

Change publicHostname domain on your Zimbra into localhost

```bash
su - zimbra -c 'zmprov md yourzimbradomain.tld zimbraPublicServiceHostname localhost'
su - zimbra -c 'zmprov md yourzimbradomain.tld zimbraPublicServiceProtocol https'
```

# Backup and replace jetty.xml.in

For Zimbra 8.8.6

'''bash
cp /opt/zimbra/jetty/etc/jetty.xml.in /opt/zimbra/jetty/etc/jetty.xml.in.backup
cp jetty.xml.in-for-zcs-886 /opt/zimbra/jetty/etc/jetty.xml.in
chown zimbra.zimbra /opt/zimbra/jetty/etc/jetty.xml.in
'''

For Zimbra 8.8.7

```bash
cp /opt/zimbra/jetty/etc/jetty.xml.in /opt/zimbra/jetty/etc/jetty.xml.in.backup
cp jetty.xml.in-for-zcs-887 /opt/zimbra/jetty/etc/jetty.xml.in
chown zimbra.zimbra /opt/zimbra/jetty/etc/jetty.xml.in
```
For Zimbra 8.8.8 – Zimbra 8.8.12

'''bash
cp /opt/zimbra/jetty/etc/jetty.xml.in /opt/zimbra/jetty/etc/jetty.xml.in.backup
cp jetty.xml.in-for-zcs-888-8812 /opt/zimbra/jetty/etc/jetty.xml.in
chown zimbra.zimbra /opt/zimbra/jetty/etc/jetty.xml.in
'''

For Zimbra 8.8.15

'''bash
cp /opt/zimbra/jetty/etc/jetty.xml.in /opt/zimbra/jetty/etc/jetty.xml.in.backup
cp jetty.xml.in-for-zcs-8815 /opt/zimbra/jetty/etc/jetty.xml.in
chown zimbra.zimbra /opt/zimbra/jetty/etc/jetty.xml.in
'''

For Zimbra 9

'''bash
cp /opt/zimbra/jetty/etc/jetty.xml.in /opt/zimbra/jetty/etc/jetty.xml.in.backup
cp jetty.xml.in-for-zcs-9 /opt/zimbra/jetty/etc/jetty.xml.in
chown zimbra.zimbra /opt/zimbra/jetty/etc/jetty.xml.in
'''

# Add zpush.ini into php

For CentOS 7

'''bash
cp zpush.ini /etc/php.d/zpush.ini
'''

For Ubuntu 16.04

'''bash
cp zpush.ini /etc/php/7.0/cgi/conf.d/10-zpush.ini
'''

For Ubuntu 18.04

'''bash
cp zpush.ini /etc/php/7.2/cgi/conf.d/10-zpush.ini
'''

Restart Zimbra Mailbox

```bash
su - zimbra -c 'zmmailboxdctl restart'
```

For testing, please access https://ip-of-zimbra/Microsoft-Server-ActiveSync from your browser. Or you can configure your mobile devices and ensure exchange as protocol
