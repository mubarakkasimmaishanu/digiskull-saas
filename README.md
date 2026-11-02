<div align="center">
  <h1>🎓 Digiskul SaaS — Multi-Tenant School ERP & Assessment Platform</h1>
  <p><strong>A secure, multi-tenant institutional school management platform engineered with Laravel, PHP, and Tailwind CSS.</strong></p>

  <p align="center">
    <img src="https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" />
    <img src="https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=for-the-badge&logo=php&logoColor=white" />
    <img src="https://img.shields.io/badge/MySQL-8.0%2B-4479A1?style=for-the-badge&logo=mysql&logoColor=white" />
    <img src="https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white" />
    <img src="https://img.shields.io/badge/License-MIT-green?style=for-the-badge" />
    <img src="https://img.shields.io/badge/Release-v1.0.0-blue?style=for-the-badge" />
  </p>
</div>

---

## 📖 Overview

**Digiskul SaaS** is a comprehensive multi-tenant school enterprise resource planning (ERP) system designed to streamline institutional administration, academic record-keeping, attendance tracking, and financial ledgers for primary and secondary educational institutions.

---

## 🚀 Key Features

* **🏢 Tenant Isolation Architecture:** Multi-tenancy support ensuring distinct database scopes and customized subdomains/branding for each school.
* **📊 Academic Assessment & Gradebooks:** Automated grading, cumulative GPA computation, term assessment recording, and printable report cards.
* **📅 Attendance Tracking Engine:** Daily student and staff attendance monitoring with real-time discrepancy alerts.
* **💳 Tuition & Fee Ledgers:** Student invoice generation, payment gateway integration, outstanding balance tracking, and automated receipts.
* **🔐 Multi-Tier Role Governance:** Strict RBAC separating Super Admins, School Principals, Teachers, Accountants, and Guardians.
* **📱 Student & Parent Portal:** Self-service portal allowing parents to inspect academic performance, attendance records, and fee payment histories.

---

## 🛠️ Tech Stack & Requirements

* **Framework:** Laravel 12.x
* **Language:** PHP 8.2+
* **Database:** MySQL 8.0+ / MariaDB
* **Frontend:** Blade, Tailwind CSS, Alpine.js
* **Authentication:** Multi-guard session authentication with 2FA verification support

---

## ⚡ Local Setup

### 1. Clone the repository
```bash
git clone https://github.com/mubarakkasimmaishanu/digiskull-saas.git
cd digiskull-saas
```

### 2. Install PHP & Node Dependencies
```bash
composer install
npm install
```

### 3. Environment & Database Configuration
```bash
cp .env.example .env
php artisan key:generate
```

Configure your database credentials in `.env`, then run migrations and seeds:
```bash
php artisan migrate --seed
```

### 4. Run Development Server
```bash
php artisan serve
npm run dev
```

---

## 📄 License

This project is licensed under the **MIT License** — see the [LICENSE](LICENSE) file for details.

---

<p align="center">
  Authored & Maintained by <a href="https://github.com/mubarakkasimmaishanu"><strong>Mubarak Kasim Mashanu</strong></a>
</p>
