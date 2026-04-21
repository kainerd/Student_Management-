<?php
require_once '../includes/config.php';
$active_page = 'grades';
$base = '../';
$conn = getConnection();

$message = ''; $message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add' || $action === 'edit') {
        $student_id  = (int)$_POST['student_id'];
        $subject_id  = (int)$_POST['subject_id'];
        $grade       = (float)$_POST['grade'];
        $semester    = trim($_POST['semester']);
        $school_year = trim($_POST['school_year']);

        if (!$student_id || !$subject_id || $grade < 0 || $grade > 100) {
            $message = 'Please fill all fields. Grade must be 0-100.';
            $message_type = 'error';
        } else {
            if ($action === 'add') {
                $stmt = $conn->prepare("INSERT INTO grades (student_id, subject_id, grade, semester, school_year) VALUES (?,?,?,?,?)");
                $stmt->bind_param("iidss", $student_id, $subject_id, $grade, $semester, $school_year);
                if ($stmt->execute()) { header("Location: grades.php?success=Grade+added!"); exit; }
                else { $message = 'Error adding grade.'; $message_type = 'error'; }
            } else {
                $id = (int)$_POST['id'];
                $stmt = $conn->prepare("UPDATE grades SET student_id=?, subject_id=?, grade=?, semester=?, school_year=? WHERE id=?");
                $stmt->bind_param("iidssi", $student_id, $subject_id, $grade, $semester, $school_year, $id);
                $stmt->execute();
                header("Location: grades.php?success=Grade+updated!"); exit;
            }
        }
    }

    if ($action === 'delete') {
        $id = (int)$_POST['id'];
        $conn->query("DELETE FROM grades WHERE id=$id");
        header("Location: grades.php?success=Grade+deleted."); exit;
    }
}

