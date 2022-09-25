# Upload and Convert files to GCP cloud storage

Upload a file to Google cloud storage bucket. Convert Excel files to csv and import into MySQL database.

**Notes:**

- Public Files are in ```/var/www/html/``` on apache2 webserver
- API Key is in ``/var/www/keys/``
- Upload script requires ```/var/www/vendor/``` (created with Composer for php)
  - Should not be publicly visible

9/23/22:

- Moved ```vendor/``` out of public html folder and back into ```var/www/``` folder

## TODO

- [ ] Handle multiple files being uploaded
- [x] Handle conversion from xlsx to csv
- [x] Return csv file path in json from POST request return

### Resources

- [Zatackcoder php uploading](https://zatackcoder.com/upload-file-to-google-cloud-storage-using-php/)
- [PhpSpreadsheet](https://phpspreadsheet.readthedocs.io/en/latest/topics/reading-and-writing-to-file/)
- [Download Storage object php - GCP Documentation](https://cloud.google.com/storage/docs/downloading-objects#storage-download-object-php)
- [Cloud Storage Reference - GCP Documentation](https://cloud.google.com/storage/docs/reference/libraries)
- [Google Cloud API - Storage Client](https://googleapis.github.io/google-cloud-php/#/docs/google-cloud/v0.122.0/storage/storageclient)
- [JQuery API - Data](https://api.jquery.com/data/)
- [Set Apache File Permissions (owner)](https://askubuntu.com/questions/1334375/how-to-set-both-www-data-and-me-as-owner)