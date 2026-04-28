<?php
require_once '../includes/config.php';
require_once '../includes/db.php';

check_login();
if (!has_permission('admin')) {
    header("Location: " . BASE_URL . "/index.php");
    exit();
}

// Handle Add Class
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_class'])) {
    $course_id = (int)$_POST['course_id'];
    $section = trim($_POST['section']);
    $instructor_id = !empty($_POST['instructor_id']) ? (int)$_POST['instructor_id'] : null;
    $capacity = (int)$_POST['capacity'];
    $room = trim($_POST['room']);
    $academic_year = trim($_POST['academic_year']);
    $semester = trim($_POST['semester']);

    if ($course_id && !empty($section)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO classes (course_id, section, instructor_id, capacity, room, academic_year, semester) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$course_id, $section, $instructor_id, $capacity, $room, $academic_year, $semester]);
            set_flash_message('success', 'Class added successfully.');
        } catch (PDOException $e) {
            set_flash_message('error', 'Error adding class: ' . $e->getMessage());
        }
    } else {
        set_flash_message('error', 'Please provide valid data.');
    }
    header("Location: classes.php");
    exit();
}

// Handle Delete Class
if (isset($_GET['delete']) && is_admin()) {
    $id = (int)$_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM classes WHERE id = ?");
        $stmt->execute([$id]);
        set_flash_message('success', 'Class deleted successfully.');
    } catch (PDOException $e) {
        set_flash_message('error', 'Error deleting class.');
    }
    header("Location: classes.php");
    exit();
}

// Search and Pagination
$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Build Query
$query = "FROM classes c
          JOIN courses co ON c.course_id = co.id
          LEFT JOIN instructors i ON c.instructor_id = i.id
          WHERE 1=1";
$params = [];
if (!empty($search)) {
    $query .= " AND (co.course_name LIKE ? OR co.course_code LIKE ? OR c.section LIKE ?)";
    $searchTerm = "%$search%";
    $params = [$searchTerm, $searchTerm, $searchTerm];
}

// Total count for pagination
$totalStmt = $pdo->prepare("SELECT COUNT(*) $query");
$totalStmt->execute($params);
$totalClasses = $totalStmt->fetchColumn();
$totalPages = ceil($totalClasses / $limit);

