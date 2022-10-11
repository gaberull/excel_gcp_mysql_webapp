# Useful MySQL Statements

```SQL
USE employees;
SHOW tables;
```

```SQL
update bday_emails set notified=FALSE WHERE notified=TRUE;
```

```SQL
SELECT first_name, last_name, date_of_birth FROM employees WHERE (first_name, last_name) NOT IN (SELECT first_name, last_name FROM bday_emails);
```

**SQL stmt to put new active employees into bday_emails table:**

```SQL
INSERT INTO bday_emails (first_name, last_name, date_of_birth) SELECT first_name, last_name, date_of_birth FROM employees WHERE (first_name, last_name) NOT IN (SELECT first_name, last_name FROM bday_emails);
```

**SQL stmt to find which employees to notify about upcoming birthdays:**

```SQL
SELECT first_name, last_name, DATE_FORMAT(date_of_birth, '%m-%d') as DOB_no_year from bday_emails where notified=FALSE and (first_name, last_name) in (SELECT first_name, last_name FROM employees WHERE DATE_FORMAT(date_of_birth, '%m-%d') >= DATE_FORMAT(NOW(), '%m-%d') AND DATE_FORMAT(date_of_birth, '%m-%d') <= DATE_FORMAT((NOW() + INTERVAL +$num_days DAY), '%m-%d') AND (start_date <= curdate() - interval (dayofmonth(curdate()) - 1) day - interval 6 month OR start_date IS NULL) ORDER BY DATE_FORMAT(date_of_birth, '%m-%d'));";
```
