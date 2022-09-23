## Upload and convert files to GCP cloud storage

Upload a file to Google cloud storage bucket. Convert Excel files to csv and import into MySQL database.

**Notes:**
- Public Files are in /var/www/html on apache2 webserver
- API Key is in /var/www/keys
- Upload script requires /var/www/html/vendor/ (created with composer for php)

### Resources

- <https://zatackcoder.com/upload-file-to-google-cloud-storage-using-php/>
cd 

9/23/22:
- Moved 'vendor/' out of public html folder and back into www folder