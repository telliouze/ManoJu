create database to_do;

CREATE TABLE tasks (id serial PRIMARY KEY, description VARCHAR (255), category_id BIGINT(20);

+-------------+---------------------+------+-----+---------+----------------+
| Field       | Type                | Null | Key | Default | Extra          |
+-------------+---------------------+------+-----+---------+----------------+
| id          | bigint(20) unsigned | NO   | PRI | NULL    | auto_increment |
| description | varchar(255)        | YES  |     | NULL    |                |
| category_id | bigint(20)          | YES  |     | NULL    |                |
+-------------+---------------------+------+-----+---------+----------------+

CREATE TABLE categories (id serial PRIMARY KEY, name VARCHAR(255));

+-------+---------------------+------+-----+---------+----------------+
| Field | Type                | Null | Key | Default | Extra          |
+-------+---------------------+------+-----+---------+----------------+
| id    | bigint(20) unsigned | NO   | PRI | NULL    | auto_increment |
| name  | varchar(255)        | YES  |     | NULL    |                |
+-------+---------------------+------+-----+---------+----------------+

CREATE TABLE users (id serial PRIMARY KEY, username VARCHAR(50), password VARCHAR(50), salt TEXT, roles VARCHAR(50));

+----------+---------------------+------+-----+---------+----------------+
| Field    | Type                | Null | Key | Default | Extra          |
+----------+---------------------+------+-----+---------+----------------+
| id       | bigint(20) unsigned | NO   | PRI | NULL    | auto_increment |
| username | varchar(50)         | YES  |     | NULL    |                |
| password | varchar(50)         | YES  |     | NULL    |                |
| salt     | text                | YES  |     | NULL    |                |
| roles    | varchar(50)         | YES  |     | NULL    |                |
+----------+---------------------+------+-----+---------+----------------+
