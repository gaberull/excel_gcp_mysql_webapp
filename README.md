# Excel File to MySQL DB Uploader

**Summary:**
Automate the addition of Employee records stored in Excel spreadsheets to a MySQL database on GCP Server, and automate scheduling of outgoing "happy birthday" email to the employee

## Complete List of Tasks Performed

- Uploads an excel file (.xlsx) to a Google cloud storage bucket using Google Storage API calls and API key
- Sends POST request to Apache2 web server back-end containing action to perform, using API key authorization
- Downloads .xlsx file to Apache local folder for processing on Google Cloud Compute Engine VM server from Google Cloud Storage bucket
- Converts Excel file to .csv file according to chosen formatting specifications
- Imports .csv file into hosted MySQL database
  - Authenticates user and grants permission to use and change the MySQL database
- Returns POST request containing file upload metadata, various success/fail messages, and new file locations
- Displays the data from the spreadsheet file in the ```index.php``` page
  - ***Possibly formatted using spreadsheets.js (not implemented as of yet)***
- Schedules and automates sending of SMTP emails with to go out to employees wishing them a happy birthday a few days before their birth-date
- Updates database of employees when new files are uploaded, or when employee info changes (roughly once per month)
  - Prior to upload of up-to-date employee spreadsheet, all employees in the MySQL database are marked as "inactive" employees. Then the database records are updated and marked as "active" once again as each entry in the spreadsheet is inserted or re-inserted into the MySQL database

### Objectives of Project - Note from Developer

I have been learning PHP on the fly on this project, having never worked with it before. I have also been brushing-up on my server-client programming and setting up webserver type applications and static web-page concepts, as well as learning more about the Ajax, PHP, and HTTP technology stack. Additionally, I have been working at cementing my knowledge of cloud computing concepts, with Apache web server and Google Cloud Platform. So far, it has been a very fruitful project.

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

### Important Composer Commands

- ```php composer.phar update```

### Important PHP Usage

- See where all php.ini configuration files are ```php --ini```
- Create info.php file and host it on apache web server. This file has a lot of useful configuration information when viewed in web browser and looks exactly like this:

  ```php
  // info.php
  <?php phpinfo() ?>
  ```

- Get current user: Add ```echo exec('whoami');``` to above ```info.php``` file

## TODO

- [ ] Rename repository "Spreadsheet Emailer" ? 
- [x] Successfully handle connecting to MySQL DB
- [x] Handle conversion from xlsx to csv
- [x] Return csv file path in json from POST request return
- [x] Hide MySQL DB credentials
  - [ ] *Encrypt credentials on GCP VM ?*
  - [ ] Double check that this is not accessible from outside
- [x] Store password for mysqli to grab for db outside public folders. in ```secret/```
- [x] Add ```composer.lock``` to git repository
- [x] Add more of my saved resources to Resources section below in README
- [ ] Consider changing user verification method to OAuth2
- [ ] Figure out which open-source license to add to this project before making repository public
- [ ] Clean up GCP folders and remove old file versions
- [ ] See about changing permissions on Google cloud storage bucket to not be quite so open
- [ ] See about moving php scripts and other files to ```scripts/```
- [ ] Handle multiple files being uploaded
- [ ] Maybe add similar Link to download .csv file from website (would require upload to GCS like xlsx file)
- [ ] See about obscuring certain php files
- [ ] Tighten up user authentification all over, and secure site against potential SQL injection attacks
- [ ] Change mysql db user info for user www-data
- [ ] Read PhpSpreadsheet open-source license and see if attribution is needed in documentation somewhere
- [ ] Look into the possibility of encrypting sensitive files (GCP Secret API key)

## Resources

