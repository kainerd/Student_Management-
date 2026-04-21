<?php
require_once 'includes/config.php';
$active_page = 'dashboard';
$conn = getConnection();

// Stats
$total_students = $conn->query("SELECT COUNT(*) as c FROM students")->fetch_assoc()['c'];
$active_students = $conn->query("SELECT COUNT(*) as c FROM students WHERE status='active'")->fetch_assoc()['c'];
$total_subjects = $conn->query("SELECT COUNT(*) as c FROM subjects")->fetch_assoc()['c'];
$total_grades = $conn->query("SELECT COUNT(*) as c FROM grades")->fetch_assoc()['c'];
$avg_grade_row = $conn->query("SELECT AVG(grade) as avg FROM grades")->fetch_assoc();
$avg_grade = $avg_grade_row['avg'] ? number_format($avg_grade_row['avg'], 1) : '0.0';

// Recent Students
$recent_students = $conn->query("SELECT * FROM students ORDER BY created_at DESC LIMIT 10");

// Grade Distribution
$grade_dist = [
    'A' => ['range' => '90-100', 'count' => 0],
    'B' => ['range' => '80-89', 'count' => 0],
    'C' => ['range' => '70-79', 'count' => 0],
    'D' => ['range' => '< 70',  'count' => 0],
];
$grade_counts = $conn->query("
    SELECT 
        SUM(CASE WHEN grade >= 90 THEN 1 ELSE 0 END) as a,
        SUM(CASE WHEN grade >= 80 AND grade < 90 THEN 1 ELSE 0 END) as b,
        SUM(CASE WHEN grade >= 70 AND grade < 80 THEN 1 ELSE 0 END) as c,
        SUM(CASE WHEN grade < 70 THEN 1 ELSE 0 END) as d
    FROM grades
")->fetch_assoc();
if ($total_grades > 0) {
    $grade_dist['A']['count'] = (int)$grade_counts['a'];
    $grade_dist['B']['count'] = (int)$grade_counts['b'];
    $grade_dist['C']['count'] = (int)$grade_counts['c'];
    $grade_dist['D']['count'] = (int)$grade_counts['d'];
}

include 'includes/header.php';
?>

<div class="page-header">
    <div>
        <h1>Dashboard</h1>
        <p>Overview of your student management system</p>
    </div>
    <a href="pages/students.php?action=add" class="btn btn-primary">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
        Add Student
    </a>
</div>

<!-- Stats Cards -->
<div class="stats-grid">
    <div class="stat-card orange">
        <div class="stat-label">Total Students</div>
        <div class="stat-value"><?= $total_students ?></div>
        <div class="stat-sub"><?= $active_students ?> currently active</div>
    </div>
    <div class="stat-card blue">
        <div class="stat-label">Subjects Offered</div>
        <div class="stat-value"><?= $total_subjects ?></div>
        <div class="stat-sub">Across all departments</div>
    </div>
    <div class="stat-card green">
        <div class="stat-label">Grade Records</div>
        <div class="stat-value"><?= $total_grades ?></div>
        <div class="stat-sub">Total enrolled grades</div>
    </div>
    <div class="stat-card orange2">
        <div class="stat-label">Average Grade</div>
        <div class="stat-value"><?= $avg_grade ?></div>
        <div class="stat-sub">Across all subjects</div>
    </div>
</div>

<!-- Recent Students + Grade Distribution -->
<div class="two-col">
    <!-- Recent Students -->
    <div class="card">
        <div class="card-header">
            <h2>Recent Students</h2>
            <a href="pages/students.php" class="btn btn-secondary btn-sm">View All</a>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Student No.</th>
                        <th>Name</th>
                        <th>Course</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($recent_students->num_rows === 0): ?>
                    <tr><td colspan="4"><div class="empty-state"><p>No students yet</p></div></td></tr>
                    <?php else: ?>
                    <?php while ($s = $recent_students->fetch_assoc()): ?>
                    <tr>
                        <td class="td-light"><?= htmlspecialchars($s['student_no']) ?></td>
                        <td class="td-name"><?= htmlspecialchars($s['name']) ?></td>
                        <td class="td-light"><?= htmlspecialchars($s['course']) ?></td>
                        <td><span class="badge badge-<?= $s['status'] ?>"><?= strtoupper($s['status']) ?></span></td>
                    </tr>
                    <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Grade Distribution -->
    <div class="card">
        <div class="card-header">
            <h2>Grade Distribution</h2>
            <a href="pages/grades.php" class="btn btn-secondary btn-sm">View Grades</a>
        </div>

        <?php
        $bar_colors = ['A'=>'bar-a','B'=>'bar-b','C'=>'bar-c','D'=>'bar-d'];
        foreach ($grade_dist as $letter => $data):
            $pct = $total_grades > 0 ? round(($data['count'] / $total_grades) * 100) : 0;
        ?>
        <div class="grade-row">
            <div class="grade-row-header">
                <span class="grade-label"><?= $letter ?> (<?= $data['range'] ?>)</span>
                <span class="grade-count"><?= $data['count'] ?> (<?= $pct ?>%)</span>
            </div>
            <div class="grade-bar-bg">
                <div class="grade-bar <?= $bar_colors[$letter] ?>" style="width:<?= $pct ?>%"></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php
$conn->close();
include 'includes/footer.php';
?>
