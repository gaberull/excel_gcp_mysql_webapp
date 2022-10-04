# Excel to CSV File Converter, MySQL DB Uploader, and Email Scheduler

**Summary:**

Upload spreadsheet of employee records to a MySQL database on running on a GCP Server, View employee data in the database, and automate the scheduling of outgoing "happy birthday" emails to the employees

## Complete List of Tasks Performed by this App

- Uploads an excel file (.xlsx) to a Google cloud storage bucket using Google Storage API calls, PHP code, and ajax POST request
- Sends POST request to Apache2 web server containing action to perform, using API key authorization
- Downloads .xlsx file to GCP local folder for processing on Google Cloud Compute Engine VM server from Google Cloud Storage bucket
- Converts Excel file to .csv file according to chosen formatting specifications
- Imports parsed data from .csv file into hosted MySQL database
  - Authenticates user and grants permission to use and change the MySQL database
- Return from POST request contains file upload metadata, various success/fail messages, the individual SQL queries executed, and new file locations
- Displays the data from the spreadsheet file in the ```index.php``` page in the client browser
- Schedules and automates sending of SMTP emails with to go out to employees wishing them a happy birthday a few days before their birth-date
- Updates database of employees when new files are uploaded, or when employee info changes (roughly once per month)
  - Prior to upload of up-to-date employee spreadsheet, a query is run to mark all employees in the MySQL database as "inactive" employees
  - Then the database records are updated and marked with "active=TRUE" once again as each entry in the spreadsheet is inserted or re-inserted into the MySQL database. This is accomplished with a REPLACE statement like the following:

    ```SQL
    REPLACE INTO employees (first_name, last_name, start_date, date_of_birth, address, email, phone_number, schedule, position, active) VALUES (?,?,?,?,?,?,?,?,?,?);
    
    ```

## Future Functionality (Still in Progress)

As of 9/29/22:

- Printout of current database of employees
- More secure user authentification and authorization to upload to, access Google Cloud Storage bucket
- Scheduling of SMTP emails to go out to employees prior to their birthday
- Functions avilable in the user interface to query the database
- Styling of main page
- Change primary key from email to combination of first and last names
  - Currently 'email' is primary key because it is guaranteed to be unique, however the company could potentially not have the email address of an employee on file

## Objectives of Project (Note from Developer)

I have been learning PHP on the fly on this project, having never worked with it before. I have also been brushing-up on my server-client programming and setting up webserver type applications and static web-page concepts, as well as learning more about the Ajax, PHP, and HTTP technology stack. Additionally, I have been working at cementing my knowledge of cloud computing concepts, with Apache web server and Google Cloud Platform. So far, it has been a very fruitful project.

**Notes to Self:**

- Important file directory structure info can be found in 
- Apache2 publicly hosted files are in ```/var/www/html/``` on webserver
- API Key, DB credentials are in ```/var/www/keys/``` in the form of json
- File Upload script requires ```/var/www/vendor/``` (created with Composer for php)
  - ***Should not be publicly visible***
- Apache2 php.ini file is located at ```/etc/php/8.1/apache2/php.ini```
  - This is separate from ubuntu php.ini file which is located at ```/etc/php/8.1/cli/php.ini``` 
    - conf.d file is located at ```/etc/php/8.1/cli/conf.d```
  - Enabled extension=myslqi here by uncommenting line after searching in vim with ```ls ./ | grep php```
- Working with GCP from command line is made very easy in VSCode bash terminal with the GCP extension. Makes it easy to SCP files to VM

## Installation Instructions

**Initial Notes:**

- The files in this repository are not *currently* organized according in the same structure as they are on my server, and thus one cannot simply clone this repository and expect it to work correctly. 
  - Throughout my server-side development process, I have been peforming an SCP every time I wanted to push changes to the server rather than just pushing/pulling from my git repo
- To set up the correct directory organization perform the following steps
  - For the sake of simplicity, I'm going to work with the assumption that you already have a working Apache2 webserver running on a GCP Compute Engine virtual machine - *clearly a big assumption*

### Steps

- View the ```director_structure.md``` file here: [directory_structure.md](directory_structure.md)
- Set up MySQL database on GCP Compue Engine VM
- TODO: FINISH these steps

#### Setting up and configuring Composer for PHP

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
    <?php
      // info.php
      echo("Current user is: "); // optional - get apache2 web server user
      echo exec('whoami');
      phpinfo(); 
     ?>
    ```

- Get current user: Add ```echo exec('whoami');``` to above ```info.php``` file

## TODO

- [ ] Rename repository "Spreadsheet Emailer" ?
- [ ] Style app main webpage
- [ ] Finish steps for one to replicate what I did to set it up
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
- [x] Hide MySQL DB credentials
  - [ ] *Encrypt credentials on GCP VM ?*
  - [ ] Double check that this is not accessible from outside
- [x] Successfully handle connecting to MySQL DB
- [x] Handle conversion from xlsx to csv
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