- [Installing and setting up PHP on Ubuntu - ServerLab](https://www.serverlab.ca/tutorials/linux/web-servers-linux/installing-php-for-apache-on-ubuntu/)
- [Composer Documentation](https://getcomposer.org/doc/01-basic-usage.md)
- [PHP for uploading to GCS - Zatackcoder (blog)](https://zatackcoder.com/upload-file-to-google-cloud-storage-using-php/)
- [PhpSpreadsheet - open source tool](https://phpspreadsheet.readthedocs.io/en/latest/topics/reading-and-writing-to-file/)
- [PhpSpreadsheet - GitHub Repository](https://github.com/PHPOffice/PhpSpreadsheet)
- [Convert xlsx to csv file - Stack Overflow](https://stackoverflow.com/questions/6895665/convert-xlsx-file-to-csv-file-using-php)
- [Download Storage object php - GCP Documentation](https://cloud.google.com/storage/docs/downloading-objects#storage-download-object-php)
- [Cloud Storage Reference - GCP Documentation](https://cloud.google.com/storage/docs/reference/libraries)
- [Google Cloud API - Storage Client](https://googleapis.github.io/google-cloud-php/#/docs/google-cloud/v0.122.0/storage/storageclient)
- [JQuery API - data](https://api.jquery.com/data/)
- [Set Apache File Permissions (owner)](https://askubuntu.com/questions/1334375/how-to-set-both-www-data-and-me-as-owner)
- [Grant user file permissions (groups)](https://askubuntu.com/questions/365087/grant-a-user-permissions-on-www-data-owned-var-www)
- [Import CSV into MySQL](https://www.phpflow.com/php/import-csv-file-into-mysql/)
- [MySQLi PHP Database extension](https://www.php.net/manual/en/book.mysqli.php)
- [PHP mysqli class](https://www.php.net/manual/en/class.mysqli)
- [mysqli documentation](https://www.php.net/manual/en/class.mysqli.php)
- [MySQL Documentation](https://dev.mysql.com/doc/refman/8.0/en/)
- [Grant User Permissions in MySQL](https://phoenixnap.com/kb/how-to-create-new-mysql-user-account-grant-privileges)
- [Import csv into MySQL DB](https://www.phpflow.com/php/import-csv-file-into-mysql/)
- [Set all values in a table to specific value - mysql](https://stackoverflow.com/questions/13612104/how-to-set-all-values-in-a-single-column-mysql-query)
- [Convert str to Date/DateTime - PHP](https://www.geeksforgeeks.org/php-converting-string-to-date-and-datetime/)
- [Retreive cell data - PhpSpreadsheet (Stack Overflow)](https://stackoverflow.com/questions/44304795/how-to-retrieve-date-from-table-cell-using-phpspreadsheet)
- [date() man page - PHP](https://www.php.net/manual/en/function.date.php)
- [NumberFormat PhpSpreadsheet](https://github.com/PHPOffice/PhpSpreadsheet/blob/master/src/PhpSpreadsheet/Style/NumberFormat.php)
- [PhpSpreadsheet Recipes](https://phpspreadsheet.readthedocs.io/en/latest/topics/recipes/)
- [MySQL Update](https://www.mysqltutorial.org/mysql-update-data.aspx)
- [fgetcsv() man page - PHP](https://www.php.net/manual/en/function.fgetcsv.php)
- [mysqli_real_query() - W3Schools](https://www.w3schools.com/php/func_mysqli_real_query.asp)
- [Replace Command - MySQL](https://dev.mysql.com/doc/refman/8.0/en/replace.html)
- [Insert Date Object in MySQL](https://www.ntchosting.com/encyclopedia/databases/mysql/insert-date/#:~:text=The%20default%20way%20to%20store,the%20dates%20as%20you%20expect.)
- [Data Types - MySQL Docs](https://dev.mysql.com/doc/refman/8.0/en/data-types.html)
- [Database Privileges MySQL](https://askubuntu.com/questions/1029177/error-1698-28000-access-denied-for-user-rootlocalhost-at-ubuntu-18-04)
- [Escaping from HTML - PHP](https://www.php.net/manual/en/language.basic-syntax.phpmode.php)
- [$_SERVER array - PHP](https://www.php.net/manual/en/reserved.variables.server.php)
- [Import CSV to MySQL using PHP file read](https://phppot.com/php/import-csv-file-into-mysql-using-php/)
- **[Append text into html by id - PHP](https://stackoverflow.com/questions/35886770/php-append-text-into-html-element-with-certain-id)**
- [mysqli->query() man page](https://www.php.net/manual/en/mysqli.query.php)

Copyright 2022 - Gabe Scott
