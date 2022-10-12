# Excel to MySQL

Excel to CSV File Converter, MySQL Database Uploader and Viewer, and automated Email Scheduler

**Summary:**

Upload spreadsheet of employee records to a MySQL database running on a GCP Server, view employee data from the database in a web browser, and automate the scheduling of outgoing notification emails to the employees' boss for a reminder to send a gift

## Complete List of Tasks Performed

- Dynamically offers drop down menu options to the user based on choice of uploading new data, or displaying existing data from the database in the browser
- Uploads an excel file (.xlsx) to a Google Cloud Storage Bucket using Google Storage API calls, PHP, and ajax POST requests, then appends current date to uploaded file name for long-term storage and organization of previous employee files
- Sends POST request to Apache2 web server containing action to perform, using API key authorization
- Downloads .xlsx file to GCP local folder for processing on Google Cloud Compute Engine VM server from Google Cloud Storage bucket
- Displays uploaded file in it's original state to the browser using PhpSpreadsheet open-source software
- Converts Excel file to .csv file according to chosen formatting specifications
- Parses and imports data from the .csv file into hosted MySQL database
  - Authenticates user from locally saved credentials and grants permission to access and alter the MySQL database
- POST request reply contains file upload metadata, various success/fail messages, the individual SQL queries executed and their success/failure statuses, and new file locations
- Displays the data from the spreadsheet file in the [index.php](index.php) page in the client browser
- Each day, the VM runs the script [bday_emailer.php](emailer_script/bday_emailer.php) and checks the mysql database for upcoming employee birthdays (of employees of at least 6 months), automates sending of email to the boss with a reminder to send a birthday gift
- Updates database of employees when new files are uploaded, or when employee info changes (roughly once per month)
  - Prior to upload of up-to-date employee spreadsheet, a query is run to mark all employees in the MySQL database as "inactive" employees
  - Then the database records are updated and marked with "active=TRUE" once again as each entry in the spreadsheet is inserted or re-inserted into the MySQL database. This is accomplished with a REPLACE statement like the following:

  ```SQL
  REPLACE INTO employees (first_name, last_name, start_date, date_of_birth, address, email, phone_number, schedule, position, active) VALUES (?,?,?,?,?,?,?,?,?,?);
  ```

More examples of MySQL statements can be viewed in [sql_statements.md](sql_statements.md)

## Demo

Note: These images have been censored within the HTML in order to protect the information of the employees

### Uploading an Excel File to the Database

### Displaying Employee Records

Note: The database records are censored to protect the privacy of real employees

**Display All Employees:**

