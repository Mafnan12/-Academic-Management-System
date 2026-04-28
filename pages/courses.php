<?php
require_once '../includes/config.php';
require_once '../includes/db.php';

check_login();

// Handle Add Course
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_course'])) {
    $course_code = trim($_POST['course_code']);
    $course_name = trim($_POST['course_name']);
    $credits = (int)$_POST['credits'];
    $fee = (float)$_POST['fee'];
    $department = trim($_POST['department']);
    $instructor_id = !empty($_POST['instructor_id']) ? (int)$_POST['instructor_id'] : null;

    if (!empty($course_code) && !empty($course_name) && $credits > 0 && $fee >= 0) {
        try {
            $stmt = $pdo->prepare("INSERT INTO courses (course_code, course_name, credits, fee, department, instructor_id) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$course_code, $course_name, $credits, $fee, $department, $instructor_id]);
            set_flash_message('success', 'Course added successfully.');
        } catch (PDOException $e) {
            set_flash_message('error', 'Error adding course: ' . $e->getMessage());
        }
    } else {
        set_flash_message('error', 'Please provide valid data.');
    }
    header("Location: courses.php");
    exit();
}

// Handle Delete Course
if (isset($_GET['delete']) && is_admin()) {
    $id = (int)$_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM courses WHERE id = ?");
        $stmt->execute([$id]);
        set_flash_message('success', 'Course deleted successfully.');
    } catch (PDOException $e) {
        set_flash_message('error', 'Error deleting course.');
    }
    header("Location: courses.php");
    exit();
}

// Search and Pagination
$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Build Query
$query = "FROM courses c LEFT JOIN instructors i ON c.instructor_id = i.id WHERE 1=1";
$params = [];
if (!empty($search)) {
    $query .= " AND (c.course_name LIKE ? OR c.course_code LIKE ?)";
    $searchTerm = "%$search%";
    $params = [$searchTerm, $searchTerm];
}

// Total count
$totalStmt = $pdo->prepare("SELECT COUNT(*) $query");
$totalStmt->execute($params);
$totalCourses = $totalStmt->fetchColumn();
$totalPages = ceil($totalCourses / $limit);

// Fetch courses
$stmt = $pdo->prepare("SELECT c.*, i.first_name as inst_first, i.last_name as inst_last $query ORDER BY c.id DESC LIMIT $limit OFFSET $offset");
$stmt->execute($params);
$courses = $stmt->fetchAll();

// Fetch instructors for dropdown
$instructors = $pdo->query("SELECT id, first_name, last_name FROM instructors ORDER BY first_name")->fetchAll();

