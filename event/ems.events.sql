CREATE TABLE events (
	id INT PRIMARY KEY AUTO_INCREMENT,
	event_name VARCHAR(255) NOT NULL,
	event_applicant INT NOT NULL,
	event_start DATETIME NOT NULL,
	event_finish DATETIME NOT NULL,
	event_content TEXT NOT NULL,
	event_deadline DATETIME NOT NULL,
	milestone_first DATETIME NOT NULL,
	milestone_second DATETIME NOT NULL,
	note TEXT,
	register_date DATE NOT NULL,
	change_date DATE
);
