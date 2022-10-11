# Crontab file

Schedule operations to run in Ubuntu with ``` crontab -e ```

## My Crontab file in GCP

```bash
# Notice that tasks will be started based on the cron's system
# daemon's notion of time and timezones.
# 
# Output of the crontab jobs (including errors) is sent through
# email to the user the crontab file belongs to (unless redirected).
# 
# For example, you can run a backup of all your user accounts
# at 5 a.m every week with:
# 0 5 * * 1 tar -zcf /var/backups/home.tgz /home/
# 
# For more information see the manual pages of crontab(5) and cron(8)
# 
# m h  dom mon dow   command
# Run script that updates bday_emails mysql db and sends mailjet email
# 0 4 * * * php /home/gabescott/bday_emailer/bday_emailer.php
0 10 * * * php /home/gabescott/bday_emailer/bday_emailer.php
```
