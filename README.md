# Automate Adding of Employee info Excel Files to MySQL DB on GCP Server with Automated B-day Notification Email

## This application performs the following tasks

- Uploads an excel file (initially) to Google cloud storage bucket using Google Storage API calls and API key
  - Coule possibly change verification method to OAuth2 in the near future
- Sends POST request to Apache2 web server back-end containing action to perform, using API key authorization
- Downloads xlsx file to local folder on Google Cloud Compute Engine VM from Google Cloud Storage bucket on the back-end
- Converts Excel file to csv according to chosen formatting specifications
- Imports .csv file into MySQL database
  - Authenticates user and grants permission to use and change the MySQL database
- Returns POST request containing file upload metadata, various success/fail messages, and new file locations
- Displays the data from the spreadsheet file in the ```index.php``` page
  - ***Possibly using spreadsheets.js***
- Schedules and automates sending of emails with SMTP to go out to employees wishing them a happy birthday just before their birth date
- Updates database of employees when new files are uploaded, or when employee info changes (roughly once per month)

**Notes to Self:**

- Apache2 publicly hosted files are in ```/var/www/html/``` on webserver
- API Key, DB credentials are in ```/var/www/keys/``` in the form of json
- File Upload script requires ```/var/www/vendor/``` (created with Composer for php)
  - ***Should not be publicly visible***
- Apache2 php.ini file is located at ```/etc/php/8.1/apache2/php.ini```
  - This is separate from ubuntu php.ini file which is located at ```/etc/php/8.1/cli/php.ini``` 
    - conf.d file is located at ```/etc/php/8.1/cli/conf.d```
  - Enabled extension=myslqi here by uncommenting line after searching in vim with ```ls ./ | grep php```
- Working with GCP from command line is made very easy in VSCode bash terminal with the GCP extension. Makes it easy to SCP files to VM

## Setting up and configuring Composer for PHP

1. I initially set up Composer at the project level on apache at ```/var/www/composer```
2. Next I configured the ```composer.json``` file to add project dependencies
3. Then I ran ```php composer.phar update```
4. I added require statments for ```google/cloud-storage``` and ```phpoffice/phpspreadsheet``` and version numbers
5. I moved ```vendor/``` to lowest level of apache, just below public html files folder

**Important Composer Commands:**

- ```php composer.phar update```

## Important PHP Commands

- See where all php.ini configuration files are ```php --ini```
- Create info.php file and host it on apache web server. This file has a lot of useful configuration information

  ```php
  // info.php
  <?php phpinfo() ?>
  ```

- Get current user: Add ```echo exec('whoami');``` to above ```info.php``` file

### TODO

- [ ] Clean up GCP folders and remove old file versions
- [ ] See about changing permissions on Google cloud storage bucket to not be quite so open
- [ ] See about moving php scripts and other files to ```scripts/```
- [ ] Add more of my saved resources to Resources section below in README
- [ ] Handle multiple files being uploaded
- [ ] See about obscuring certain php files
- [ ] Tighten up user authentification all over, and secure site against potential SQL injection attacks
- [ ] Change mysql db user info for user www-data
- [ ] Read PhpSpreadsheet open-source license and see if attribution is needed in documentation somewhere
- [ ] Look into the possibility of encrypting sensitive files (GCP Secret API key)
- [x] Successfully handle connecting to MySQL DB
- [x] Handle conversion from xlsx to csv
- [x] Return csv file path in json from POST request return
- [x] Hide MySQL DB credentials
  - [ ] Double check that this is not accessible from outside
- [x] Store password for mysqli to grab for db outside public folders. in ```secret/```
- [x] Add ```composer.lock``` to git repository

#### Resources

- [Installing and setting up PHP on Ubuntu - ServerLab](https://www.serverlab.ca/tutorials/linux/web-servers-linux/installing-php-for-apache-on-ubuntu/)
- [Composer Documentation](https://getcomposer.org/doc/01-basic-usage.md)
- [PHP for uploading to GCS - Zatackcoder (blog)](https://zatackcoder.com/upload-file-to-google-cloud-storage-using-php/)
- [PhpSpreadsheet - open source tool](https://phpspreadsheet.readthedocs.io/en/latest/topics/reading-and-writing-to-file/)
- [Download Storage object php - GCP Documentation](https://cloud.google.com/storage/docs/downloading-objects#storage-download-object-php)
- [Cloud Storage Reference - GCP Documentation](https://cloud.google.com/storage/docs/reference/libraries)
- [Google Cloud API - Storage Client](https://googleapis.github.io/google-cloud-php/#/docs/google-cloud/v0.122.0/storage/storageclient)
- [JQuery API - data](https://api.jquery.com/data/)
- [Set Apache File Permissions (owner)](https://askubuntu.com/questions/1334375/how-to-set-both-www-data-and-me-as-owner)
- [Grant user file permissions (groups)](https://askubuntu.com/questions/365087/grant-a-user-permissions-on-www-data-owned-var-www)
- [Import CSV into MySQL](https://www.phpflow.com/php/import-csv-file-into-mysql/)
- [MySQLi PHP Database extension](https://www.php.net/manual/en/book.mysqli.php)
- [mysqli](https://www.php.net/manual/en/mysqli.quickstart.dual-interface.php)
- [mysqli documentation](https://www.php.net/manual/en/class.mysqli.php)
- [Grant User Permissions in MySQL](https://phoenixnap.com/kb/how-to-create-new-mysql-user-account-grant-privileges)
