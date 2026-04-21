<?php
require_once '../includes/config.php';
$active_page = 'students';
$base = '../';
$conn = getConnection();

$message = '';
$message_type = '';

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add' || $action === 'edit') {
        $student_no  = trim($_POST['student_no']);
        $name        = trim($_POST['name']);
        $email       = trim($_POST['email']);
        $course      = trim($_POST['course']);
        $department  = trim($_POST['department']);
        $year_level  = (int)$_POST['year_level'];
        $status      = $_POST['status'];

        if (empty($student_no) || empty($name) || empty($email) || empty($course)) {
            $message = 'Please fill in all required fields.';
            $message_type = 'error';
        } else {
            if ($action === 'add') {
                $stmt = $conn->prepare("INSERT INTO students (student_no, name, email, course, department, year_level, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sssssiss", $student_no, $name, $email, $course, $department, $year_level, $status);
                if ($stmt->execute()) {
                    header("Location: students.php?success=" . urlencode("Student added successfully!"));
                    exit;
                } else {
                    $message = 'Error: ' . ($conn->error ?: 'Student No. or Email already exists.');
                    $message_type = 'error';
                }
            } else {
                $id = (int)$_POST['id'];
                $stmt = $conn->prepare("UPDATE students SET student_no=?, name=?, email=?, course=?, department=?, year_level=?, status=? WHERE id=?");
                $stmt->bind_param("sssssisi", $student_no, $name, $email, $course, $department, $year_level, $status, $id);
                if ($stmt->execute()) {
                    header("Location: students.php?success=" . urlencode("Student updated successfully!"));
                    exit;
                } else {
                    $message = 'Error updating student.';
                    $message_type = 'error';
                }
            }
        }
    }

    if ($action === 'delete') {
        $id = (int)$_POST['id'];
        $conn->query("DELETE FROM students WHERE id=$id");
        header("Location: students.php?success=" . urlencode("Student deleted."));
        exit;
    }
}

// Fetch
$search = trim($_GET['search'] ?? '');
$status_filter = $_GET['status'] ?? '';
$sql = "SELECT * FROM students WHERE 1=1";
if ($search) $sql .= " AND (name LIKE '%$search%' OR student_no LIKE '%$search%' OR course LIKE '%$search%')";
if ($status_filter) $sql .= " AND status='$status_filter'";
$sql .= " ORDER BY created_at DESC";
$students = $conn->query($sql);

$action_param = $_GET['action'] ?? '';
$edit_student = null;
if ($action_param === 'edit' && isset($_GET['id'])) {
    $edit_id = (int)$_GET['id'];
    $edit_student = $conn->query("SELECT * FROM students WHERE id=$edit_id")->fetch_assoc();
}

include '../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1>Students</h1>
        <p>Manage all enrolled students</p>
    </div>
    <button class="btn btn-primary" onclick="document.getElementById('addModal').classList.add('open')">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
        Add Student
    </button>
</div>

