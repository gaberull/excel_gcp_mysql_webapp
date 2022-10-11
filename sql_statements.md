# Usefule MySQL Statements

```SQL
update bday_emails set notified=FALSE WHERE notified=TRUE;
```

```SQL
SHOW tables;
```

```SQL
SELECT first_name, last_name, date_of_birth FROM employees WHERE (first_name, last_name) NOT IN (SELECT first_name, last_name FROM bday_emails);
```

**SQL stmt to put new active employees into bday_emails table:**

```SQL
INSERT INTO bday_emails (first_name, last_name, date_of_birth) SELECT first_name, last_name, date_of_birth FROM employees WHERE (first_name, last_name) NOT IN (SELECT first_name, last_name FROM bday_emails);
```
