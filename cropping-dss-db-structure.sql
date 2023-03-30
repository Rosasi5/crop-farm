DROP DATABASE cropping;
CREATE DATABASE IF NOT EXISTS cropping;
USE cropping;

CREATE TABLE IF NOT EXISTS county(
	county_id INT(2) PRIMARY KEY,
	county_name VARCHAR(20) NOT NULL
);

CREATE TABLE IF NOT EXISTS soil_type(
	soil_id INT(1) PRIMARY KEY AUTO_INCREMENT,
	soil_name VARCHAR(20) UNIQUE
);

CREATE TABLE IF NOT EXISTS constituency(
	constituency_id INT(3) PRIMARY KEY,
	constituency_name VARCHAR(20) NOT NULL,
	county_id INT(2),
	soil_type_id INT(1),
	FOREIGN KEY (county_id) REFERENCES county(county_id) ON DELETE SET NULL,
	FOREIGN KEY (soil_type_id) REFERENCES soil_type(soil_id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS rainfall_distribution(
	rainfall_id INT(2) PRIMARY KEY AUTO_INCREMENT,
	rainfall_range VARCHAR(20) UNIQUE NOT NULL
);

CREATE TABLE IF NOT EXISTS temperatures(
	temperature_id INT(2) PRIMARY KEY AUTO_INCREMENT,
	temperature_range VARCHAR(9) UNIQUE NOT NULL
);

CREATE TABLE IF NOT EXISTS humidity(
	humidity_id INT(2) PRIMARY KEY AUTO_INCREMENT,
	humidity_range VARCHAR(9) UNIQUE NOT NULL
);

-- DROP TABLE crops;
CREATE TABLE IF NOT EXISTS crops(
	crop_id INT(3) PRIMARY KEY AUTO_INCREMENT,
	crop_name VARCHAR(20) NOT NULL UNIQUE,
	soil_id VARCHAR(10),
	rainfall_id INT(2),
	temperature_id INT(2),
	humidity_id INT(2),
-- 	FOREIGN KEY (soil_id) REFERENCES soil_type(soil_id) ON DELETE SET NULL,
	FOREIGN KEY (rainfall_id) REFERENCES rainfall_distribution(rainfall_id) ON DELETE SET NULL,
	FOREIGN KEY (temperature_id) REFERENCES temperatures(temperature_id) ON DELETE SET NULL,
	FOREIGN KEY (humidity_id) REFERENCES humidity(humidity_id) ON DELETE SET NULL
);








