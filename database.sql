-- EduTrack Database Setup
CREATE DATABASE IF NOT EXISTS edutrack;
USE edutrack;

-- Students Table
CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_no VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    course VARCHAR(100) NOT NULL,
    department VARCHAR(100),
    year_level INT DEFAULT 1,
    status ENUM('active', 'inactive', 'graduated') DEFAULT 'active',
    enrolled_date DATE DEFAULT (CURRENT_DATE),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Subjects Table
CREATE TABLE IF NOT EXISTS subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject_code VARCHAR(20) UNIQUE NOT NULL,
    subject_name VARCHAR(100) NOT NULL,
    department VARCHAR(100),
    units INT DEFAULT 3,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Grades Table
CREATE TABLE IF NOT EXISTS grades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    subject_id INT NOT NULL,
    grade DECIMAL(5,2) NOT NULL,
    semester VARCHAR(20) DEFAULT '1st Sem',
    school_year VARCHAR(10) DEFAULT '2024-2025',
    remarks VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
);

-- Sample Data: Students
INSERT INTO students (student_no, name, email, course, department, year_level, status, enrolled_date) VALUES
('001', 'Keza Nemah', 'keza@example.com', 'Software Development', 'IT Department', 1, 'active', '2024-09-01'),
('2024-0001', 'Alice Johnson', 'alice@example.com', 'BS Computer Science', 'Computer Science', 2, 'active', '2024-09-01'),
('2024-0002', 'Bob Martinez', 'bob@example.com', 'BS Information Technology', 'IT Department', 1, 'active', '2024-09-01'),
('2024-0003', 'Carol White', 'carol@example.com', 'BS Computer Engineering', 'Engineering', 1, 'active', '2024-09-01'),
('2023-0021', 'David Kim', 'david@example.com', 'BS Computer Science', 'Computer Science', 2, 'active', '2023-09-01'),
('2022-0045', 'Eva Cruz', 'eva@example.com', 'BS Information Systems', 'IT Department', 4, 'graduated', '2022-09-01');

-- Sample Data: Subjects
INSERT INTO subjects (subject_code, subject_name, department, units) VALUES
('CS101', 'Introduction to Programming', 'Computer Science', 3),
('CS201', 'Data Structures & Algorithms', 'Computer Science', 3),
('IT101', 'Web Development', 'IT Department', 3),
('IT201', 'Database Management', 'IT Department', 3),
('ENG101', 'Computer Architecture', 'Engineering', 3);

-- Sample Data: Grades (uses subqueries to avoid hardcoded IDs)
INSERT INTO grades (student_id, subject_id, grade, semester, school_year)
SELECT s.id, sub.id, 88.0, '1st Sem', '2024-2025'
FROM students s JOIN subjects sub ON sub.subject_code = 'CS101'
WHERE s.student_no = '2024-0001' LIMIT 1;

INSERT INTO grades (student_id, subject_id, grade, semester, school_year)
SELECT s.id, sub.id, 92.5, '1st Sem', '2024-2025'
FROM students s JOIN subjects sub ON sub.subject_code = 'CS201'
WHERE s.student_no = '2024-0001' LIMIT 1;

INSERT INTO grades (student_id, subject_id, grade, semester, school_year)
SELECT s.id, sub.id, 78.0, '1st Sem', '2024-2025'
FROM students s JOIN subjects sub ON sub.subject_code = 'IT101'
WHERE s.student_no = '2024-0002' LIMIT 1;

INSERT INTO grades (student_id, subject_id, grade, semester, school_year)
SELECT s.id, sub.id, 85.5, '1st Sem', '2024-2025'
FROM students s JOIN subjects sub ON sub.subject_code = 'ENG101'
WHERE s.student_no = '2024-0003' LIMIT 1;

INSERT INTO grades (student_id, subject_id, grade, semester, school_year)
SELECT s.id, sub.id, 95.0, '1st Sem', '2024-2025'
FROM students s JOIN subjects sub ON sub.subject_code = 'CS101'
WHERE s.student_no = '2023-0021' LIMIT 1;
