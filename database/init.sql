CREATE DATABASE IF NOT EXISTS autorent;
USE autorent;

CREATE TABLE IF NOT EXISTS cars (
    id INT AUTO_INCREMENT PRIMARY KEY,
    brand VARCHAR(100) NOT NULL,
    model VARCHAR(100) NOT NULL,
    engine VARCHAR(50),
    fuel VARCHAR(50),
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255),
    year INT,
    transmission VARCHAR(50),
    seats INT,
    description TEXT,
    status VARCHAR(50) DEFAULT 'vaba'
);

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(50) DEFAULT 'user'
);

CREATE TABLE IF NOT EXISTS reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    car_id INT NOT NULL,
    user_name VARCHAR(100) NOT NULL,
    user_email VARCHAR(150) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status VARCHAR(50) DEFAULT 'aktiivne',
    FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE
);

INSERT INTO cars (brand, model, engine, fuel, price, image, year, transmission, seats, description, status) VALUES
('Audi', 'Q8', 'V6', 'Bensiin', 120.00, 'https://loremflickr.com/400/250/audi', 2019, 'Automaat', 5, 'Luksuslik ja mugav maastur', 'vaba'),
('Mercedes', 'A-Class', 'V4', 'Bensiin', 90.00, 'https://loremflickr.com/400/250/mercedes', 2020, 'Automaat', 5, 'Kompaktne ja ökonoomne linnaauto', 'vaba'),
('BMW', 'X5', 'V6', 'Diisel', 140.00, 'https://loremflickr.com/400/250/bmw', 2021, 'Manuaal', 5, 'Võimas ja töökindel SUV', 'vaba'),
('Audi', 'R8', 'V10', 'Bensiin', 250.00, 'https://loremflickr.com/400/250/audi-r8', 2022, 'Automaat', 2, 'Sportlik ja kiire superauto', 'vaba');
