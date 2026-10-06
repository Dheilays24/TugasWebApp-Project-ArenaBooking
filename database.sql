DROP DATABASE IF EXISTS arenabook;
CREATE DATABASE arenabook CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE arenabook;
CREATE TABLE users(id INT AUTO_INCREMENT PRIMARY KEY,name VARCHAR(100) NOT NULL,email VARCHAR(150) NOT NULL UNIQUE,password VARCHAR(255) NOT NULL,role ENUM('admin','customer') NOT NULL DEFAULT 'customer',created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB;
CREATE TABLE customers(id INT AUTO_INCREMENT PRIMARY KEY,user_id INT NOT NULL UNIQUE,phone VARCHAR(30) NOT NULL,address TEXT,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE) ENGINE=InnoDB;
CREATE TABLE fields(id INT AUTO_INCREMENT PRIMARY KEY,name VARCHAR(100) NOT NULL,type ENUM('Futsal','Badminton') NOT NULL,description TEXT,price_per_hour INT NOT NULL,facilities VARCHAR(255),status ENUM('active','inactive') NOT NULL DEFAULT 'active',created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB;
CREATE TABLE schedules(id INT AUTO_INCREMENT PRIMARY KEY,field_id INT NOT NULL,schedule_date DATE NOT NULL,start_time TIME NOT NULL,end_time TIME NOT NULL,status ENUM('available','booked','blocked') NOT NULL DEFAULT 'available',created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,UNIQUE KEY uq_slot(field_id,schedule_date,start_time,end_time),FOREIGN KEY(field_id) REFERENCES fields(id) ON DELETE RESTRICT) ENGINE=InnoDB;
CREATE TABLE bookings(id INT AUTO_INCREMENT PRIMARY KEY,booking_code VARCHAR(30) NOT NULL UNIQUE,customer_id INT NOT NULL,field_id INT NOT NULL,schedule_id INT NOT NULL,booking_date DATE NOT NULL,start_time TIME NOT NULL,end_time TIME NOT NULL,total_price INT NOT NULL,status ENUM('pending','confirmed','cancelled','completed') NOT NULL DEFAULT 'pending',notes TEXT,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,FOREIGN KEY(customer_id) REFERENCES customers(id) ON DELETE RESTRICT,FOREIGN KEY(field_id) REFERENCES fields(id) ON DELETE RESTRICT,FOREIGN KEY(schedule_id) REFERENCES schedules(id) ON DELETE RESTRICT) ENGINE=InnoDB;
INSERT INTO users(name,email,password,role) VALUES
('Administrator ArenaBook','admin@arenabook.test','$2y$10$Gd8p/NNHfFieekAmlSYleeKhdFxx7TNp/d6/stWsLZzd42t2vBVIG','admin'),
('Customer Demo','customer@arenabook.test','$2y$10$q7VoEnTSHI9uCogjyfQo7uHFsTMOkRawVReT8hjLLVC.t7a3y6/Ia','customer');
INSERT INTO customers(user_id,phone,address) VALUES(2,'081234567890','Medan');
INSERT INTO fields(name,type,description,price_per_hour,facilities) VALUES
('Futsal 1','Futsal','Lapangan futsal indoor',100000,'Toilet, Parkir, Kantin'),
('Futsal 2','Futsal','Lapangan futsal indoor',100000,'Toilet, Parkir, Kantin'),
('Futsal 3','Futsal','Lapangan futsal indoor',100000,'Toilet, Parkir, Kantin'),
('Badminton 1','Badminton','Lapangan badminton indoor',50000,'Toilet, Parkir, Kantin'),
('Badminton 2','Badminton','Lapangan badminton indoor',50000,'Toilet, Parkir, Kantin'),
('Badminton 3','Badminton','Lapangan badminton indoor',50000,'Toilet, Parkir, Kantin');
