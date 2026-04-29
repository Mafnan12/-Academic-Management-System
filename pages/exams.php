<?php
require_once '../includes/config.php';
require_once '../includes/db.php';

check_login();
if (!has_permission('admin') && !is_instructor()) {
    header("Location: " . BASE_URL . "/index.php");
    exit();
}

// Handle Add Exam
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_exam'])) {
    $class_id = (int)$_POST['class_id'];
    $exam_type = $_POST['exam_type'];
    $term = trim($_POST['term']);
    $exam_date = $_POST['exam_date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $total_marks = (int)$_POST['total_marks'];

    if ($class_id && !empty($exam_type)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO exams (class_id, exam_type, term, exam_date, start_time, end_time, total_marks) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$class_id, $exam_type, $term, $exam_date, $start_time, $end_time, $total_marks]);
            set_flash_message('success', 'Exam added successfully.');
        } catch (PDOException $e) {
            set_flash_message('error', 'Error adding exam: ' . $e->getMessage());
        }
    } else {
        set_flash_message('error', 'Please provide valid data.');
    }
    header("Location: exams.php");
    exit();
}

// Handle Delete Exam
if (isset($_GET['delete']) && has_permission('admin')) {
    $id = (int)$_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM exams WHERE id = ?");
        $stmt->execute([$id]);
        set_flash_message('success', 'Exam deleted successfully.');
    } catch (PDOException $e) {
        set_flash_message('error', 'Error deleting exam.');
    }
    header("Location: exams.php");
    exit();
}

// Filter by instructor if not admin
$instructor_filter = "";
if (is_instructor()) {
    $instructor_id = $pdo->query("SELECT id FROM instructors WHERE user_id = {$_SESSION['user_id']}")->fetchColumn();
    $instructor_filter = "AND c.instructor_id = $instructor_id";
}

// Search and Pagination
$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Build Query
$query = "FROM exams e
          JOIN classes c ON e.class_id = c.id
          JOIN courses co ON c.course_id = co.id
          LEFT JOIN instructors i ON c.instructor_id = i.id
          WHERE 1=1 $instructor_filter";
$params = [];
if (!empty($search)) {
    $query .= " AND (co.course_name LIKE ? OR co.course_code LIKE ? OR e.exam_type LIKE ?)";
    $searchTerm = "%$search%";
    $params = [$searchTerm, $searchTerm, $searchTerm];
}

// Total count for pagination
$totalStmt = $pdo->prepare("SELECT COUNT(*) $query");
$totalStmt->execute($params);
$totalExams = $totalStmt->fetchColumn();
$totalPages = ceil($totalExams / $limit);

