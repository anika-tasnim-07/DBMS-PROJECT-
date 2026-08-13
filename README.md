# DBMS-PROJECT-Skill Swap & Student Service Exchange Platform

## Overview
Exchange skills. Grow Together. 
Connect with peers to share your expertise, learn new technologies, and complete verified skill assessments.

## Group Details
Group Number:04
Course Name:Database Management System 
Instructor: MD.Fahmidur Rahman Shakib

## Team Members
NAME                       |      ID          |  Contributors
---------------------------|------------------|---------------------
Anika Tasnim               |      241-115-254 |  HTML     - Frontend
Abdullah As Sayef Tanzim   |      242-115-261 |  CSS      - Frontend
Md. Nahidul Islam          |      242-115-279 |  PhPAdmin - Backend
Nahida Islam Shormi        |      242-115-287 |  SQL      - Database

## Objective
Skill Swap solves the challenge of finding trustworthy, accessible peer tutoring by enabling direct skill trading without financial barriers. By integrating automated test evaluations, students earn verified proficiency badges (Beginner, Intermediate, Expert) that guarantee skill authenticity before making swap requests.

## Features
1. Skill Management (CRUD)
2. Peer-to-Peer Swapping
3. Skill Verification
4. Automated Tier Assignment
5. Admin Monitoring Portal
6. Account Control
   
## Project Preview
### UI Screenshots
• Landing Page: <img width="1919" height="913" alt="Screenshot 2026-08-11 034724" src="https://github.com/user-attachments/assets/dac13144-22a6-4d0d-b2fb-28d49126a7ee" />
• Student Dashboard: <img width="1919" height="902" alt="Screenshot 2026-08-11 035045" src="https://github.com/user-attachments/assets/03f1753f-f2c3-4721-8702-5231316d76f9" />
• Verification Test Interface:<img width="938" height="905" alt="Screenshot 2026-08-11 035147" src="https://github.com/user-attachments/assets/66b4fc10-d2be-4350-81cd-250db79a9987" />
• Admin Portal:<img width="1917" height="915" alt="Screenshot 2026-08-11 035224" src="https://github.com/user-attachments/assets/59c5fea2-9350-432c-b539-6641010d52e3" />
• ER Diagram: <img width="627" height="597" alt="image" src="https://github.com/user-attachments/assets/848305ac-5f56-48f9-9d55-b6106a9e444f" />


## Tech Stack
*Frontend:
Built using HTML,CSS to style with clean variable themes and modern card/table layouts for responsive desktop navigation. 
*Backend:
Developed with PHP using native PHP sessions for role-based authentication and MySQL prepared statements for backed validation.
*Database:
Relational MySQL database managed via XAMPP / phpMyAdmin. Utilizes relational integrity (FOREIGN KEY with ON DELETE CASCADE), dynamic queries using SQL JOIN operations and automated aggregate counts (COUNT( *)) for admin matrics.

## Installation & Setup
# git clone:
https://github.com/anika-tasnim-07/DBMS-PROJECT-.git
# Navigate to project folder
D:\DBMS-PROJECT-
Setup the Database 
  1. Open phpMyAdmin (http://localhost/phpmyadmin)
  2. Create a database named 'skillswap'
  3. Import the schema.sql file from the database/ folder

## Project Structure

/skillswap
│── admin/
│ └── dashboard.php     # Admin monitoring & management portal
│── assets/
│ └── css/ 				 # Stylesheets and custom UI themes
│── config/
│ └── database.php 		 # MySQL connection script
│── database/
│ └── schema.sql 		 # Full database structure & quiz seed data
│── index.php 			 # Landing page
│── login.php 			 # Authentication page
│── register.php 		 # Student registration
│── dashboard.php 		 # Student home view
│── skills.php 			 # Skill CRUD & test score history
│── take_test.php 		 # Interactive skill verification quiz engine
└── README.md				 # Project documentation

## Video
https://drive.google.com/file/d/1A0jZdKDXqBUy07ACDEh5eTC5vEJ03JLz/view?usp=sharing
