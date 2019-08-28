# zcs-zpush
This repository is source for integrating Zimbra Single Server and Z-Push + Zimbra Backend to achieve ActiveSync on Zimbra OSE.

# Integrating Zimbra and Z-push on CentOS 7

Install dependecies

```bash
yum install epel-release -y
yum update -y
yum upgrade -y
yum install git php-cli php-soap php-process php-mbstring -y
```

Clone repo

```bash
git clone https://github.com/imanudin11/zcs-zpush.git
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

Backup and replace jetty.xml.in

```bash
cp /opt/zimbra/jetty/etc/jetty.xml.in /opt/zimbra/jetty/etc/jetty.xml.in.backup
cp jetty.xml.in-for-zcs-8815 /opt/zimbra/jetty/etc/jetty.xml.in
chown zimbra.zimbra /opt/zimbra/jetty/etc/jetty.xml.in
```

Replace php.ini

```bash
cp /etc/php.ini /etc/php.ini.backup
cp php.ini /etc/php.ini
```

Restart Zimbra Mailbox

```bash
su - zimbra -c 'zmmailboxdctl restart'
```

For testing, please access https://ip-of-zimbra/Microsoft-Server-ActiveSync from your browser. Or you can configure your mobile devices and ensure exchange as protocol
