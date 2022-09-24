# Upload and Convert files to GCP cloud storage

Upload a file to Google cloud storage bucket. Convert Excel files to csv and import into MySQL database.

**Notes:**

- Public Files are in ```/var/www/html/``` on apache2 webserver
- API Key is in ``/var/www/keys/``
- Upload script requires /var/www/vendor/ (created with Composer for php)
  - Needs to not be publicly visible

9/23/22:

- Moved ```vendor/``` out of public html folder and back into ```www/``` folder

## TODO

- [ ] Handle multiple files being uploaded
- [x] Handle else

### Resources

- [Zatackcoder php uploading](https://zatackcoder.com/upload-file-to-google-cloud-storage-using-php/)
- [PhpSpreadsheet](https://phpspreadsheet.readthedocs.io/en/latest/topics/reading-and-writing-to-file/)
