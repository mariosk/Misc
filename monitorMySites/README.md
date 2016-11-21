**PHP script to monitor your websites periodically**
=================================================================

There are times you need to monitor whether your websites have changed size or even dropped. You can do it with the following php script in a periodic way.
-
### Step-1. Deploy monitorMySites.php under a path of your preference in webserver.

### Step-2. Deploy monitorMySites.txt under the same path. Example contents of the txt file:

```bash
# <website to monitor> *** <bytes of the toplevel page> <bytes to add/remove in comparison>
http://www.example.com/ *** 683 100
```
####Parameters:

a. URL of the web site to monitor

b. Static delimeter "***"

c. Number of bytes to compare with

d. Some of the websites are dynamic ones so the number of the bytes you need to compare is not certain each time the robot script runs. For this reason the last parameter defines the number of bytes to add or subtrack from parameter (c). This parameter is optional.

### Step-3. Create a CRON entry in your CPANEL

For example if you need to run it once a day: 
```bash
0	0 *	* * /usr/bin/php /home/user_dir/public_html/monitorMySites.php
```
