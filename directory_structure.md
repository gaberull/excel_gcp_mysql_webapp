# Structure of Directories, Important Files for Project

**Note:** This web application no longer running on the original server

- var/www/   (*document root folder*)
  - uploads/   (*folder where new files are added. Not publicly accessible*)
    - recent_excel.xlsx -  (*After upload*)
    - employees_0.csv   - (*After conversion*)
    - protected.html  - (*pre-encryption file - also index.php*)
  - domain.com/  -  (*APACHE2 project root folder*)
    - vendor/
      - autoload.php
    - keys/
      - GCS_api_key.json
      - mysql_db_credentials.json
    - html/   -  (*Publicly accessible folder*)
      - employees.html (index.php)
      - config.php
      - requests.php
      - subcategories.php
      - style.css
      - censored_demo/  - (*no ability to upload, no sensitive data displayed*)
        - index.php
        - config.php
        - requests.php
        - subcategories.php
        - style.css

- ~/
  - bday_emailer/
    - bday_logs/
      - log_date1_time1.html
      - log_date2_time2.html
      - ...
    - keys/
      - mysql_db_credentials.json
      - mailjet_credentials.json
    - vendor/
      - autoload.php
      - composer/
      - ...
    - bday_emailer.php
