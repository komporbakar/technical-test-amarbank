# Technical Test Amar Bank - PHP Engineer

### Installation

```
git clone https://github.com/komporbakar/technical-test-amarbank.git
```

```
composer update
```

### Setup Database

```raw
CREATE DATABASE IF NOT EXISTS `amarbank_loans`;
USE `amarbank_loans`;

CREATE TABLE `loans` (
    `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255pinjamans) NOT NULL,
    `ktp` VARCHAR(16) NOT NULL,
    `loan_amount` INT(10) NOT NULL,
    `loan_period` INT(10) NOT NULL,
    `loan_purpose` TINYTEXT NOT NULL,
    `date_of_birth` DATE NULL DEFAULT NULL,
    `sex` VARCHAR(50) NOT NULL,
    PRIMARY KEY (`id`) USING BTREE
);
```

### Running Program

```
composer start
```

OR

```
php -S localhost:8080 -t public
```

### Running Test

```
composer test
```

### Endpoint in create in the App

#### Create Loan

```
Endpoint : http://localhost:8080/api/loan
Method : POST
```

- Request Body :

```json
{
  "first_name": "Aris",
  "last_name": "asds",
  "ktp": "6521332412200020",
  "loan_amount": 2300,
  "loan_periode": 3,
  "loan_purpose": "Vacation in duty",
  "date_of_birth": "2000-12-24",
  "sex": "male"
}
```

- Response Body Success (200):

```json
{
  "error": false,
  "message": "Loan succesfully created",
  "data": {
    "id": 84,
    "name": "Aris asds",
    "ktp": "6521332412200020",
    "loan_amount": 2300,
    "loan_periode": 3,
    "loan_purpose": "Vacation in duty",
    "date_of_birth": "2000-12-24",
    "sex": "male"
  }
}
```

- Response Body Failed (400)

```json
{
  "error": true,
  "message": "Failed Create Loan",
  "data": [Optional]
}
```

#### Thank You,
