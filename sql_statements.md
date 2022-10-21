# Useful MySQL Statements

```SQL
/* select which DB to use */
USE employees;

SHOW tables;
```

```SQL
UPDATE bday_emails 
SET notified=FALSE 
WHERE notified=TRUE;
```

```SQL
SELECT first_name, last_name, date_of_birth 
FROM employees 
WHERE (first_name, last_name) 
    NOT IN (SELECT first_name, last_name FROM bday_emails);
```

**SQL stmt to put new active employees into bday_emails table:**

```SQL
INSERT INTO bday_emails (first_name, last_name, date_of_birth) 
SELECT first_name, last_name, date_of_birth 
FROM employees 
WHERE (first_name, last_name) NOT IN (SELECT first_name, last_name FROM bday_emails);
```

**Find which employees to notify about upcoming birthdays:**

```SQL
SELECT first_name, last_name, DATE_FORMAT(date_of_birth, '%m-%d') as DOB_no_year 
FROM bday_emails 
WHERE notified=FALSE 
AND (first_name, last_name) IN (SELECT first_name, last_name FROM employees WHERE DATE_FORMAT(date_of_birth, '%m-%d') >= DATE_FORMAT(NOW(), '%m-%d') 
AND DATE_FORMAT(date_of_birth, '%m-%d') <= DATE_FORMAT((NOW() + INTERVAL +$num_days DAY), '%m-%d') 
AND (start_date <= curdate() - interval (dayofmonth(curdate()) - 1) day - interval 6 month OR start_date IS NULL) 
ORDER BY DATE_FORMAT(date_of_birth, '%m-%d'));
```

```SQL
SELECT b.first_name, b.last_name, b.phone_number, b.email, DATE_FORMAT(b.date_of_birth, '%m-%d') AS DOB from bday_emails AS a 
INNER JOIN employees AS b 
ON a.first_name=b.first_name AND a.last_name=b.last_name 
WHERE a.notified=FALSE 
AND DATE_FORMAT(b.date_of_birth, '%m-%d') >= DATE_FORMAT(NOW(), '%m-%d') 
AND DATE_FORMAT(b.date_of_birth, '%m-%d') <= DATE_FORMAT((NOW() + INTERVAL +$num_days DAY), '%m-%d') 
AND (b.start_date <= curdate() - interval (dayofmonth(curdate()) - 1) day - interval 6 month OR b.start_date IS NULL) 
ORDER BY DATE_FORMAT(b.date_of_birth, '%m-%d');
```

```SQL
insert into users (userID, password_hash, display_name) VALUES(1, SHA2('THIS IS NOT REAL PASSPHRASE', 256), 'gabe');
```
