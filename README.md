# ConnectBridge
> A web-based platform connecting customers with trusted local service providers — electricians, plumbers, carpenters, tutors, mechanics, and cleaners.

---

## 📌 Overview

ConnectBridge allows users to discover and review verified local workers. Workers manage their profiles and track client feedback. Admins verify worker registrations and moderate the platform.

---

## 🎯 Objectives

- Simplify finding local service providers
- Allow workers to reach more customers
- Improve transparency through ratings and reviews
- Enable administrators to monitor and verify the platform

---

## ✨ Features

**User**
- Register & Login
- Browse and filter verified workers by profession and location
- View worker profiles, documents, and feedback
- Like / Dislike workers and post comments
- Book a service with date, time, and note
- View booking history & cancel pending bookings
- Rate and review workers after completed bookings

**Worker**
- Register with Aadhar & PAN verification
- View and update profile (description, address, mobile)
- Add and manage skills
- Set availability status
- Accept / Reject booking requests
- Mark jobs as completed
- View earnings summary (completed job count)
- View client feedback, ratings, and notifications

**Admin**
- Login to a separate dashboard
- View platform statistics (users, workers, bookings, ratings, complaints)
- Verify or delete worker registrations
- Manage registered users
- View and resolve / delete complaints

---

## 🛠 Technology Stack

| Component          | Technology              |
|--------------------|-------------------------|
| Frontend           | HTML5, CSS3             |
| Styling            | Bootstrap 5             |
| Client-side        | JavaScript              |
| Backend            | PHP (PDO)               |
| Database           | MySQL                   |
| Server             | Apache (XAMPP / WAMP)   |
| Database Tool      | phpMyAdmin              |

---

## 📂 Project Structure

```
DBMS2/
├── assets/
│   ├── css/
│   │   └── styles.css
│   └── images/
│       ├── log.jpg
│       ├── instalogo.jpg
│       └── twitterlogo.jpg
├── includes/
│   ├── config.php          ← DB credentials & app constants
│   ├── db.php              ← PDO connection (uses config.php)
│   ├── helpers.php         ← e(), redirect(), requireLogin/Worker/Admin()
│   └── auth.php            ← delegates to helpers.php
├── uploads/                ← worker Aadhar/PAN file uploads
├── views/
│   └── partials/
│       ├── navbar.php      ← shared navbar
│       └── footer.php      ← shared footer
├── index.php               ← homepage — verified workers listing & filter
├── login.php               ← user / worker login
├── register.php            ← user / worker registration
├── worker_details.php      ← worker profile, likes, comments
├── worker_dashboard.php    ← booking requests, availability, skills, earnings
├── notifications.php       ← likes, ratings & client feedback
├── admin_login.php         ← admin login
├── admin_dashboard.php     ← worker verification, users, complaints & statistics
├── booking.php             ← book a service
├── booking_history.php     ← view, cancel bookings & rate workers
├── aboutus.html
└── README.md
```

---

## 🚀 Installation

