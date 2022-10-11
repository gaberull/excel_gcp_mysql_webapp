# Script to schedule outgoing email notifications and update of database table

- This script edits the MySQL table called bday_emails
- It will be run daily on the GCP Compute Engine VM
- This script uses JetMail API version 3, with public and private keys saved as environment variables in Bash shell
- The MySQL database credentials are pulled from a seperate directory.
- This script creates a daily log of html files organized by date in the folder ```bday_logs/```
  - Might change this to something else from html later. Perhaps .txt files

## Usage

```bash
    php ./bday_emailer.php
```

## TODO

- [ ] Schedule this to be run daily with Ubutnu Cronjob
- [ ] Maybe make logs something other than html maybe
- [x] make it so that new additions don't replace current and set everyone to not-notified

Copyright 2022 by Gabe Scott
