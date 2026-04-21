<?php
require_once '../includes/config.php';
$active_page = 'subjects';
$base = '../';
$conn = getConnection();

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add' || $action === 'edit') {
        $subject_code = trim($_POST['subject_code']);
        $subject_name = trim($_POST['subject_name']);
        $department   = trim($_POST['department']);
        $units        = (int)$_POST['units'];
        $description  = trim($_POST['description']);

        if (empty($subject_code) || empty($subject_name)) {
            $message = 'Subject code and name are required.';
            $message_type = 'error';
        } else {
            if ($action === 'add') {
                $stmt = $conn->prepare("INSERT INTO subjects (subject_code, subject_name, department, units, description) VALUES (?,?,?,?,?)");
                $stmt->bind_param("sssIs", $subject_code, $subject_name, $department, $units, $description);
                if ($stmt->execute()) { header("Location: subjects.php?success=Subject+added!"); exit; }
                else { $message = 'Subject code already exists.'; $message_type = 'error'; }
            } else {
                $id = (int)$_POST['id'];
                $stmt = $conn->prepare("UPDATE subjects SET subject_code=?, subject_name=?, department=?, units=?, description=? WHERE id=?");
                $stmt->bind_param("sssIsi", $subject_code, $subject_name, $department, $units, $description, $id);
                $stmt->execute();
                header("Location: subjects.php?success=Subject+updated!"); exit;
            }
        }
    }

    if ($action === 'delete') {
        $id = (int)$_POST['id'];
        $conn->query("DELETE FROM subjects WHERE id=$id");
        header("Location: subjects.php?success=Subject+deleted."); exit;
    }
}

$subjects = $conn->query("SELECT * FROM subjects ORDER BY subject_code");
include '../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1>Subjects</h1>
        <p>Manage course subjects and offerings</p>
    </div>
    <button class="btn btn-primary" onclick="document.getElementById('addModal').classList.add('open')">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
        Add Subject
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
                    <th>Subject Code</th>
                    <th>Subject Name</th>
                    <th>Department</th>
                    <th>Units</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($subjects->num_rows === 0): ?>
                <tr><td colspan="6"><div class="empty-state"><p>No subjects added yet.</p></div></td></tr>
                <?php else: ?>
                <?php while ($s = $subjects->fetch_assoc()): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($s['subject_code']) ?></strong></td>
                    <td class="td-name"><?= htmlspecialchars($s['subject_name']) ?></td>
                    <td class="td-light"><?= htmlspecialchars($s['department']) ?></td>
                    <td><?= $s['units'] ?> units</td>
                    <td class="td-light" style="max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= htmlspecialchars($s['description']) ?></td>
                    <td>
                        <div class="action-btns">
                            <button class="btn btn-secondary btn-sm" onclick="openEdit(<?= htmlspecialchars(json_encode($s)) ?>)">Edit</button>
                            <form method="POST" style="display:inline" onsubmit="return confirm('Delete this subject?')">
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
            <h3>Add New Subject</h3>
            <button class="modal-close" onclick="document.getElementById('addModal').classList.remove('open')">&times;</button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="form-grid">
                <div class="form-group">
                    <label>Subject Code *</label>
                    <input type="text" name="subject_code" required placeholder="e.g. CS101">
                </div>
                <div class="form-group">
                    <label>Subject Name *</label>
                    <input type="text" name="subject_name" required placeholder="e.g. Introduction to Programming">
                </div>
                <div class="form-group">
                    <label>Department</label>
                    <input type="text" name="department" placeholder="e.g. Computer Science">
                </div>
                <div class="form-group">
                    <label>Units</label>
                    <select name="units">
                        <option value="1">1 unit</option>
                        <option value="2">2 units</option>
                        <option value="3" selected>3 units</option>
                        <option value="4">4 units</option>
                        <option value="5">5 units</option>
                    </select>
                </div>
                <div class="form-group full">
                    <label>Description</label>
                    <textarea name="description" rows="3" placeholder="Brief description..."></textarea>
                </div>
            </div>
            <div class="form-actions" style="margin-top:20px">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('addModal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Subject</button>
            </div>
        </form>
    </div>
</div>

<!-- EDIT MODAL -->
<div class="modal-overlay" id="editModal">
    <div class="modal">
        <div class="modal-header">
            <h3>Edit Subject</h3>
            <button class="modal-close" onclick="document.getElementById('editModal').classList.remove('open')">&times;</button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="edit_id">
            <div class="form-grid">
                <div class="form-group">
                    <label>Subject Code *</label>
                    <input type="text" name="subject_code" id="edit_subject_code" required>
                </div>
                <div class="form-group">
                    <label>Subject Name *</label>
                    <input type="text" name="subject_name" id="edit_subject_name" required>
                </div>
                <div class="form-group">
                    <label>Department</label>
                    <input type="text" name="department" id="edit_department">
                </div>
                <div class="form-group">
                    <label>Units</label>
                    <select name="units" id="edit_units">
                        <option value="1">1 unit</option>
                        <option value="2">2 units</option>
                        <option value="3">3 units</option>
                        <option value="4">4 units</option>
                        <option value="5">5 units</option>
                    </select>
                </div>
                <div class="form-group full">
                    <label>Description</label>
                    <textarea name="description" id="edit_description" rows="3"></textarea>
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
    document.getElementById('edit_subject_code').value = s.subject_code;
    document.getElementById('edit_subject_name').value = s.subject_name;
    document.getElementById('edit_department').value = s.department;
    document.getElementById('edit_units').value = s.units;
    document.getElementById('edit_description').value = s.description;
    document.getElementById('editModal').classList.add('open');
}
document.querySelectorAll('.modal-overlay').forEach(o => o.addEventListener('click', e => { if (e.target === o) o.classList.remove('open'); }));
</script>

<?php $conn->close(); include '../includes/footer.php'; ?>