require_once '../includes/header.php';
?>

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-bold text-[#1a237e]">Courses Offered</h1>
        <p class="text-sm text-gray-500">Manage academic courses and curriculum</p>
    </div>
    <?php if(is_admin()): ?>
    <button onclick="document.getElementById('addModal').classList.remove('hidden')" class="bg-[#1a237e] text-white px-4 py-2 rounded-lg font-semibold hover:bg-[#000666] transition flex items-center gap-2">
        <span class="material-symbols-outlined text-sm">add</span> Add Course
    </button>
    <?php endif; ?>
</div>

<!-- Search Form -->
<form method="GET" class="mb-6 flex gap-2">
    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search by course code or name..." class="px-4 py-2 border rounded-lg w-full max-w-md focus:ring-2 focus:ring-[#1a237e]">
    <button type="submit" class="bg-gray-100 px-4 py-2 rounded-lg border hover:bg-gray-200">Search</button>
    <?php if($search): ?>
        <a href="courses.php" class="px-4 py-2 text-gray-500 hover:text-gray-700 mt-2">Clear</a>
    <?php endif; ?>
</form>

<div class="glass-card rounded-xl shadow-sm overflow-hidden mb-6">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                <th class="px-6 py-4">Code</th>
                <th class="px-6 py-4">Course Name</th>
                <th class="px-6 py-4">Credits</th>
                <th class="px-6 py-4">Fee</th>
                <th class="px-6 py-4">Instructor</th>
                <th class="px-6 py-4">Department</th>
                <?php if(is_admin()): ?><th class="px-6 py-4 text-right">Actions</th><?php endif; ?>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            <?php if(empty($courses)): ?>
                <tr><td colspan="7" class="px-6 py-4 text-center text-gray-500">No courses found.</td></tr>
            <?php else: ?>
                <?php foreach($courses as $course): ?>
                <tr class="hover:bg-indigo-50/30 transition-colors">
                    <td class="px-6 py-4 font-bold text-[#1a237e]"><?php echo htmlspecialchars($course['course_code']); ?></td>
                    <td class="px-6 py-4 text-gray-800 font-semibold"><?php echo htmlspecialchars($course['course_name']); ?></td>
                    <td class="px-6 py-4"><span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs font-bold"><?php echo $course['credits']; ?> Cr</span></td>
                    <td class="px-6 py-4 text-emerald-600 font-semibold">Rs <?php echo number_format($course['fee'], 2); ?></td>
                    <td class="px-6 py-4 text-gray-600">
                        <?php echo $course['instructor_id'] ? htmlspecialchars($course['inst_first'] . ' ' . $course['inst_last']) : '<span class="text-gray-400 italic">Not Assigned</span>'; ?>
                    </td>
                    <td class="px-6 py-4">
                        <span class="bg-indigo-100 text-indigo-800 px-3 py-1 rounded-full text-xs font-bold"><?php echo htmlspecialchars($course['department']); ?></span>
                    </td>
                    <?php if(is_admin()): ?>
                    <td class="px-6 py-4 text-right">
                        <a href="?delete=<?php echo $course['id']; ?>" onclick="return confirm('Are you sure you want to delete this course?');" class="text-red-500 hover:text-red-700">
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

<!-- Add Course Modal -->
<div id="addModal" class="fixed inset-0 bg-black/50 hidden z-50 flex items-center justify-center backdrop-blur-sm">
    <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-lg">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold text-[#1a237e]">Add New Course</h2>
            <button onclick="document.getElementById('addModal').classList.add('hidden')" class="text-gray-500 hover:text-gray-700"><span class="material-symbols-outlined">close</span></button>
        </div>
        <form method="POST" action="">
            <input type="hidden" name="add_course" value="1">
            <div class="grid grid-cols-3 gap-4 mb-4">
                <div class="col-span-1">
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Course Code</label>
                    <input type="text" name="course_code" placeholder="CS101" required class="w-full px-3 py-2 border rounded focus:ring-1 focus:ring-[#1a237e]">
                </div>
                <div class="col-span-2">
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Course Name</label>
                    <input type="text" name="course_name" required class="w-full px-3 py-2 border rounded focus:ring-1 focus:ring-[#1a237e]">
                </div>
            </div>
            <div class="grid grid-cols-3 gap-4 mb-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Credits</label>
                    <input type="number" name="credits" min="1" max="6" value="3" required class="w-full px-3 py-2 border rounded focus:ring-1 focus:ring-[#1a237e]">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Fee (Rs)</label>
                    <input type="number" name="fee" min="0" step="100" value="15000" required class="w-full px-3 py-2 border rounded focus:ring-1 focus:ring-[#1a237e]">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Department</label>
                    <select name="department" class="w-full px-3 py-2 border rounded focus:ring-1 focus:ring-[#1a237e]" required>
                        <option value="Computer Science">Computer Science</option>
                        <option value="Software Engineering">Software Engineering</option>
                        <option value="Artificial Intelligence">Artificial Intelligence</option>
                        <option value="Data Science">Data Science</option>
                    </select>
                </div>
            </div>
            <div class="mb-6">
                <label class="block text-xs font-semibold text-gray-600 mb-1">Assign Instructor (Optional)</label>
                <select name="instructor_id" class="w-full px-3 py-2 border rounded focus:ring-1 focus:ring-[#1a237e]">
                    <option value="">-- No Instructor Yet --</option>
                    <?php foreach($instructors as $inst): ?>
                        <option value="<?php echo $inst['id']; ?>"><?php echo htmlspecialchars($inst['first_name'] . ' ' . $inst['last_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('addModal').classList.add('hidden')" class="px-4 py-2 border rounded text-gray-600 hover:bg-gray-50">Cancel</button>
                <button type="submit" class="bg-[#1a237e] text-white px-4 py-2 rounded hover:bg-[#000666]">Save Course</button>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