### Step 1 — Install XAMPP
Download and install [XAMPP](https://www.apachefriends.org/). Start **Apache** and **MySQL**.

### Step 2 — Copy project
```
C:\xampp\htdocs\DBMS2\
```

### Step 3 — Create database
Open `http://localhost/phpmyadmin` and create a database named **`worker_db`**.

---

## 🗄 Database Schema

### `users`
| Field       | Type                    | Notes            |
|-------------|-------------------------|------------------|
| id          | INT AUTO_INCREMENT PK   |                  |
| name        | VARCHAR(100)            |                  |
| email       | VARCHAR(150) UNIQUE     |                  |
| password    | VARCHAR(255)            | bcrypt hashed    |
| role        | ENUM('user','worker')   |                  |
| is_verified | TINYINT(1)              | set by admin     |
| Profession  | VARCHAR(100)            | workers only     |
| mobile      | VARCHAR(30)             | workers only     |
| address     | VARCHAR(255)            | workers only     |
| aadhar_file | VARCHAR(255)            | path in uploads/ |
| pan_file    | VARCHAR(255)            | path in uploads/ |
| description | TEXT                    | workers only     |

### `likes`
| Field      | Type                   |
|------------|------------------------|
| id         | INT AUTO_INCREMENT PK  |
| user_id    | INT (FK → users.id)    |
| worker_id  | INT (FK → users.id)    |
| type       | ENUM('like','dislike') |
| created_at | TIMESTAMP              |

### `comments`
| Field      | Type                  |
|------------|-----------------------|
| id         | INT AUTO_INCREMENT PK |
| user_id    | INT (FK → users.id)   |
| worker_id  | INT (FK → users.id)   |
| comment    | TEXT                  |
| created_at | TIMESTAMP             |

### `complaints`
| Field      | Type                    | Notes                 |
|------------|-------------------------|-----------------------|
| id         | INT AUTO_INCREMENT PK   |                       |
| user_id    | INT (FK → users.id)     |                       |
| worker_id  | INT (FK → users.id)     |                       |
| message    | TEXT                    |                       |
| status     | ENUM('open','resolved') | default open          |
| created_at | TIMESTAMP               |                       |

### `skills`
| Field      | Type                  | Notes                 |
|------------|-----------------------|-----------------------|
| id         | INT AUTO_INCREMENT PK |                       |
| worker_id  | INT (FK → users.id)   |                       |
| skill      | VARCHAR(100)          | UNIQUE per worker     |

### `bookings`
| Field        | Type                                                    | Notes              |
|--------------|---------------------------------------------------------|--------------------|
| id           | INT AUTO_INCREMENT PK                                   |                    |
| user_id      | INT (FK → users.id)                                     |                    |
| worker_id    | INT (FK → users.id)                                     |                    |
| booking_date | DATE                                                    |                    |
| booking_time | TIME                                                    |                    |
| note         | TEXT                                                    | optional           |
| status       | ENUM('pending','accepted','rejected','completed','cancelled') | default pending |
| created_at   | TIMESTAMP                                               |                    |

### `ratings`
| Field      | Type                  | Notes                     |
|------------|-----------------------|---------------------------|
| id         | INT AUTO_INCREMENT PK |                           |
| booking_id | INT (FK → bookings.id)| UNIQUE — one rating/booking |
| user_id    | INT (FK → users.id)   |                           |
| worker_id  | INT (FK → users.id)   |                           |
| rating     | TINYINT (1–5)         |                           |
| review     | TEXT                  | optional                  |
| created_at | TIMESTAMP             |                           |

### `admin`
| Field    | Type                  |
|----------|-----------------------|
| id       | INT AUTO_INCREMENT PK |
| username | VARCHAR(100) UNIQUE   |
| password | VARCHAR(255)          |

### Step 4 — Configure connection
Edit `includes/config.php`:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'worker_db');
define('DB_USER', 'root');
define('DB_PASS', '');          // update if your MySQL has a password
define('BASE_URL', '/DBMS2/'); // update to match your htdocs folder name
```

### Step 5 — Run
```
http://localhost/DBMS2/
```

---

## 🧪 Test Workflow

1. Register a **worker** at `register.php` (fill profession, mobile, address, upload Aadhar & PAN)
2. Register a **user** at `register.php`
3. Login as **admin** at `admin_login.php` → verify the worker
4. Login as the **user** at `login.php` → browse workers, view profile, like/comment
5. Open a worker profile → click **Book This Worker** → fill date, time, note
6. View and cancel bookings at `booking_history.php`
7. After a booking is marked **completed**, rate the worker from the history page
8. Login as the **worker** at `login.php` → check `notifications.php` for feedback
9. Users can file complaints on a worker profile → admin resolves via **Complaints** tab

---

## 🔧 Troubleshooting

| Problem | Fix |
|---|---|
| White page / DB error | Check `includes/config.php` credentials |
| Workers not on homepage | Ensure `role='worker'` and `is_verified=1` |
| "Worker not found" | Confirm the `id` exists in `users` |
| Uploaded files not showing | Ensure `uploads/` folder exists and is writable |
| Admin login fails | Insert a row into the `admin` table manually via phpMyAdmin |

---

## 🔮 Future Improvements

- Google Maps integration for worker location
- OTP verification on registration
- Online payment gateway
- Live chat between users and workers
- AI-based worker recommendations
- Push notifications for booking requests
