# Upload and Convert files to GCP cloud storage

Upload a file to Google cloud storage bucket. Convert Excel files to csv and import into MySQL database.

**Notes:**

- Public Files are in ```/var/www/html/``` on apache2 webserver
- API Key, DB credentials are in ```/var/www/keys/```
- File Upload script requires ```/var/www/vendor/``` (created with Composer for php)
  - Should not be publicly visible
- Apache2 php.ini file is located at ```/etc/php/8.1/apache2/php.ini```
  - This is separate from ubuntu php.ini file
  - Enabled extension=myslqi here by uncommenting line after searching in vim

## Setting up and configuring Composer

1. I initially set up Composer at the project level on apache at ```/var/www/composer```
2. Next I configured the ```composer.json``` file
3. Then I ran ```php composer.phar update```
4. I added require statments for ```google/cloud-storage``` and ```phpoffice/phpspreadsheet``` and version numbers
5. I moved ```vendor/``` to lowest level of apache, just below public html files folder
  - important commands:
    - ```php composer.phar update```
    - 


## TODO

- [ ] Handle multiple files being uploaded
- [ ] See about hiding certain php files
- [ ] Change mysql db pw for user www-data
- [x] Successfully handle connecting to MySQL DB
- [x] Handle conversion from xlsx to csv
- [x] Return csv file path in json from POST request return
- [x] Hide MySQL DB credentials
  - [ ] Double check that this is not accessible from outside
- [x] Store password for mysqli to grab for db outside public folders. in secret/

### Resources

- [Composer Documentation](https://getcomposer.org/doc/01-basic-usage.md)
- [PHP for uploading to GCS - Zatackcoder](https://zatackcoder.com/upload-file-to-google-cloud-storage-using-php/)
- [PhpSpreadsheet](https://phpspreadsheet.readthedocs.io/en/latest/topics/reading-and-writing-to-file/)
- [Download Storage object php - GCP Documentation](https://cloud.google.com/storage/docs/downloading-objects#storage-download-object-php)
- [Cloud Storage Reference - GCP Documentation](https://cloud.google.com/storage/docs/reference/libraries)
- [Google Cloud API - Storage Client](https://googleapis.github.io/google-cloud-php/#/docs/google-cloud/v0.122.0/storage/storageclient)
- [JQuery API - Data](https://api.jquery.com/data/)
- [Set Apache File Permissions (owner)](https://askubuntu.com/questions/1334375/how-to-set-both-www-data-and-me-as-owner)
- [Grant user file permissions (groups)](https://askubuntu.com/questions/365087/grant-a-user-permissions-on-www-data-owned-var-www)
- [Import CSV into MySQL](https://www.phpflow.com/php/import-csv-file-into-mysql/)
- [MySQLi PHP Database extension](https://www.php.net/manual/en/book.mysqli.php)
- [mysqli](https://www.php.net/manual/en/mysqli.quickstart.dual-interface.php)
- [mysqli documentation](https://www.php.net/manual/en/class.mysqli.php)
- [Grant User Permissions in MySQL](https://phoenixnap.com/kb/how-to-create-new-mysql-user-account-grant-privileges)