// Fetch classes
$stmt = $pdo->prepare("SELECT c.*, co.course_name, co.course_code,
                       CONCAT(i.first_name, ' ', i.last_name) as instructor_name,
                       (SELECT COUNT(*) FROM student_class_enrollment sce WHERE sce.class_id = c.id AND sce.status = 'enrolled') as enrolled_students
                       $query ORDER BY c.id DESC LIMIT $limit OFFSET $offset");
$stmt->execute($params);
$classes = $stmt->fetchAll();

// Fetch courses and instructors for dropdowns
$courses = $pdo->query("SELECT id, course_code, course_name FROM courses ORDER BY course_code")->fetchAll();
$instructors = $pdo->query("SELECT id, CONCAT(first_name, ' ', last_name) as name FROM instructors ORDER BY first_name")->fetchAll();

require_once '../includes/header.php';
?>

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-bold text-[#1a237e]">Class Management</h1>
        <p class="text-sm text-gray-500">Manage course sections and class assignments</p>
    </div>
    <button onclick="document.getElementById('addModal').classList.remove('hidden')" class="bg-[#1a237e] text-white px-4 py-2 rounded-lg font-semibold hover:bg-[#000666] transition flex items-center gap-2">
        <span class="material-symbols-outlined text-sm">add</span> Add Class
    </button>
</div>

<!-- Search Form -->
<form method="GET" class="mb-6 flex gap-2">
    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search by course name, code, or section..." class="px-4 py-2 border rounded-lg w-full max-w-md focus:ring-2 focus:ring-[#1a237e]">
    <button type="submit" class="bg-gray-100 px-4 py-2 rounded-lg border hover:bg-gray-200">Search</button>
    <?php if($search): ?>
        <a href="classes.php" class="px-4 py-2 text-gray-500 hover:text-gray-700">Clear</a>
    <?php endif; ?>
</form>

<div class="glass-card rounded-xl shadow-sm overflow-hidden mb-6">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                <th class="px-6 py-4">Course</th>
                <th class="px-6 py-4">Section</th>
                <th class="px-6 py-4">Instructor</th>
                <th class="px-6 py-4">Capacity / Enrolled</th>
                <th class="px-6 py-4">Room</th>
                <th class="px-6 py-4">Academic Year</th>
                <th class="px-6 py-4 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            <?php if(empty($classes)): ?>
                <tr><td colspan="7" class="px-6 py-4 text-center text-gray-500">No classes found.</td></tr>
            <?php else: ?>
                <?php foreach($classes as $class): ?>
                <tr class="hover:bg-indigo-50/30 transition-colors">
                    <td class="px-6 py-4">
                        <div class="font-semibold text-[#1a237e]"><?php echo htmlspecialchars($class['course_code']); ?></div>
                        <div class="text-sm text-gray-500"><?php echo htmlspecialchars($class['course_name']); ?></div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="bg-indigo-100 text-indigo-800 px-3 py-1 rounded-full text-xs font-bold"><?php echo htmlspecialchars($class['section']); ?></span>
                    </td>
                    <td class="px-6 py-4 text-gray-700">
                        <?php echo htmlspecialchars($class['instructor_name'] ?? 'Not Assigned'); ?>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-sm"><?php echo $class['enrolled_students']; ?>/<?php echo $class['capacity']; ?></span>
                    </td>
                    <td class="px-6 py-4 text-gray-700"><?php echo htmlspecialchars($class['room']); ?></td>
                    <td class="px-6 py-4 text-gray-700">
                        <?php echo htmlspecialchars($class['academic_year'] . ' ' . $class['semester']); ?>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <a href="?delete=<?php echo $class['id']; ?>" onclick="return confirm('Are you sure you want to delete this class?');" class="text-red-500 hover:text-red-700">
                            <span class="material-symbols-outlined text-sm">delete</span>
                        </a>
                    </td>
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

<!-- Add Class Modal -->
<div id="addModal" class="fixed inset-0 bg-black/50 hidden z-50 flex items-center justify-center backdrop-blur-sm">
    <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-lg">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold text-[#1a237e]">Add New Class</h2>
            <button onclick="document.getElementById('addModal').classList.add('hidden')" class="text-gray-500 hover:text-gray-700"><span class="material-symbols-outlined">close</span></button>
        </div>
        <form method="POST" action="">
            <input type="hidden" name="add_class" value="1">
            <div class="mb-4">
                <label class="block text-xs font-semibold text-gray-600 mb-1">Course</label>
                <select name="course_id" class="w-full px-3 py-2 border rounded focus:ring-1 focus:ring-[#1a237e]" required>
                    <option value="">Select Course</option>
                    <?php foreach($courses as $course): ?>
                        <option value="<?php echo $course['id']; ?>"><?php echo htmlspecialchars($course['course_code'] . ' - ' . $course['course_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Section</label>
                    <select name="section" class="w-full px-3 py-2 border rounded focus:ring-1 focus:ring-[#1a237e]" required>
                        <option value="">Select Section</option>
                        <option value="A">A</option>
                        <option value="B">B</option>
                        <option value="C">C</option>
                        <option value="D">D</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Capacity</label>
                    <input type="number" name="capacity" value="50" min="1" class="w-full px-3 py-2 border rounded focus:ring-1 focus:ring-[#1a237e]" required>
                </div>
            </div>
            <div class="mb-4">
                <label class="block text-xs font-semibold text-gray-600 mb-1">Instructor (Optional)</label>
                <select name="instructor_id" class="w-full px-3 py-2 border rounded focus:ring-1 focus:ring-[#1a237e]">
                    <option value="">Select Instructor</option>
                    <?php foreach($instructors as $instructor): ?>
                        <option value="<?php echo $instructor['id']; ?>"><?php echo htmlspecialchars($instructor['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Room</label>
                    <input type="text" name="room" placeholder="CR-101" class="w-full px-3 py-2 border rounded focus:ring-1 focus:ring-[#1a237e]">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Academic Year</label>
                    <input type="text" name="academic_year" value="2023-2024" class="w-full px-3 py-2 border rounded focus:ring-1 focus:ring-[#1a237e]">
                </div>
            </div>
            <div class="mb-6">
                <label class="block text-xs font-semibold text-gray-600 mb-1">Semester</label>
                <select name="semester" class="w-full px-3 py-2 border rounded focus:ring-1 focus:ring-[#1a237e]">
                    <option value="Fall">Fall</option>
                    <option value="Spring">Spring</option>
                    <option value="Summer">Summer</option>
                </select>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('addModal').classList.add('hidden')" class="px-4 py-2 border rounded text-gray-600 hover:bg-gray-50">Cancel</button>
                <button type="submit" class="bg-[#1a237e] text-white px-4 py-2 rounded hover:bg-[#000666]">Save Class</button>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>