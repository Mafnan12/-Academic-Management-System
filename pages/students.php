<?php
require_once '../includes/config.php';
require_once '../includes/db.php';

check_login();

// Handle Add Student
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_student'])) {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $dob = $_POST['dob'];
    $bform_cnic = trim($_POST['bform_cnic']);
    $address = trim($_POST['address']);
    $parent_info = trim($_POST['parent_info']);
    $admission_year = trim($_POST['admission_year']);
    $class = trim($_POST['class']);
    $section = trim($_POST['section']);
    $roll_number = trim($_POST['roll_number']);
    $batch_year = trim($_POST['batch_year']);

    if (!empty($first_name) && !empty($last_name) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO students (first_name, last_name, email, phone, dob, bform_cnic, address, parent_info, admission_year, class, section, roll_number, batch_year) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$first_name, $last_name, $email, $phone, $dob, $bform_cnic, $address, $parent_info, $admission_year, $class, $section, $roll_number, $batch_year]);
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
    <button onclick="openModal()" class="bg-[#1a237e] text-white px-4 py-2 rounded-lg font-semibold hover:bg-[#000666] transition flex items-center gap-2 shadow-lg shadow-indigo-900/20">
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
                <th class="px-6 py-4">Class/Section</th>
                <th class="px-6 py-4">Roll Number</th>
                <th class="px-6 py-4">Email / Phone</th>
                <th class="px-6 py-4">Status</th>
                <?php if(is_admin()): ?><th class="px-6 py-4 text-right">Actions</th><?php endif; ?>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            <?php if(empty($students)): ?>
                <tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">No students found.</td></tr>
            <?php else: ?>
                <?php foreach($students as $student): ?>
                <tr class="hover:bg-indigo-50/30 transition-colors">
                    <td class="px-6 py-4">
                        <div class="font-semibold text-[#1a237e]"><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="bg-indigo-100 text-indigo-800 px-3 py-1 rounded-full text-xs font-bold"><?php echo htmlspecialchars($student['class'] . ' ' . $student['section']); ?></span>
                    </td>
                    <td class="px-6 py-4 text-gray-700"><?php echo htmlspecialchars($student['roll_number']); ?></td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-gray-800"><?php echo htmlspecialchars($student['email']); ?></div>
                        <div class="text-xs text-gray-500"><?php echo htmlspecialchars($student['phone']); ?></div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 rounded-full text-xs font-bold
                            <?php echo $student['status'] === 'active' ? 'bg-green-100 text-green-800' :
                                     ($student['status'] === 'transferred' ? 'bg-yellow-100 text-yellow-800' :
                                     ($student['status'] === 'left' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800')); ?>">
                            <?php echo htmlspecialchars(ucfirst($student['status'])); ?>
                        </span>
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
<div id="addModal" class="fixed inset-0 bg-black/50 hidden z-50 flex items-center justify-center backdrop-blur-sm transition-opacity duration-300">
    <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-lg transform scale-95 opacity-0 transition-all duration-300 relative max-h-[90vh] overflow-y-auto" id="modalContent">
        <div class="flex justify-between items-center mb-6 pb-3 border-b border-gray-100">
            <h2 class="text-xl font-bold text-[#1a237e] flex items-center gap-2"><span class="material-symbols-outlined">person_add</span> Add New Student</h2>
            <button type="button" onclick="closeModal()" class="text-gray-400 hover:text-gray-700 hover:bg-gray-100 p-1 rounded-full transition-colors"><span class="material-symbols-outlined">close</span></button>
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
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Phone Number</label>
                    <input type="text" name="phone" class="w-full px-3 py-2 border rounded focus:ring-1 focus:ring-[#1a237e]">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Date of Birth</label>
                    <input type="date" name="dob" class="w-full px-3 py-2 border rounded focus:ring-1 focus:ring-[#1a237e]">
                </div>
            </div>
            <div class="mb-4">
                <label class="block text-xs font-semibold text-gray-600 mb-1">B-Form/CNIC</label>
                <input type="text" name="bform_cnic" class="w-full px-3 py-2 border rounded focus:ring-1 focus:ring-[#1a237e]">
            </div>
            <div class="mb-4">
                <label class="block text-xs font-semibold text-gray-600 mb-1">Address</label>
                <textarea name="address" rows="2" class="w-full px-3 py-2 border rounded focus:ring-1 focus:ring-[#1a237e]"></textarea>
            </div>
            <div class="mb-4">
                <label class="block text-xs font-semibold text-gray-600 mb-1">Parent Information</label>
                <textarea name="parent_info" rows="2" class="w-full px-3 py-2 border rounded focus:ring-1 focus:ring-[#1a237e]"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Admission Year</label>
                    <input type="text" name="admission_year" placeholder="2020" class="w-full px-3 py-2 border rounded focus:ring-1 focus:ring-[#1a237e]">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Batch Year</label>
                    <input type="text" name="batch_year" placeholder="2020-2024" class="w-full px-3 py-2 border rounded focus:ring-1 focus:ring-[#1a237e]">
                </div>
            </div>
            <div class="grid grid-cols-3 gap-4 mb-6">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Class</label>
                    <select name="class" class="w-full px-3 py-2 border rounded focus:ring-1 focus:ring-[#1a237e]">
                        <option value="">Select Class</option>
                        <option value="BCS-1">BCS-1</option>
                        <option value="BCS-2">BCS-2</option>
                        <option value="BCS-3">BCS-3</option>
                        <option value="BCS-4">BCS-4</option>
                        <option value="BCS-5">BCS-5</option>
                        <option value="BCS-6">BCS-6</option>
                        <option value="BCS-7">BCS-7</option>
                        <option value="BCS-8">BCS-8</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Section</label>
                    <select name="section" class="w-full px-3 py-2 border rounded focus:ring-1 focus:ring-[#1a237e]">
                        <option value="">Select Section</option>
                        <option value="A">A</option>
                        <option value="B">B</option>
                        <option value="C">C</option>
                        <option value="D">D</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Roll Number</label>
                    <input type="text" name="roll_number" placeholder="2020-BCS-001" class="w-full px-3 py-2 border rounded focus:ring-1 focus:ring-[#1a237e]">
                </div>
            </div>
            <div class="flex justify-end gap-2 mt-6 pt-4 border-t">
                <button type="button" onclick="closeModal()" class="px-4 py-2 border rounded text-gray-600 hover:bg-gray-50 transition-colors">Cancel</button>
                <button type="submit" class="bg-[#1a237e] text-white px-6 py-2 rounded hover:bg-[#000666] transition-colors shadow-lg shadow-indigo-900/20">Save Student</button>
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
