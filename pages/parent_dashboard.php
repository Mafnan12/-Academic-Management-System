<?php
require_once '../includes/config.php';
require_once '../includes/db.php';

check_login();
if (!is_parent()) {
    header("Location: " . BASE_URL . "/index.php");
    exit();
}

// Get parent info and linked students
$parent_id = $pdo->query("SELECT id FROM parents WHERE user_id = {$_SESSION['user_id']}")->fetchColumn();

$linked_students = $pdo->query("
    SELECT s.*, psl.relationship
    FROM students s
    JOIN parent_student_link psl ON s.id = psl.student_id
    WHERE psl.parent_id = $parent_id
")->fetchAll();

// Get children's results
$children_results = [];
$children_attendance = [];
$children_fees = [];

foreach ($linked_students as $student) {
    $student_id = $student['id'];

    // Get recent results
    $results = $pdo->query("
        SELECT r.*, e.exam_type, co.course_name, c.section
        FROM results r
        JOIN exams e ON r.exam_id = e.id
        JOIN classes c ON e.class_id = c.id
        JOIN courses co ON c.course_id = co.id
        WHERE r.student_id = $student_id
        ORDER BY e.exam_date DESC
        LIMIT 3
    ")->fetchAll();

    $children_results[$student_id] = $results;

    // Get attendance summary
    $attendance = $pdo->query("
        SELECT
            co.course_name,
            c.section,
            COUNT(a.id) as total_classes,
            SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present_count,
            ROUND((SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) / COUNT(a.id)) * 100, 1) as attendance_percentage
        FROM attendance a
        JOIN student_class_enrollment sce ON a.student_class_id = sce.id
        JOIN classes c ON sce.class_id = c.id
        JOIN courses co ON c.course_id = co.id
        WHERE sce.student_id = $student_id
        GROUP BY c.id
    ")->fetchAll();

    $children_attendance[$student_id] = $attendance;

    // Get fee status
    $fees = $pdo->query("
        SELECT f.*, SUM(p.amount_paid) as paid_amount
        FROM fees f
        LEFT JOIN payments p ON f.id = p.fee_id
        WHERE f.student_id = $student_id
        GROUP BY f.id
    ")->fetchAll();

    $children_fees[$student_id] = $fees;
}

require_once '../includes/header.php';
?>

<section class="relative h-64 rounded-xl overflow-hidden shadow-2xl group mb-8">
    <div class="absolute inset-0 bg-gradient-to-r from-[#1a237e]/90 via-[#1a237e]/60 to-[#1a237e]/30 z-10"></div>
    <img src="<?php echo BASE_URL; ?>/assets/images/campus2.jpg" alt="Campus" class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105 z-0" onerror="this.style.display='none'">
    <div class="absolute inset-0 flex flex-col justify-center px-12 z-20">
        <span class="text-white/80 font-label-sm uppercase tracking-[0.2em] mb-2">Parent Portal</span>
        <h2 class="text-white text-3xl font-bold mb-4 max-w-lg leading-tight">Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
        <div class="flex gap-4">
            <a href="child_progress.php" class="bg-white text-[#1a237e] px-6 py-2.5 rounded-lg font-bold shadow-xl hover:bg-gray-100 transition-all">View Child Progress</a>
        </div>
    </div>
</section>

<section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Stats Cards -->
    <div class="glass-card p-6 rounded-xl shadow-sm hover:-translate-y-1 transition-all duration-300">
        <div class="flex items-start justify-between mb-4">
            <div class="p-3 bg-indigo-50 text-[#1a237e] rounded-lg">
                <span class="material-symbols-outlined">child_care</span>
            </div>
        </div>
        <p class="text-gray-500 text-xs font-bold uppercase tracking-wider mb-1">Children</p>
        <h3 class="text-3xl font-black text-[#1a237e]"><?php echo count($linked_students); ?></h3>
    </div>

    <div class="glass-card p-6 rounded-xl shadow-sm hover:-translate-y-1 transition-all duration-300">
        <div class="flex items-start justify-between mb-4">
            <div class="p-3 bg-indigo-50 text-[#1a237e] rounded-lg">
                <span class="material-symbols-outlined">grade</span>
            </div>
        </div>
        <p class="text-gray-500 text-xs font-bold uppercase tracking-wider mb-1">Total Results</p>
        <h3 class="text-3xl font-black text-[#1a237e]">
            <?php echo array_sum(array_map('count', $children_results)); ?>
        </h3>
    </div>

    <div class="glass-card p-6 rounded-xl shadow-sm hover:-translate-y-1 transition-all duration-300">
        <div class="flex items-start justify-between mb-4">
            <div class="p-3 bg-indigo-50 text-[#1a237e] rounded-lg">
                <span class="material-symbols-outlined">check_circle</span>
            </div>
        </div>
        <p class="text-gray-500 text-xs font-bold uppercase tracking-wider mb-1">Avg Attendance</p>
        <h3 class="text-3xl font-black text-[#1a237e]">
            <?php
            $all_attendance = array_merge(...array_values($children_attendance));
            $avg_attendance = 0;
            if (!empty($all_attendance)) {
                $avg_attendance = round(array_sum(array_column($all_attendance, 'attendance_percentage')) / count($all_attendance), 1);
            }
            echo $avg_attendance . '%';
            ?>
        </h3>
    </div>

    <div class="glass-card p-6 rounded-xl shadow-sm hover:-translate-y-1 transition-all duration-300">
        <div class="flex items-start justify-between mb-4">
            <div class="p-3 bg-indigo-50 text-[#1a237e] rounded-lg">
                <span class="material-symbols-outlined">payment</span>
            </div>
        </div>
        <p class="text-gray-500 text-xs font-bold uppercase tracking-wider mb-1">Pending Fees</p>
        <h3 class="text-3xl font-black text-[#1a237e]">
            <?php
            $pending_count = 0;
            foreach ($children_fees as $fees) {
                foreach ($fees as $fee) {
                    if ($fee['amount'] > ($fee['paid_amount'] ?? 0)) {
                        $pending_count++;
                    }
                }
            }
            echo $pending_count;
            ?>
        </h3>
    </div>
</section>

<?php foreach ($linked_students as $student): ?>
<section class="glass-card rounded-xl shadow-sm overflow-hidden mb-6">
    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between bg-white/50">
        <div>
            <h2 class="text-xl text-[#1a237e] font-bold"><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></h2>
            <p class="text-sm text-gray-500">Class: <?php echo htmlspecialchars($student['class'] . ' ' . $student['section']); ?> | Roll: <?php echo htmlspecialchars($student['roll_number']); ?></p>
        </div>
    </div>

    <div class="p-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Results -->
        <div>
            <h3 class="text-lg font-semibold text-[#1a237e] mb-4">Recent Results</h3>
            <div class="space-y-3">
                <?php if (empty($children_results[$student['id']])): ?>
                    <p class="text-sm text-gray-500">No results available.</p>
                <?php else: ?>
                    <?php foreach ($children_results[$student['id']] as $result): ?>
                    <div class="bg-gray-50 p-3 rounded-lg">
                        <div class="flex justify-between items-start mb-2">
                            <span class="text-sm font-semibold text-[#1a237e]"><?php echo htmlspecialchars($result['course_name']); ?></span>
                            <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-bold"><?php echo htmlspecialchars($result['grade']); ?></span>
                        </div>
                        <p class="text-xs text-gray-600"><?php echo htmlspecialchars($result['exam_type']); ?> - <?php echo $result['marks_obtained']; ?>/<?php echo $result['total_marks']; ?></p>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Attendance Summary -->
        <div>
            <h3 class="text-lg font-semibold text-[#1a237e] mb-4">Attendance Summary</h3>
            <div class="space-y-3">
                <?php if (empty($children_attendance[$student['id']])): ?>
                    <p class="text-sm text-gray-500">No attendance data.</p>
                <?php else: ?>
                    <?php foreach ($children_attendance[$student['id']] as $attendance): ?>
                    <div class="bg-gray-50 p-3 rounded-lg">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm font-semibold text-[#1a237e]"><?php echo htmlspecialchars($attendance['course_name']); ?></span>
                            <span class="text-sm <?php echo $attendance['attendance_percentage'] >= 75 ? 'text-green-600' : 'text-red-600'; ?> font-bold">
                                <?php echo $attendance['attendance_percentage']; ?>%
                            </span>
                        </div>
                        <p class="text-xs text-gray-600"><?php echo $attendance['present_count']; ?>/<?php echo $attendance['total_classes']; ?> classes</p>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Fee Status -->
        <div>
            <h3 class="text-lg font-semibold text-[#1a237e] mb-4">Fee Status</h3>
            <div class="space-y-3">
                <?php if (empty($children_fees[$student['id']])): ?>
                    <p class="text-sm text-gray-500">No fee records.</p>
                <?php else: ?>
                    <?php foreach ($children_fees[$student['id']] as $fee): ?>
                    <div class="bg-gray-50 p-3 rounded-lg">
                        <div class="flex justify-between items-start mb-2">
                            <span class="text-sm font-semibold text-[#1a237e]"><?php echo htmlspecialchars($fee['fee_type']); ?></span>
                            <span class="text-xs <?php echo $fee['amount'] <= ($fee['paid_amount'] ?? 0) ? 'text-green-600' : 'text-red-600'; ?>">
                                <?php echo $fee['amount'] <= ($fee['paid_amount'] ?? 0) ? 'Paid' : 'Pending'; ?>
                            </span>
                        </div>
                        <p class="text-xs text-gray-600">Rs. <?php echo number_format($fee['amount']); ?> | Paid: Rs. <?php echo number_format($fee['paid_amount'] ?? 0); ?></p>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
<?php endforeach; ?>

<?php require_once '../includes/footer.php'; ?>