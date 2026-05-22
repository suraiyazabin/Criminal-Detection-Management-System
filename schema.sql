CREATE TABLE users (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(100) NOT NULL,
    email         VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    phone         VARCHAR(20),
    role          ENUM('officer','detective','admin') NOT NULL,
    badge_number  VARCHAR(50),
    is_active     TINYINT(1) DEFAULT 1,
    created_at    DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE criminals (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    full_name      VARCHAR(150) NOT NULL,
    alias          VARCHAR(100),
    dob            DATE,
    gender         ENUM('male','female','other'),
    nationality    VARCHAR(80),
    address        TEXT,
    photo_path     VARCHAR(255),
    threat_level   ENUM('low','medium','high','critical') DEFAULT 'medium',
    status         ENUM('wanted','arrested','released','deceased') DEFAULT 'wanted',
    description    TEXT,
    created_by     INT,
    created_at     DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE cases (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    case_number    VARCHAR(50) NOT NULL UNIQUE,
    title          VARCHAR(200) NOT NULL,
    description    TEXT,
    crime_type     VARCHAR(100),
    location       VARCHAR(200),
    incident_date  DATE,
    status         ENUM('open','under_investigation','solved','closed') DEFAULT 'open',
    assigned_to    INT,
    created_by     INT,
    created_at     DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (assigned_to) REFERENCES users(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);
CREATE TABLE case_criminals (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    case_id      INT NOT NULL,
    criminal_id  INT NOT NULL,
    role_in_case VARCHAR(100),
    FOREIGN KEY (case_id)     REFERENCES cases(id),
    FOREIGN KEY (criminal_id) REFERENCES criminals(id)
);
CREATE TABLE evidence (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    case_id       INT NOT NULL,
    title         VARCHAR(150) NOT NULL,
    description   TEXT,
    file_path     VARCHAR(255),
    evidence_type ENUM('document','photo','video','physical','digital') DEFAULT 'document',
    collected_by  INT,
    collected_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (case_id)      REFERENCES cases(id),
    FOREIGN KEY (collected_by) REFERENCES users(id)
);
CREATE TABLE reports (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    case_id     INT NOT NULL,
    officer_id  INT NOT NULL,
    title       VARCHAR(200) NOT NULL,
    content     TEXT NOT NULL,
    report_type ENUM('initial','progress','final','incident') DEFAULT 'progress',
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (case_id)    REFERENCES cases(id),
    FOREIGN KEY (officer_id) REFERENCES users(id)
);
CREATE TABLE arrests (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    criminal_id  INT NOT NULL,
    case_id      INT NOT NULL,
    arrested_by  INT NOT NULL,
    arrest_date  DATETIME NOT NULL,
    location     VARCHAR(200),
    notes        TEXT,
    created_at   DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (criminal_id) REFERENCES criminals(id),
    FOREIGN KEY (case_id)     REFERENCES cases(id),
    FOREIGN KEY (arrested_by) REFERENCES users(id)
);
INSERT IGNORE INTO users (name, email, password_hash, role, badge_number)
VALUES (
    'Admin Officer',
    'admin@cdms.gov',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'admin',
    'ADM-001'
);