<?php if ($message): ?>
<div class="alert alert-<?= $message_type ?>"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<div class="card">
    <!-- Search & Filter -->
    <form method="GET" class="search-bar" style="margin-bottom:20px">
        <div class="search-input-wrap">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M15.5 14h-.79l-.28-.27A6.471 6.471 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>
            <input type="text" name="search" placeholder="Search by name, student no, or course..." value="<?= htmlspecialchars($search) ?>">
        </div>
        <select name="status" style="width:160px">
            <option value="">All Status</option>
            <option value="active" <?= $status_filter==='active'?'selected':'' ?>>Active</option>
            <option value="inactive" <?= $status_filter==='inactive'?'selected':'' ?>>Inactive</option>
            <option value="graduated" <?= $status_filter==='graduated'?'selected':'' ?>>Graduated</option>
        </select>
        <button type="submit" class="btn btn-secondary">Filter</button>
        <?php if ($search || $status_filter): ?><a href="students.php" class="btn btn-secondary">Clear</a><?php endif; ?>
    </form>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Student No.</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Course</th>
                    <th>Department</th>
                    <th>Year</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($students->num_rows === 0): ?>
                <tr><td colspan="8">
                    <div class="empty-state">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="currentColor"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3z"/></svg>
                        <p>No students found.</p>
                    </div>
                </td></tr>
                <?php else: ?>
                <?php while ($s = $students->fetch_assoc()): ?>
                <tr>
                    <td class="td-light"><?= htmlspecialchars($s['student_no']) ?></td>
                    <td class="td-name"><?= htmlspecialchars($s['name']) ?></td>
                    <td class="td-light"><?= htmlspecialchars($s['email']) ?></td>
                    <td><?= htmlspecialchars($s['course']) ?></td>
                    <td class="td-light"><?= htmlspecialchars($s['department']) ?></td>
                    <td class="td-light"><?= $s['year_level'] ?></td>
                    <td><span class="badge badge-<?= $s['status'] ?>"><?= strtoupper($s['status']) ?></span></td>
                    <td>
                        <div class="action-btns">
                            <button class="btn btn-secondary btn-sm" onclick="openEdit(<?= htmlspecialchars(json_encode($s)) ?>)">Edit</button>
                            <form method="POST" style="display:inline" onsubmit="return confirm('Delete this student?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $s['id'] ?>">
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
            <h3>Add New Student</h3>
            <button class="modal-close" onclick="document.getElementById('addModal').classList.remove('open')">&times;</button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="form-grid">
                <div class="form-group">
                    <label>Student No. *</label>
                    <input type="text" name="student_no" required placeholder="e.g. 2024-0001">
                </div>
                <div class="form-group">
                    <label>Full Name *</label>
                    <input type="text" name="name" required placeholder="Full name">
                </div>
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" required placeholder="student@email.com">
                </div>
                <div class="form-group">
                    <label>Course *</label>
                    <input type="text" name="course" required placeholder="e.g. BS Computer Science">
                </div>
                <div class="form-group">
                    <label>Department</label>
                    <input type="text" name="department" placeholder="e.g. Computer Science">
                </div>
                <div class="form-group">
                    <label>Year Level</label>
                    <select name="year_level">
                        <option value="1">1st Year</option>
                        <option value="2">2nd Year</option>
                        <option value="3">3rd Year</option>
                        <option value="4">4th Year</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="graduated">Graduated</option>
                    </select>
                </div>
            </div>
            <div class="form-actions" style="margin-top:20px">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('addModal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Student</button>
            </div>
        </form>
    </div>
</div>

<!-- EDIT MODAL -->
<div class="modal-overlay" id="editModal">
    <div class="modal">
        <div class="modal-header">
            <h3>Edit Student</h3>
            <button class="modal-close" onclick="document.getElementById('editModal').classList.remove('open')">&times;</button>
        </div>
        <form method="POST" id="editForm">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="edit_id">
            <div class="form-grid">
                <div class="form-group">
                    <label>Student No. *</label>
                    <input type="text" name="student_no" id="edit_student_no" required>
                </div>
                <div class="form-group">
                    <label>Full Name *</label>
                    <input type="text" name="name" id="edit_name" required>
                </div>
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" id="edit_email" required>
                </div>
                <div class="form-group">
                    <label>Course *</label>
                    <input type="text" name="course" id="edit_course" required>
                </div>
                <div class="form-group">
                    <label>Department</label>
                    <input type="text" name="department" id="edit_department">
                </div>
                <div class="form-group">
                    <label>Year Level</label>
                    <select name="year_level" id="edit_year_level">
                        <option value="1">1st Year</option>
                        <option value="2">2nd Year</option>
                        <option value="3">3rd Year</option>
                        <option value="4">4th Year</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" id="edit_status">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="graduated">Graduated</option>
                    </select>
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
function openEdit(s) {
    document.getElementById('edit_id').value = s.id;
    document.getElementById('edit_student_no').value = s.student_no;
    document.getElementById('edit_name').value = s.name;
    document.getElementById('edit_email').value = s.email;
    document.getElementById('edit_course').value = s.course;
    document.getElementById('edit_department').value = s.department;
    document.getElementById('edit_year_level').value = s.year_level;
    document.getElementById('edit_status').value = s.status;
    document.getElementById('editModal').classList.add('open');
}
// Close modal on overlay click
document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) overlay.classList.remove('open');
    });
});
<?php if ($action_param === 'add'): ?>
document.getElementById('addModal').classList.add('open');
<?php endif; ?>
</script>

<?php
$conn->close();
include '../includes/footer.php';
?>