$grades = $conn->query("
    SELECT g.*, s.name as student_name, s.student_no, sub.subject_name, sub.subject_code
    FROM grades g
    JOIN students s ON g.student_id = s.id
    JOIN subjects sub ON g.subject_id = sub.id
    ORDER BY g.created_at DESC
");

$students = $conn->query("SELECT id, student_no, name FROM students ORDER BY name");
$subjects = $conn->query("SELECT id, subject_code, subject_name FROM subjects ORDER BY subject_code");

include '../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1>Grades</h1>
        <p>Manage student grades and records</p>
    </div>
    <button class="btn btn-primary" onclick="document.getElementById('addModal').classList.add('open')">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
        Add Grade
    </button>
</div>

<?php if ($message): ?>
<div class="alert alert-<?= $message_type ?>"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<div class="card">
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Student No.</th>
                    <th>Student Name</th>
                    <th>Subject</th>
                    <th>Grade</th>
                    <th>Letter</th>
                    <th>Semester</th>
                    <th>School Year</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($grades->num_rows === 0): ?>
                <tr><td colspan="8"><div class="empty-state"><p>No grade records yet.</p></div></td></tr>
                <?php else: ?>
                <?php while ($g = $grades->fetch_assoc()):
                    $letter = $g['grade'] >= 90 ? 'A' : ($g['grade'] >= 80 ? 'B' : ($g['grade'] >= 70 ? 'C' : 'D'));
                    $badge_color = $letter === 'A' ? 'var(--green)' : ($letter === 'B' ? 'var(--blue)' : ($letter === 'C' ? 'var(--orange)' : 'var(--red)'));
                ?>
                <tr>
                    <td class="td-light"><?= htmlspecialchars($g['student_no']) ?></td>
                    <td class="td-name"><?= htmlspecialchars($g['student_name']) ?></td>
                    <td><?= htmlspecialchars($g['subject_code']) ?> - <?= htmlspecialchars($g['subject_name']) ?></td>
                    <td><strong><?= number_format($g['grade'], 1) ?></strong></td>
                    <td><span class="badge" style="background:<?= $badge_color ?>22;color:<?= $badge_color ?>"><?= $letter ?></span></td>
                    <td class="td-light"><?= htmlspecialchars($g['semester']) ?></td>
                    <td class="td-light"><?= htmlspecialchars($g['school_year']) ?></td>
                    <td>
                        <div class="action-btns">
                            <button class="btn btn-secondary btn-sm" onclick="openEdit(<?= htmlspecialchars(json_encode($g)) ?>)">Edit</button>
                            <form method="POST" style="display:inline" onsubmit="return confirm('Delete this grade?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $g['id'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ADD MODAL -->
<div class="modal-overlay" id="addModal">
    <div class="modal">
        <div class="modal-header">
            <h3>Add Grade</h3>
            <button class="modal-close" onclick="document.getElementById('addModal').classList.remove('open')">&times;</button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="form-grid">
                <div class="form-group full">
                    <label>Student *</label>
                    <select name="student_id" required>
                        <option value="">-- Select Student --</option>
                        <?php $students->data_seek(0); while ($s = $students->fetch_assoc()): ?>
                        <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['student_no'] . ' - ' . $s['name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group full">
                    <label>Subject *</label>
                    <select name="subject_id" required>
                        <option value="">-- Select Subject --</option>
                        <?php $subjects->data_seek(0); while ($s = $subjects->fetch_assoc()): ?>
                        <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['subject_code'] . ' - ' . $s['subject_name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Grade (0-100) *</label>
                    <input type="number" name="grade" min="0" max="100" step="0.01" required placeholder="e.g. 88.5">
                </div>
                <div class="form-group">
                    <label>Semester</label>
                    <select name="semester">
                        <option>1st Sem</option>
                        <option>2nd Sem</option>
                        <option>Summer</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>School Year</label>
                    <input type="text" name="school_year" value="2024-2025" placeholder="2024-2025">
                </div>
            </div>
            <div class="form-actions" style="margin-top:20px">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('addModal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Grade</button>
            </div>
        </form>
    </div>
</div>

<!-- EDIT MODAL -->
<div class="modal-overlay" id="editModal">
    <div class="modal">
        <div class="modal-header">
            <h3>Edit Grade</h3>
            <button class="modal-close" onclick="document.getElementById('editModal').classList.remove('open')">&times;</button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="edit_id">
            <div class="form-grid">
                <div class="form-group full">
                    <label>Student *</label>
                    <select name="student_id" id="edit_student_id" required>
                        <?php $students->data_seek(0); while ($s = $students->fetch_assoc()): ?>
                        <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['student_no'] . ' - ' . $s['name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group full">
                    <label>Subject *</label>
                    <select name="subject_id" id="edit_subject_id" required>
                        <?php $subjects->data_seek(0); while ($s = $subjects->fetch_assoc()): ?>
                        <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['subject_code'] . ' - ' . $s['subject_name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Grade *</label>
                    <input type="number" name="grade" id="edit_grade" min="0" max="100" step="0.01" required>
                </div>
                <div class="form-group">
                    <label>Semester</label>
                    <select name="semester" id="edit_semester">
                        <option>1st Sem</option><option>2nd Sem</option><option>Summer</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>School Year</label>
                    <input type="text" name="school_year" id="edit_school_year">
                </div>
            </div>
            <div class="form-actions" style="margin-top:20px">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('editModal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEdit(g) {
    document.getElementById('edit_id').value = g.id;
    document.getElementById('edit_student_id').value = g.student_id;
    document.getElementById('edit_subject_id').value = g.subject_id;
    document.getElementById('edit_grade').value = g.grade;
    document.getElementById('edit_semester').value = g.semester;
    document.getElementById('edit_school_year').value = g.school_year;
    document.getElementById('editModal').classList.add('open');
}
document.querySelectorAll('.modal-overlay').forEach(o => o.addEventListener('click', e => { if (e.target === o) o.classList.remove('open'); }));
</script>

<?php $conn->close(); include '../includes/footer.php'; ?>
