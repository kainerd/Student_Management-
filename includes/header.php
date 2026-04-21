<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduTrack - Student Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= isset($base) ? $base : '' ?>assets/css/style.css">
</head>
<body>

<nav class="navbar">
    <div class="nav-brand">
        <div class="nav-logo">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="white">
                <path d="M12 3L1 9l11 6 9-4.91V17h2V9L12 3zM5 13.18v4L12 21l7-3.82v-4L12 17l-7-3.82z"/>
            </svg>
        </div>
        <span class="brand-name">Edu<strong>Track</strong></span>
    </div>
    <div class="nav-links">
        <a href="<?= isset($base) ? $base : '' ?>index.php" class="nav-link <?= ($active_page ?? '') == 'dashboard' ? 'active' : '' ?>">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/></svg>
            Dashboard
        </a>
        <a href="<?= isset($base) ? $base : '' ?>pages/students.php" class="nav-link <?= ($active_page ?? '') == 'students' ? 'active' : '' ?>">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
            Students
        </a>
        <a href="<?= isset($base) ? $base : '' ?>pages/subjects.php" class="nav-link <?= ($active_page ?? '') == 'subjects' ? 'active' : '' ?>">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M18 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-2 14H8v-2h8v2zm0-4H8v-2h8v2zm-3-4H8V6h5v2z"/></svg>
            Subjects
        </a>
        <a href="<?= isset($base) ? $base : '' ?>pages/grades.php" class="nav-link <?= ($active_page ?? '') == 'grades' ? 'active' : '' ?>">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M3.5 18.49l6-6.01 4 4L22 6.92l-1.41-1.41-7.09 7.97-4-4L2 16.99l1.5 1.5z"/></svg>
            Grades
        </a>
        <a href="<?= isset($base) ? $base : '' ?>pages/reports.php" class="nav-link <?= ($active_page ?? '') == 'reports' ? 'active' : '' ?>">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M5 9.2h3V19H5V9.2zM10.6 5h2.8v14h-2.8V5zm5.6 8H19v6h-2.8v-6z"/></svg>
            Reports
        </a>
    </div>
    <div class="nav-badge">XAMPP / MySQL</div>
</nav>

<div class="main-wrapper">
