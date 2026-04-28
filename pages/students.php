<?php
require_once '../includes/config.php';
require_once '../includes/db.php';

check_login();

// Handle Add Student
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_student'])) {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $department = trim($_POST['department']);
    $phone = trim($_POST['phone']);
    $dob = $_POST['dob'];

    if (!empty($first_name) && !empty($last_name) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO students (first_name, last_name, email, phone, department, dob) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$first_name, $last_name, $email, $phone, $department, $dob]);
            set_flash_message('success', 'Student added successfully.');
        } catch (PDOException $e) {
            set_flash_message('error', 'Error adding student: ' . $e->getMessage());
        }
    } else {
        set_flash_message('error', 'Please provide valid data.');
    }
    header("Location: students.php");
    exit();
}

// Handle Delete Student
if (isset($_GET['delete']) && is_admin()) {
    $id = (int)$_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
        $stmt->execute([$id]);
        set_flash_message('success', 'Student deleted successfully.');
    } catch (PDOException $e) {
        set_flash_message('error', 'Error deleting student.');
    }
    header("Location: students.php");
    exit();
}

// Search and Pagination
$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Build Query
$query = "FROM students WHERE 1=1";
$params = [];
if (!empty($search)) {
    $query .= " AND (first_name LIKE ? OR last_name LIKE ? OR email LIKE ?)";
    $searchTerm = "%$search%";
    $params = [$searchTerm, $searchTerm, $searchTerm];
}

// Total count for pagination
$totalStmt = $pdo->prepare("SELECT COUNT(*) $query");
$totalStmt->execute($params);
$totalStudents = $totalStmt->fetchColumn();
$totalPages = ceil($totalStudents / $limit);

// Fetch students
$stmt = $pdo->prepare("SELECT * $query ORDER BY id DESC LIMIT $limit OFFSET $offset");
$stmt->execute($params);
$students = $stmt->fetchAll();

require_once '../includes/header.php';
?>

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-bold text-[#1a237e]">Students Directory</h1>
        <p class="text-sm text-gray-500">Manage university student records</p>
    </div>
    <?php if(is_admin()): ?>
    <button onclick="document.getElementById('addModal').classList.remove('hidden')" class="bg-[#1a237e] text-white px-4 py-2 rounded-lg font-semibold hover:bg-[#000666] transition flex items-center gap-2">
        <span class="material-symbols-outlined text-sm">add</span> Add Student
    </button>
    <?php endif; ?>
</div>

<!-- Search Form -->
<form method="GET" class="mb-6 flex gap-2">
    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search by name or email..." class="px-4 py-2 border rounded-lg w-full max-w-md focus:ring-2 focus:ring-[#1a237e]">
    <button type="submit" class="bg-gray-100 px-4 py-2 rounded-lg border hover:bg-gray-200">Search</button>
    <?php if($search): ?>
        <a href="students.php" class="px-4 py-2 text-gray-500 hover:text-gray-700 mt-2">Clear</a>
    <?php endif; ?>
</form>

<div class="glass-card rounded-xl shadow-sm overflow-hidden mb-6">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                <th class="px-6 py-4">Name</th>
                <th class="px-6 py-4">Department</th>
                <th class="px-6 py-4">Email / Phone</th>
                <th class="px-6 py-4">Enrolled Date</th>
                <?php if(is_admin()): ?><th class="px-6 py-4 text-right">Actions</th><?php endif; ?>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            <?php if(empty($students)): ?>
                <tr><td colspan="5" class="px-6 py-4 text-center text-gray-500">No students found.</td></tr>
            <?php else: ?>
                <?php foreach($students as $student): ?>
                <tr class="hover:bg-indigo-50/30 transition-colors">
                    <td class="px-6 py-4">
                        <div class="font-semibold text-[#1a237e]"><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="bg-indigo-100 text-indigo-800 px-3 py-1 rounded-full text-xs font-bold"><?php echo htmlspecialchars($student['department']); ?></span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-gray-800"><?php echo htmlspecialchars($student['email']); ?></div>
                        <div class="text-xs text-gray-500"><?php echo htmlspecialchars($student['phone']); ?></div>
                    </td>
                    <td class="px-6 py-4 text-gray-500 text-sm">
                        <?php echo date('d-m-Y', strtotime($student['created_at'])); ?>
                    </td>
                    <?php if(is_admin()): ?>
                    <td class="px-6 py-4 text-right">
                        <a href="?delete=<?php echo $student['id']; ?>" onclick="return confirm('Are you sure you want to delete this student?');" class="text-red-500 hover:text-red-700">
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

<!-- Add Student Modal -->
<div id="addModal" class="fixed inset-0 bg-black/50 hidden z-50 flex items-center justify-center backdrop-blur-sm">
    <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-lg">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold text-[#1a237e]">Add New Student</h2>
            <button onclick="document.getElementById('addModal').classList.add('hidden')" class="text-gray-500 hover:text-gray-700"><span class="material-symbols-outlined">close</span></button>
        </div>
        <form method="POST" action="">
            <input type="hidden" name="add_student" value="1">
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">First Name</label>
                    <input type="text" name="first_name" required class="w-full px-3 py-2 border rounded focus:ring-1 focus:ring-[#1a237e]">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Last Name</label>
                    <input type="text" name="last_name" required class="w-full px-3 py-2 border rounded focus:ring-1 focus:ring-[#1a237e]">
                </div>
            </div>
            <div class="mb-4">
                <label class="block text-xs font-semibold text-gray-600 mb-1">Email Address</label>
                <input type="email" name="email" required class="w-full px-3 py-2 border rounded focus:ring-1 focus:ring-[#1a237e]">
            </div>
            <div class="mb-4">
                <label class="block text-xs font-semibold text-gray-600 mb-1">Department</label>
                <select name="department" class="w-full px-3 py-2 border rounded focus:ring-1 focus:ring-[#1a237e]" required>
                    <option value="Computer Science">Computer Science</option>
                    <option value="Software Engineering">Software Engineering</option>
                    <option value="Artificial Intelligence">Artificial Intelligence</option>
                    <option value="Data Science">Data Science</option>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Phone Number</label>
                    <input type="text" name="phone" class="w-full px-3 py-2 border rounded focus:ring-1 focus:ring-[#1a237e]">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Date of Birth</label>
                    <input type="date" name="dob" class="w-full px-3 py-2 border rounded focus:ring-1 focus:ring-[#1a237e]">
                </div>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('addModal').classList.add('hidden')" class="px-4 py-2 border rounded text-gray-600 hover:bg-gray-50">Cancel</button>
                <button type="submit" class="bg-[#1a237e] text-white px-4 py-2 rounded hover:bg-[#000666]">Save Student</button>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
