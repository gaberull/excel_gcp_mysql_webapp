# Structure of Directories, Important Files in Apache Webserver

- var/www/boolsa.io  -  (*APACHE2 root folder*)
  - vendor/
    - autoload.php
  - keys/
    - GCS_api_key.json
    - mysql_db_credentials.json
  - html/       -  (*Publicly accessible folder*)
    - index.php
    - config.php
    - requests.php
    - uploads/
      - EMPLOYEE_PROFILES.xlsx -  (*After upload*)
      - employees_0.csv   - (*After conversion*)
