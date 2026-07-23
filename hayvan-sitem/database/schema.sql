-- SQL schema for the hayvan_sitem database

-- Create the 'ilanlar' table for pet listings
CREATE TABLE ilanlar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    baslik VARCHAR(255) NOT NULL,
    aciklama TEXT NOT NULL,
    cins_id INT,
    hastalik_id INT,
    kategori_id INT,
    il_id INT,
    ilce_id INT,
    durum ENUM('Aktif', 'sahiplenildi') DEFAULT 'Aktif',
    tarih TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cins_id) REFERENCES cinsler(id),
    FOREIGN KEY (hastalik_id) REFERENCES hastaliklar(id),
    FOREIGN KEY (kategori_id) REFERENCES kategoriler(id),
    FOREIGN KEY (il_id) REFERENCES il(id),
    FOREIGN KEY (ilce_id) REFERENCES ilce(id)
);

-- Create the 'cinsler' table for pet breeds
CREATE TABLE cinsler (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ad VARCHAR(100) NOT NULL
);

-- Create the 'hastaliklar' table for pet diseases
CREATE TABLE hastaliklar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ad VARCHAR(100) NOT NULL
);

-- Create the 'kategoriler' table for pet categories
CREATE TABLE kategoriler (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ad VARCHAR(100) NOT NULL
);

-- Create the 'il' table for cities
CREATE TABLE il (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ad VARCHAR(100) NOT NULL
);

-- Create the 'ilce' table for districts
CREATE TABLE ilce (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ad VARCHAR(100) NOT NULL,
    il_id INT,
    FOREIGN KEY (il_id) REFERENCES il(id)
);