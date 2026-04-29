<?php
require_once '../includes/config.php';
require_once '../includes/db.php';

check_login();

// Handle Add Enrollment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_enrollment'])) {
    $student_id = (int)$_POST['student_id'];
    $class_id = (int)$_POST['class_id'];

    if ($student_id > 0 && $class_id > 0) {
        try {
            $stmt = $pdo->prepare("INSERT INTO student_class_enrollment (student_id, class_id, enrollment_date) VALUES (?, ?, CURDATE())");
            $stmt->execute([$student_id, $class_id]);
            // Update enrolled_count
            $pdo->exec("UPDATE classes SET enrolled_count = (SELECT COUNT(*) FROM student_class_enrollment WHERE class_id = classes.id AND status = 'enrolled') WHERE id = $class_id");
            set_flash_message('success', 'Student enrolled successfully.');
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                set_flash_message('error', 'Student is already enrolled in this class.');
            } else {
                set_flash_message('error', 'Error enrolling student: ' . $e->getMessage());
            }
        }
    } else {
        set_flash_message('error', 'Please select both a student and a class.');
    }
    header("Location: enrollments.php");
    exit();
}

// Handle Delete/Unenroll Enrollment
if (isset($_GET['delete']) && is_admin()) {
    $id = (int)$_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM student_class_enrollment WHERE id = ?");
        $stmt->execute([$id]);
        set_flash_message('success', 'Enrollment removed successfully.');
    } catch (PDOException $e) {
        set_flash_message('error', 'Error removing enrollment.');
    }
    header("Location: enrollments.php");
    exit();
}

// Pagination
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 15;
$offset = ($page - 1) * $limit;

// Total count
$totalStmt = $pdo->prepare("SELECT COUNT(*) FROM student_class_enrollment");
$totalStmt->execute();
$totalEnrollments = $totalStmt->fetchColumn();
$totalPages = ceil($totalEnrollments / $limit);