WIDTH=600
<br><br>
[<img src="https://gabrielscott.io/hosted_files/all_censored.gif" alt="Display All Employees (gif)" width="600"/>](https://gabrielscott.io/hosted_files/all_censored.gif)

<br><br><br><br>

ORIGINAL - 60% width, height
![Display All Employees (gif)](https://gabrielscott.io/hosted_files/all_censored.gif){ width=60% height=60% }

**Display Active/Inactive Employees:**

<br><br>

![Active/Inactive Employees (gif)](https://gabrielscott.io/hosted_files/active_censored.gif)
**Display Employees with Upcoming Birthdays:**

<br><br>

![Employee Birthdays (gif)](https://gabrielscott.io/hosted_files/bdays_censored.gif)

## Future Functionality (Tasks still in progress)

- Specific options avilable in the user interface to query the database

## Objectives of Project (Note from Developer)

I have been learning PHP on the fly on this project, having never worked with it before. I have also been brushing-up on my server-client programming and setting up webserver type applications and static web-page concepts, as well as learning more about the Ajax, PHP, JavaScript, and HTTP technology stack. Additionally, I have been working at cementing my knowledge of cloud computing concepts, with Apache web server and Google Cloud Platform. So far, it has been a very fruitful project

**Notes to Self (dev):**

- Important file directory structure info can be found in [directory_structure.md](directory_structure.md)
- Apache2 publicly hosted files are in ```/var/www/boolsa.io/html``` on webserver
- API Key, DB credentials are in ```/var/www/boolsa.io/keys/``` in the form of json
- File Upload script requires ```/var/www/boolsa.io/vendor/``` (created with Composer for php)
  - ***Should not be publicly visible***
- Apache2 php.ini file is located at ```/etc/php/8.1/apache2/php.ini```
  - This is separate from ubuntu php.ini file which is located at ```/etc/php/8.1/cli/php.ini```
    - conf.d file is located at ```/etc/php/8.1/cli/conf.d```
  - Enabled extension=myslqi here by uncommenting line after searching in vim with ```ls ./ | grep php```
- Working with GCP from command line is made very easy in VSCode bash terminal with the GCP extension. Makes it easy to SCP files in to the VM

## Installation Instructions

**Initial Notes:**

- The files in this repository are not *currently* organized according in the same structure as they are on the GCP server, and thus **one cannot simply clone this repository and expect it to work correctly**
  - Throughout my server-side development process, I have been peforming an SCP every time I wanted to push changes to the server rather than just pushing/pulling from my git repo
- To set up the correct directory organization perform the following steps
  - For the sake of simplicity, I'm going to work with the assumption that you already have a working Apache2 webserver running on a GCP Compute Engine virtual machine - *clearly a big assumption*

## Setting up and configuring Composer for PHP

1. I initially set up Composer at the project level on apache at ```/var/www/boolsa.io/composer```
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
  <?php
    // info.php
    echo("Current user is: "); // optional - get apache2 web server user
    echo exec('whoami');
    phpinfo(); 
    ?>
  ```

- Get current user: Add ```echo exec('whoami');``` to above ```info.php``` file

## TODO

- [ ] Add phone number formatting (dashes) for other display options other than display all
- [ ] Rename repository ``excel_to_mysql``
- [ ] Delete files created during the upload process (.csv, .html, etc)
- [ ] Create screen recording gif of web application in action (don't use sensitive data)
- [ ] maybe color code by position
- [ ] Calendar visual aide with birthdays on it could be nice - show 2 months (this,next)
- [ ] See about moving style.css out of html folder in GCP VM
- [ ] Adjust and secure the user authorization to upload to, access Google Cloud Storage bucket
- [ ] Consider changing user verification method to OAuth2
  - [ ] Add authorization
- [ ] Figure out which open-source license to add to this project before making repository public
- [ ] Clean up GCP folders and remove old file versions
- [ ] See about changing permissions on Google cloud storage bucket to not be quite so open
- [ ] See about moving php scripts and other files to a folder called ``scripts/``
- [ ] See about obscuring certain php files
- [ ] Tighten up user authentification all over, and secure site against potential SQL injection attacks
- [ ] Change mysql db user info for user www-data
- [ ] Read PhpSpreadsheet open-source license and see if attribution is needed in documentation somewhere
- [ ] Look into the possibility of encrypting sensitive files (GCP Secret API key)
- [x] In "dislay all employees" use background color of red for inactive employees
- [x] Add check in [bday_emailer.php](emailer_script/bday_emailer.php) script to only notify about employees who have been at the company for at least 6 months
- [x] Change primary key from email to combination of first and last names
- [x] In dislay all employees sort by active, then last name
- [x] Style app main webpage
  - [ ] Could use more styling
- [x] Hide MySQL DB credentials
  - [ ] *Encrypt credentials on GCP VM ?*
  - [ ] Double check that this is not accessible from outside
- [x] Add date to uploaded xlsx files for storage
- [x] Add ability to print data from MySQL database
- [x] Successfully handle connecting to MySQL DB
- [x] Handle conversion from xlsx to csv
- [x] Make sure birthdays displayed are active employees
- [x] Trim .csv entries before adding to mysql query strings
  - [x] Trim leading/trailing asterisks
  - [x] Trim leading/trailing whitespace and whitespace-like special characters
  - [x] remove extra whitespace-like special characters
- [x] Return csv file path in json from POST request return
- [x] Store password for mysqli to grab for db outside public folders. in ```secret/```
- [x] Add ```composer.lock``` to git repository
- [x] Add more of my saved resources to Resources section below in README

## Resources

See page [resources.md](resources.md) to view a list of many (but not all) of the specific resources that I referenced

Copyright 2022 - Gabe Scott