// Fetch exams
$stmt = $pdo->prepare("SELECT e.*, co.course_name, co.course_code, c.section,
                       CONCAT(i.first_name, ' ', i.last_name) as instructor_name,
                       (SELECT COUNT(*) FROM exam_results r WHERE r.exam_id = e.id) as results_count
                       $query ORDER BY e.exam_date DESC LIMIT $limit OFFSET $offset");
$stmt->execute($params);
$exams = $stmt->fetchAll();

// Fetch classes for dropdown (filtered by instructor if applicable)
$classQuery = "SELECT c.id, CONCAT(co.course_code, ' - ', co.course_name, ' (', c.section, ')') as name
               FROM classes c
               JOIN courses co ON c.course_id = co.id";
if (is_instructor()) {
    $classQuery .= " WHERE c.instructor_id = $instructor_id";
}
$classes = $pdo->query($classQuery)->fetchAll();

require_once '../includes/header.php';
?>

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-bold text-[#1a237e]">Exam Management</h1>
        <p class="text-sm text-gray-500">Schedule and manage examinations</p>
    </div>
    <?php if(has_permission('admin') || is_instructor()): ?>
    <button onclick="openModal()" class="bg-[#1a237e] text-white px-4 py-2 rounded-lg font-semibold hover:bg-[#000666] transition flex items-center gap-2 shadow-lg shadow-indigo-900/20">
        <span class="material-symbols-outlined text-sm">add</span> Schedule Exam
    </button>
    <?php endif; ?>
</div>

<!-- Search Form -->
<form method="GET" class="mb-6 flex gap-2">
    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search by course name, code, or exam type..." class="px-4 py-2 border rounded-lg w-full max-w-md focus:ring-2 focus:ring-[#1a237e]">
    <button type="submit" class="bg-gray-100 px-4 py-2 rounded-lg border hover:bg-gray-200">Search</button>
    <?php if($search): ?>
        <a href="exams.php" class="px-4 py-2 text-gray-500 hover:text-gray-700">Clear</a>
    <?php endif; ?>
</form>

<div class="glass-card rounded-xl shadow-sm overflow-hidden mb-6">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                <th class="px-6 py-4">Course</th>
                <th class="px-6 py-4">Exam Type</th>
                <th class="px-6 py-4">Date & Time</th>
                <th class="px-6 py-4">Total Marks</th>
                <th class="px-6 py-4">Status</th>
                <th class="px-6 py-4">Results</th>
                <?php if(has_permission('admin')): ?><th class="px-6 py-4 text-right">Actions</th><?php endif; ?>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            <?php if(empty($exams)): ?>
                <tr><td colspan="7" class="px-6 py-4 text-center text-gray-500">No exams found.</td></tr>
            <?php else: ?>
                <?php foreach($exams as $exam): ?>
                <tr class="hover:bg-indigo-50/30 transition-colors">
                    <td class="px-6 py-4">
                        <div class="font-semibold text-[#1a237e]"><?php echo htmlspecialchars($exam['course_code']); ?></div>
                        <div class="text-sm text-gray-500"><?php echo htmlspecialchars($exam['course_name'] . ' (' . $exam['section'] . ')'); ?></div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-xs font-bold"><?php echo htmlspecialchars($exam['exam_type']); ?></span>
                        <?php if($exam['term']): ?>
                        <div class="text-xs text-gray-500 mt-1"><?php echo htmlspecialchars($exam['term']); ?></div>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm font-semibold"><?php echo date('M d, Y', strtotime($exam['exam_date'])); ?></div>
                        <div class="text-xs text-gray-500"><?php echo date('H:i', strtotime($exam['start_time'])) . ' - ' . date('H:i', strtotime($exam['end_time'])); ?></div>
                    </td>
                    <td class="px-6 py-4 text-gray-700"><?php echo $exam['total_marks']; ?></td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 rounded-full text-xs font-bold
                            <?php echo $exam['status'] === 'completed' ? 'bg-green-100 text-green-800' :
                                     ($exam['status'] === 'ongoing' ? 'bg-yellow-100 text-yellow-800' :
                                     ($exam['status'] === 'cancelled' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800')); ?>">
                            <?php echo htmlspecialchars(ucfirst($exam['status'])); ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 text-gray-700"><?php echo $exam['results_count']; ?> submitted</td>
                    <?php if(has_permission('admin')): ?>
                    <td class="px-6 py-4 text-right">
                        <a href="?delete=<?php echo $exam['id']; ?>" onclick="return confirm('Are you sure you want to delete this exam?');" class="text-red-500 hover:text-red-700">
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
        <a href="?page=<?php echo $i; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?>" class="px-4 py-2 border rounded-lg <?php echo $i === $page ? 'bg-[#1a237e] text-white' : 'bg-white text-[#1a237e] hover:bg-indigo-50'; ?>">
            <?php echo $i; ?>
        </a>
    <?php endfor; ?>
</div>
<?php endif; ?>

<!-- Add Exam Modal -->
<div id="addModal" class="fixed inset-0 bg-black/50 hidden z-50 flex items-center justify-center backdrop-blur-sm transition-opacity duration-300">
    <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-lg transform scale-95 opacity-0 transition-all duration-300 relative max-h-[90vh] overflow-y-auto" id="modalContent">
        <div class="flex justify-between items-center mb-6 pb-3 border-b border-gray-100">
            <h2 class="text-xl font-bold text-[#1a237e] flex items-center gap-2"><span class="material-symbols-outlined">quiz</span> Schedule New Exam</h2>
            <button type="button" onclick="closeModal()" class="text-gray-400 hover:text-gray-700 hover:bg-gray-100 p-1 rounded-full transition-colors"><span class="material-symbols-outlined">close</span></button>
        </div>
        <form method="POST" action="">
            <input type="hidden" name="add_exam" value="1">
            <div class="mb-4">
                <label class="block text-xs font-semibold text-gray-600 mb-1">Class</label>
                <select name="class_id" class="w-full px-3 py-2 border rounded focus:ring-1 focus:ring-[#1a237e]" required>
                    <option value="">Select Class</option>
                    <?php foreach($classes as $class): ?>
                        <option value="<?php echo $class['id']; ?>"><?php echo htmlspecialchars($class['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Exam Type</label>
                    <select name="exam_type" class="w-full px-3 py-2 border rounded focus:ring-1 focus:ring-[#1a237e]" required>
                        <option value="quiz">Quiz</option>
                        <option value="midterm">Midterm</option>
                        <option value="final">Final</option>
                        <option value="assignment">Assignment</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Term (Optional)</label>
                    <input type="text" name="term" placeholder="Midterm 2024" class="w-full px-3 py-2 border rounded focus:ring-1 focus:ring-[#1a237e]">
                </div>
            </div>
            <div class="mb-4">
                <label class="block text-xs font-semibold text-gray-600 mb-1">Exam Date</label>
                <input type="date" name="exam_date" class="w-full px-3 py-2 border rounded focus:ring-1 focus:ring-[#1a237e]" required>
            </div>
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Start Time</label>
                    <input type="time" name="start_time" class="w-full px-3 py-2 border rounded focus:ring-1 focus:ring-[#1a237e]" required>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">End Time</label>
                    <input type="time" name="end_time" class="w-full px-3 py-2 border rounded focus:ring-1 focus:ring-[#1a237e]" required>
                </div>
            </div>
            <div class="mb-6">
                <label class="block text-xs font-semibold text-gray-600 mb-1">Total Marks</label>
                <input type="number" name="total_marks" value="100" min="1" class="w-full px-3 py-2 border rounded focus:ring-1 focus:ring-[#1a237e]" required>
            </div>
            <div class="flex justify-end gap-2 mt-6 pt-4 border-t">
                <button type="button" onclick="closeModal()" class="px-4 py-2 border rounded text-gray-600 hover:bg-gray-50 transition-colors">Cancel</button>
                <button type="submit" class="bg-[#1a237e] text-white px-6 py-2 rounded hover:bg-[#000666] transition-colors shadow-lg shadow-indigo-900/20">Schedule Exam</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal() {
    const modal = document.getElementById('addModal');
    const content = document.getElementById('modalContent');
    modal.classList.remove('hidden');
    // Small delay to allow display:block to apply before animating opacity/transform
    setTimeout(() => {
        content.classList.remove('scale-95', 'opacity-0');
        content.classList.add('scale-100', 'opacity-100');
    }, 10);
}

function closeModal() {
    const modal = document.getElementById('addModal');
    const content = document.getElementById('modalContent');
    content.classList.remove('scale-100', 'opacity-100');
    content.classList.add('scale-95', 'opacity-0');
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 300); // Wait for transition to finish
}
</script>

<?php require_once '../includes/footer.php'; ?>