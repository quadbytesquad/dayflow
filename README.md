Dayflow — Human Resource Management System

Every workday, perfectly aligned

Dayflow is a lightweight HRMS that digitizes core HR operations: employee onboarding, profile management, attendance tracking, leave/time-off management, payroll visibility, and approval workflows for Admins and HR Officers.

  1. Purpose

This system defines and implements the functional and non-functional requirements of an HRMS aimed at streamlining:

• Employee onboarding
• Profile management
• Attendance tracking
• Leave / time-off management
• Payroll visibility
• Approval workflows for Admins and HR Officers

  2. Scope

Dayflow provides:

• Secure authentication (Sign Up / Sign In)
• Role-based access (Admin vs Employee)
• Employee profile management
• Attendance tracking (daily/weekly view)
• Leave and time-off management
• Approval workflows for HR/Admin

  3. Definitions & Abbreviations

| Term | Meaning |
|---|---|
|  Admin / HR Officer  | User with management and approval privileges |
|  Employee  | Regular user with limited access |
|  Time-Off  | Paid leave, sick leave, unpaid leave, etc. |

---

  4. User Classes and Characteristics

| User Type | Description |
|---|---|
|  Admin / HR Officer  | Manages employees, approves leave & attendance, views payroll details |
|  Employee  | Views personal profile, attendance, applies for leave, views salary details |

---

  5. Functional Requirements

   5.1 Authentication & Authorization

 Sign Up 
• Users register using: Employee ID, Email, Password, Role (Employee / HR)
• Password must follow security rules
• Email verification is required

 Sign In 
• Users log in using email and password
• Incorrect credentials display error messages
• Successful login redirects to the dashboard

   5.2 Dashboard

 Employee Dashboard 
• Quick-access cards: Profile, Attendance, Leave Requests, Logout
• Recent activity / alerts

 Admin / HR Dashboard 
• Employee list
• Attendance records
• Leave approvals
• Ability to switch between employees

   5.3 Employee Profile Management

 View Profile 
• Personal details
• Job details
• Salary structure
• Documents
• Profile picture

 Edit Profile 
• Employees can edit limited fields (address, phone, profile picture)
• Admin can edit all employee details

   5.4 Attendance Management

 Attendance Tracking 
• Daily and weekly attendance views
• Check-in / check-out for employees
• Status types: Present, Absent, Half-day, Leave

 Attendance View 
• Employees can view only their own attendance
• Admin/HR can view attendance of all employees

   5.5 Leave & Time-Off Management

 Apply for Leave (Employee) 
• Select leave type: Paid, Sick, Unpaid
• Choose date range
• Add remarks
• Status: Pending, Approved, Rejected

 Leave Approval (Admin/HR) 
• View all leave requests
• Approve or reject requests
• Add comments
• Changes reflect immediately in employee records

   5.6 Payroll / Salary Management

 Employee Payroll View 
• Read-only for employees

 Admin Payroll Control 
• View payroll of all employees
• Update salary structure
• Ensure payroll accuracy

   5.7 Notifications & Reporting

• Email & notification alerts
• Analytics & reports dashboard (salary slips, attendance reports, etc.)


  7. Tech Stack

•  Frontend:  HTML, CSS, vanilla JavaScript
•  Backend:  PHP
•  Storage:  File-based JSON (current) — pluggable toward a database as the system grows
