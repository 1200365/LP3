CREATE TABLE students (
	id INT PRIMARY KEY,
	name VARCHAR(255) NOT NULL,
	password VARCHAR(255) NOT NULL,
	mail VARCHAR(255) NOT NULL,
	grade VARCHAR(255) NOT NULL,
	status INT NOT NULL,
	register DATE NOT NULL,
	change_date DATE
);