// Fetch enrollments with INNER JOIN
$stmt = $pdo->prepare("
    SELECT sce.id, sce.enrollment_date, sce.status as enrollment_status,
           s.first_name, s.last_name, s.class as student_class,
           co.course_code, co.course_name, cl.section
    FROM student_class_enrollment sce
    INNER JOIN students s ON sce.student_id = s.id
    INNER JOIN classes cl ON sce.class_id = cl.id
    INNER JOIN courses co ON cl.course_id = co.id
    ORDER BY sce.enrollment_date DESC
    LIMIT $limit OFFSET $offset
");
$stmt->execute();
$enrollments = $stmt->fetchAll();

// Fetch lists for modal
$students = $pdo->query("SELECT id, first_name, last_name, email FROM students ORDER BY first_name")->fetchAll();
$classesForModal = $pdo->query("
    SELECT cl.id, CONCAT(co.course_code, ' - ', co.course_name, ' (Section ', cl.section, ')') as name
    FROM classes cl
    JOIN courses co ON cl.course_id = co.id
    WHERE cl.is_active = 1
    ORDER BY co.course_code
")->fetchAll();

require_once '../includes/header.php';
?>

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-bold text-[#1a237e]">Class Enrollments</h1>
        <p class="text-sm text-gray-500">Manage student registrations in class sections</p>
    </div>
    <?php if(is_admin()): ?>
    <button onclick="document.getElementById('addModal').classList.remove('hidden')" class="bg-[#1a237e] text-white px-4 py-2 rounded-lg font-semibold hover:bg-[#000666] transition flex items-center gap-2">
        <span class="material-symbols-outlined text-sm">how_to_reg</span> New Enrollment
    </button>
    <?php endif; ?>
</div>

<div class="glass-card rounded-xl shadow-sm overflow-hidden mb-6">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                <th class="px-6 py-4">Student</th>
                <th class="px-6 py-4">Course / Section</th>
                <th class="px-6 py-4">Student Class</th>
                <th class="px-6 py-4">Status</th>
                <th class="px-6 py-4">Enrollment Date</th>
                <?php if(is_admin()): ?><th class="px-6 py-4 text-right">Actions</th><?php endif; ?>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            <?php if(empty($enrollments)): ?>
                <tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">No enrollments found.</td></tr>
            <?php else: ?>
                <?php foreach($enrollments as $enr): ?>
                <tr class="hover:bg-indigo-50/30 transition-colors">
                    <td class="px-6 py-4 font-semibold text-[#1a237e]"><?php echo htmlspecialchars($enr['first_name'] . ' ' . $enr['last_name']); ?></td>
                    <td class="px-6 py-4 text-gray-800">
                        <span class="font-bold mr-1"><?php echo htmlspecialchars($enr['course_code']); ?></span>
                        <?php echo htmlspecialchars($enr['course_name'] . ' (' . $enr['section'] . ')'); ?>
                    </td>
                    <td class="px-6 py-4">
                        <span class="bg-indigo-100 text-indigo-800 px-3 py-1 rounded-full text-xs font-bold"><?php echo htmlspecialchars($enr['student_class'] ?? 'N/A'); ?></span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 rounded-full text-xs font-bold
                            <?php echo $enr['enrollment_status'] === 'enrolled' ? 'bg-green-100 text-green-800' :
                                     ($enr['enrollment_status'] === 'completed' ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800'); ?>">
                            <?php echo htmlspecialchars(ucfirst($enr['enrollment_status'])); ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 text-gray-500 text-sm">
                        <?php echo date('d-m-Y', strtotime($enr['enrollment_date'])); ?>
                    </td>
                    <?php if(is_admin()): ?>
                    <td class="px-6 py-4 text-right">
                        <a href="?delete=<?php echo $enr['id']; ?>" onclick="return confirm('Are you sure you want to remove this enrollment?');" class="text-red-500 hover:text-red-700">
                            <span class="material-symbols-outlined text-sm">delete</span>
                        </a>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Pagination -->
<?php if($totalPages > 1): ?>
<div class="flex justify-center gap-2">
    <?php for($i = 1; $i <= $totalPages; $i++): ?>
        <a href="?page=<?php echo $i; ?>" class="px-4 py-2 border rounded-lg <?php echo $i === $page ? 'bg-[#1a237e] text-white' : 'bg-white text-[#1a237e] hover:bg-indigo-50'; ?>">
            <?php echo $i; ?>
        </a>
    <?php endfor; ?>
</div>
<?php endif; ?>

<!-- Add Enrollment Modal -->
<div id="addModal" class="fixed inset-0 bg-black/50 hidden z-50 flex items-center justify-center backdrop-blur-sm">
    <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-lg">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold text-[#1a237e]">Enroll Student in Class</h2>
            <button onclick="document.getElementById('addModal').classList.add('hidden')" class="text-gray-500 hover:text-gray-700"><span class="material-symbols-outlined">close</span></button>
        </div>
        <form method="POST" action="">
            <input type="hidden" name="add_enrollment" value="1">
            <div class="mb-4">
                <label class="block text-xs font-semibold text-gray-600 mb-1">Select Student</label>
                <select name="student_id" class="w-full px-3 py-2 border rounded focus:ring-1 focus:ring-[#1a237e]" required>
                    <option value="">-- Choose Student --</option>
                    <?php foreach($students as $stu): ?>
                        <option value="<?php echo $stu['id']; ?>"><?php echo htmlspecialchars($stu['first_name'] . ' ' . $stu['last_name'] . ' (' . $stu['email'] . ')'); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-6">
                <label class="block text-xs font-semibold text-gray-600 mb-1">Select Class Section</label>
                <select name="class_id" class="w-full px-3 py-2 border rounded focus:ring-1 focus:ring-[#1a237e]" required>
                    <option value="">-- Choose Class --</option>
                    <?php foreach($classesForModal as $cls): ?>
                        <option value="<?php echo $cls['id']; ?>"><?php echo htmlspecialchars($cls['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('addModal').classList.add('hidden')" class="px-4 py-2 border rounded text-gray-600 hover:bg-gray-50">Cancel</button>
                <button type="submit" class="bg-[#1a237e] text-white px-4 py-2 rounded hover:bg-[#000666]">Enroll Student</button>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
