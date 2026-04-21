<?php
require_once '../includes/config.php';
$active_page = 'reports';
$base = '../';
$conn = getConnection();

// Stats
$total_students = $conn->query("SELECT COUNT(*) c FROM students")->fetch_assoc()['c'];
$active_students = $conn->query("SELECT COUNT(*) c FROM students WHERE status='active'")->fetch_assoc()['c'];
$graduated = $conn->query("SELECT COUNT(*) c FROM students WHERE status='graduated'")->fetch_assoc()['c'];
$total_grades = $conn->query("SELECT COUNT(*) c FROM grades")->fetch_assoc()['c'];
$avg_grade = $conn->query("SELECT AVG(grade) avg FROM grades")->fetch_assoc()['avg'];
$highest = $conn->query("SELECT MAX(grade) m FROM grades")->fetch_assoc()['m'];
$lowest = $conn->query("SELECT MIN(grade) m FROM grades")->fetch_assoc()['m'];
$top_students = $conn->query("
    SELECT s.name, s.student_no, s.course, AVG(g.grade) as avg_grade, COUNT(g.id) as total_subjects
    FROM students s JOIN grades g ON s.id = g.student_id
    GROUP BY s.id ORDER BY avg_grade DESC LIMIT 5
");
$subject_avg = $conn->query("
    SELECT sub.subject_name, sub.subject_code, AVG(g.grade) avg, COUNT(g.id) enrolled
    FROM subjects sub JOIN grades g ON sub.id = g.subject_id
    GROUP BY sub.id ORDER BY avg DESC
");
$course_dist = $conn->query("SELECT course, COUNT(*) c FROM students GROUP BY course ORDER BY c DESC");

include '../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1>Reports</h1>
        <p>Analytics and overview of your institution</p>
    </div>
</div>

<!-- Summary Cards -->
<div class="stats-grid">
    <div class="stat-card orange"><div class="stat-label">Total Students</div><div class="stat-value"><?= $total_students ?></div><div class="stat-sub"><?= $active_students ?> active · <?= $graduated ?> graduated</div></div>
    <div class="stat-card blue"><div class="stat-label">Average Grade</div><div class="stat-value"><?= $avg_grade ? number_format($avg_grade,1) : '—' ?></div><div class="stat-sub">Across all subjects</div></div>
    <div class="stat-card green"><div class="stat-label">Highest Grade</div><div class="stat-value"><?= $highest ? number_format($highest,1) : '—' ?></div><div class="stat-sub">Best performance</div></div>
    <div class="stat-card orange2"><div class="stat-label">Lowest Grade</div><div class="stat-value"><?= $lowest ? number_format($lowest,1) : '—' ?></div><div class="stat-sub">Needs attention</div></div>
</div>

<div class="two-col">
    <!-- Top Students -->
    <div class="card">
        <div class="card-header"><h2>Top Performing Students</h2></div>
        <?php if ($top_students->num_rows === 0): ?>
        <div class="empty-state"><p>No grade data available.</p></div>
        <?php else: ?>
        <div class="table-wrapper">
            <table>
                <thead><tr><th>#</th><th>Student</th><th>Course</th><th>Avg Grade</th></tr></thead>
                <tbody>
                    <?php $rank = 1; while ($s = $top_students->fetch_assoc()): ?>
                    <tr>
                        <td><strong style="color:var(--orange)"><?= $rank++ ?></strong></td>
                        <td>
                            <div class="td-name"><?= htmlspecialchars($s['name']) ?></div>
                            <div style="font-size:12px;color:var(--gray-400)"><?= htmlspecialchars($s['student_no']) ?></div>
                        </td>
                        <td class="td-light"><?= htmlspecialchars($s['course']) ?></td>
                        <td>
                            <?php $a = (float)$s['avg_grade']; $color = $a>=90?'var(--green)':($a>=80?'var(--blue)':($a>=70?'var(--orange)':'var(--red)')); ?>
                            <strong style="color:<?= $color ?>"><?= number_format($a, 1) ?></strong>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <!-- Subject Performance -->
    <div class="card">
        <div class="card-header"><h2>Subject Performance</h2></div>
        <?php if ($subject_avg->num_rows === 0): ?>
        <div class="empty-state"><p>No grade data available.</p></div>
        <?php else: ?>
        <div class="table-wrapper">
            <table>
                <thead><tr><th>Subject</th><th>Enrolled</th><th>Avg Grade</th></tr></thead>
                <tbody>
                    <?php while ($s = $subject_avg->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <div class="td-name"><?= htmlspecialchars($s['subject_name']) ?></div>
                            <div style="font-size:12px;color:var(--gray-400)"><?= htmlspecialchars($s['subject_code']) ?></div>
                        </td>
                        <td><?= $s['enrolled'] ?></td>
                        <td>
                            <?php $a = (float)$s['avg']; $color = $a>=90?'var(--green)':($a>=80?'var(--blue)':($a>=70?'var(--orange)':'var(--red)')); ?>
                            <span class="badge" style="background:<?= $color ?>22;color:<?= $color ?>"><?= number_format($a,1) ?></span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Course Distribution -->
<div class="card">
    <div class="card-header"><h2>Student Distribution by Course</h2></div>
    <?php if ($course_dist->num_rows === 0): ?>
    <div class="empty-state"><p>No data available.</p></div>
    <?php else:
        $total = $total_students ?: 1;
        $colors = ['var(--orange)','var(--blue)','var(--green)','#9B59B6','#E84A3A'];
        $ci = 0;
        while ($c = $course_dist->fetch_assoc()):
            $pct = round(($c['c'] / $total) * 100);
    ?>
    <div class="grade-row">
        <div class="grade-row-header">
            <span class="grade-label"><?= htmlspecialchars($c['course']) ?></span>
            <span class="grade-count"><?= $c['c'] ?> students (<?= $pct ?>%)</span>
        </div>
        <div class="grade-bar-bg">
            <div class="grade-bar" style="width:<?= $pct ?>%;background:<?= $colors[$ci % count($colors)] ?>"></div>
        </div>
    </div>
    <?php $ci++; endwhile; endif; ?>
</div>

<?php $conn->close(); include '../includes/footer.php'; ?>
