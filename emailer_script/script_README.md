# Script to schedule outgoing email notifications and update of database table

- This script edits the MySQL table called bday_emails
- It will be run daily on the GCP Compute Engine VM
- This script is  completely separate from and is unrelated to the apache2 web server
- This script uses JetMail API version 3.1, with public and private keys saved as environment variables in Bash shell
- The MySQL database credentials are pulled from a json file in a seperate directory
- The MailJet API credentials are pulled from a json file in a separate directory
- This script creates a daily log of html files organized by date in the folder ```bday_logs/```
  - **NOTE:**Might change this to something else from html later. Perhaps .txt files

## Usage

```bash
    php ./bday_emailer.php
```

### Notes

18:00 should be 1 PM on GCP Ubtuntu VM

- [ ] **Check log to make sure this is accurate. I scheduled for hour 18 in crontab**

## TODO

- [x] Schedule this to be run daily with Ubutnu Cronjob
- [x] make it so that new additions don't replace current and set everyone to not-notified

Copyright 2022 by Gabe Scott
