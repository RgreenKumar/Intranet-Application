CREATE DATABASE IF NOT EXISTS users_db;
USE users_db;

CREATE TABLE IF NOT EXISTS users (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(255) NOT NULL,
    email      VARCHAR(255) NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,
    role       ENUM('USER','ADMIN') NOT NULL DEFAULT 'USER',
    status     ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS products (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(255) NOT NULL,
    description TEXT,
    tool_url    VARCHAR(1024),
    image_path  VARCHAR(1024) DEFAULT NULL,
    status      ENUM('Active','Inactive') NOT NULL DEFAULT 'Active',
    created_by  INT DEFAULT 0,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
);