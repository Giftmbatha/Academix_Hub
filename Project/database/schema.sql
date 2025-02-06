USE student_management_system;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(255) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  email VARCHAR(255) DEFAULT NULL,
  role VARCHAR(10) NOT NULL
  profile_photo VARCHAR(255) DEFAULT NULL;
);

CREATE TABLE events (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  description TEXT,
  start_date DATE NOT NULL,
  end_date DATE DEFAULT NULL
);

CREATE TABLE courses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  course_code VARCHAR(20) NOT NULL,
  course_name VARCHAR(255) NOT NULL,
  description TEXT,
  credits INT NOT NULL
);

CREATE TABLE classes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  course_id INT NOT NULL,
  lecturer_id INT NOT NULL,
  schedule_time TIME NOT NULL,
  room VARCHAR(50) NOT NULL,
  FOREIGN KEY (course_id) REFERENCES courses(id),
  FOREIGN KEY (lecturer_id) REFERENCES users(id)
);

CREATE TABLE enrollments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  course_id INT NOT NULL,
  progress INT DEFAULT 0,
  FOREIGN KEY (student_id) REFERENCES users(id),
  FOREIGN KEY (course_id) REFERENCES courses(id)
);

CREATE TABLE assessments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  class_id INT NOT NULL,
  title VARCHAR(255) NOT NULL,
  due_date DATE NOT NULL,
  FOREIGN KEY (class_id) REFERENCES classes(id)
);

CREATE TABLE communications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sender_id INT NOT NULL,
  recipient_id INT DEFAULT NULL,
  message TEXT NOT NULL,
  sent_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (sender_id) REFERENCES users(id),
  FOREIGN KEY (recipient_id) REFERENCES users(id)
);

CREATE TABLE attendance (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  class_id INT NOT NULL,
  status VARCHAR(10) NOT NULL,
  FOREIGN KEY (student_id) REFERENCES users(id),
  FOREIGN KEY (class_id) REFERENCES classes(id)
);

CREATE TABLE grades (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  class_id INT NOT NULL,
  grade VARCHAR(10) NOT NULL,
  FOREIGN KEY (student_id) REFERENCES users(id),
  FOREIGN KEY (class_id) REFERENCES classes(id)
);

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE chats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE chat_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chat_id INT NOT NULL,
    user_id INT NOT NULL,
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (chat_id) REFERENCES chats(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chat_id INT NOT NULL,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (chat_id) REFERENCES chats(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

ALTER TABLE communications
ADD title VARCHAR(255) NOT NULL;

-- Add enrollment_date to enrollments table
ALTER TABLE enrollments
ADD enrollment_date DATE ;

-- Add name to users table
ALTER TABLE users
ADD name VARCHAR(255) NOT NULL;

-- Add schedule to classes table
ALTER TABLE classes
ADD schedule VARCHAR(255) NOT NULL;

ALTER TABLE attendance
ADD attendance_date DATE;

ALTER TABLE courses ADD lecturer_id INT;

ALTER TABLE assessments 
ADD COLUMN course_id INT NOT NULL;

ALTER TABLE grades  
ADD COLUMN assessment_id INT NOT NULL;

ALTER TABLE assessments ADD COLUMN details TEXT;

ALTER TABLE assessments ADD COLUMN description TEXT;

ALTER TABLE classes ADD COLUMN course_name VARCHAR(255);

ALTER TABLE assessments ADD file_path VARCHAR(255);

CREATE TABLE assessment_submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    assessment_id INT NOT NULL,
    student_id INT NOT NULL,
    submission_text TEXT,
    file_path VARCHAR(255),
    submission_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (assessment_id) REFERENCES assessments(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
);


ALTER TABLE enrollments
ADD class_id INT NOT NULL AFTER course_id,
ADD FOREIGN KEY (class_id) REFERENCES classes(id);
