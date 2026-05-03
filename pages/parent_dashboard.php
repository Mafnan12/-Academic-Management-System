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
        SELECT er.*, e.exam_name, e.exam_type, co.course_name, c.section
        FROM exam_results er
        JOIN exams e ON er.exam_id = e.id
        JOIN classes c ON e.class_id = c.id
        JOIN courses co ON c.course_id = co.id
        WHERE er.student_id = $student_id
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
        JOIN classes c ON a.class_id = c.id
        JOIN courses co ON c.course_id = co.id
        WHERE a.student_id = $student_id
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

<section class="relative h-72 rounded-3xl overflow-hidden shadow-premium group mb-10">
    <div class="absolute inset-0 bg-gradient-to-r from-secondary-dark via-secondary-dark/60 to-transparent z-10"></div>
    <img src="<?php echo BASE_URL; ?>/assets/images/campus2.png" alt="Campus" class="absolute inset-0 w-full h-full object-cover transition-transform duration-1000 group-hover:scale-110 z-0" onerror="this.style.display='none'">
    <div class="absolute inset-0 flex flex-col justify-center px-12 z-20">
        <span class="text-accent font-bold uppercase tracking-[0.3em] text-xs mb-3">Parental Guidance</span>
        <h2 class="text-white text-4xl font-black mb-6 max-w-lg leading-tight font-serif">Welcome back,<br/><span class="text-primary-light"><?php echo htmlspecialchars($_SESSION['username']); ?>!</span></h2>
        <div class="flex gap-4">
            <a href="reports.php" class="bg-primary text-white px-8 py-3 rounded-xl font-bold shadow-lg shadow-primary/20 hover:bg-primary-dark transition-all flex items-center gap-2 group/btn">
                <span>View Children Progress</span>
                <span class="material-symbols-outlined text-xl group-hover/btn:translate-x-1 transition-transform">arrow_forward</span>
            </a>
            <a href="reports.php" class="bg-white/10 backdrop-blur-md text-white border border-white/20 px-8 py-3 rounded-xl font-bold hover:bg-white/20 transition-all">Academic Reports</a>
        </div>
    </div>
</section>

<section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-10">
    <!-- Stats Cards -->
    <div class="glass-card p-8 rounded-2xl">
        <div class="flex items-center justify-between mb-6">
            <div class="w-12 h-12 bg-primary/10 text-primary rounded-xl flex items-center justify-center">
                <span class="material-symbols-outlined text-2xl">child_care</span>
            </div>
            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Linked</span>
        </div>
        <p class="text-slate-500 text-xs font-bold uppercase tracking-wider mb-1">Children</p>
        <h3 class="text-3xl font-black text-secondary-dark"><?php echo count($linked_students); ?></h3>
    </div>

    <div class="glass-card p-8 rounded-2xl">
        <div class="flex items-center justify-between mb-6">
            <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center">
                <span class="material-symbols-outlined text-2xl">grade</span>
            </div>
            <span class="text-[10px] font-black text-emerald-500 uppercase tracking-widest">+4 New</span>
        </div>
        <p class="text-slate-500 text-xs font-bold uppercase tracking-wider mb-1">Total Results</p>
        <h3 class="text-3xl font-black text-secondary-dark">
            <?php echo array_sum(array_map('count', $children_results)); ?>
        </h3>
    </div>

    <div class="glass-card p-8 rounded-2xl">
        <div class="flex items-center justify-between mb-6">
            <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center">
                <span class="material-symbols-outlined text-2xl">check_circle</span>
            </div>
            <span class="text-[10px] font-black text-amber-500 uppercase tracking-widest">Aggregate</span>
        </div>
        <p class="text-slate-500 text-xs font-bold uppercase tracking-wider mb-1">Avg Attendance</p>
        <h3 class="text-3xl font-black text-secondary-dark">
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

    <div class="glass-card p-8 rounded-2xl">
        <div class="flex items-center justify-between mb-6">
            <div class="w-12 h-12 bg-rose-50 text-rose-600 rounded-xl flex items-center justify-center">
                <span class="material-symbols-outlined text-2xl">payments</span>
            </div>
            <span class="text-[10px] font-black text-rose-500 uppercase tracking-widest">Pending</span>
        </div>
        <p class="text-slate-500 text-xs font-bold uppercase tracking-wider mb-1">Fee Invoices</p>
        <h3 class="text-3xl font-black text-secondary-dark">
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
<section class="glass-card rounded-3xl overflow-hidden mb-10 border border-slate-100">
    <div class="px-10 py-8 border-b border-slate-100 bg-white/50 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-5">
            <div class="w-16 h-16 rounded-2xl bg-primary-soft border border-primary/10 flex items-center justify-center text-2xl font-black text-primary font-serif">
                <?php echo strtoupper(substr($student['first_name'], 0, 1)); ?>
            </div>
            <div>
                <h2 class="text-2xl text-secondary-dark font-black font-serif"><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></h2>
                <div class="flex flex-wrap gap-x-4 gap-y-1 mt-1">
                    <span class="text-[10px] text-slate-400 font-bold uppercase tracking-widest flex items-center gap-1.5"><span class="material-symbols-outlined text-xs">school</span> <?php echo htmlspecialchars($student['class'] . ' ' . $student['section']); ?></span>
                    <span class="text-[10px] text-slate-400 font-bold uppercase tracking-widest flex items-center gap-1.5"><span class="material-symbols-outlined text-xs">fingerprint</span> Roll: <?php echo htmlspecialchars($student['roll_number']); ?></span>
                </div>
            </div>
        </div>
        <a href="child_progress.php?id=<?php echo $student['id']; ?>" class="bg-slate-100 hover:bg-slate-200 text-slate-600 px-6 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest transition-colors flex items-center gap-2 w-fit">
            <span>Detailed Analysis</span>
            <span class="material-symbols-outlined text-sm">analytics</span>
        </a>
    </div>

    <div class="p-10 grid grid-cols-1 lg:grid-cols-3 gap-10">
        <!-- Recent Results -->
        <div>
            <div class="flex items-center gap-2 mb-6">
                <span class="material-symbols-outlined text-primary">grade</span>
                <h3 class="text-sm font-black text-secondary-dark uppercase tracking-widest">Recent Performance</h3>
            </div>
            <div class="space-y-4">
                <?php if (empty($children_results[$student['id']])): ?>
                    <div class="p-8 rounded-2xl bg-slate-50/50 border border-dashed border-slate-200 text-center">
                        <p class="text-xs text-slate-400 font-medium italic">No results released yet.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($children_results[$student['id']] as $result): ?>
                    <div class="p-4 rounded-xl bg-slate-50/50 border border-slate-100 hover:border-primary/20 transition-all group">
                        <div class="flex justify-between items-start mb-2">
                            <span class="text-sm font-bold text-secondary-dark group-hover:text-primary transition-colors"><?php echo htmlspecialchars($result['course_name']); ?></span>
                            <span class="bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded text-[10px] font-black">GRADE <?php echo htmlspecialchars($result['grade']); ?></span>
                        </div>
                        <div class="flex justify-between items-center text-[10px] font-black text-slate-400 uppercase tracking-widest">
                            <span><?php echo htmlspecialchars($result['exam_type']); ?></span>
                            <span><?php echo $result['marks_obtained']; ?> / <?php echo $result['total_marks']; ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Attendance Summary -->
        <div>
            <div class="flex items-center gap-2 mb-6">
                <span class="material-symbols-outlined text-amber-500">event_available</span>
                <h3 class="text-sm font-black text-secondary-dark uppercase tracking-widest">Attendance Status</h3>
            </div>
            <div class="space-y-4">
                <?php if (empty($children_attendance[$student['id']])): ?>
                    <div class="p-8 rounded-2xl bg-slate-50/50 border border-dashed border-slate-200 text-center">
                        <p class="text-xs text-slate-400 font-medium italic">No attendance records.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($children_attendance[$student['id']] as $attendance): ?>
                    <div class="space-y-2">
                        <div class="flex justify-between items-center">
                            <span class="text-xs font-bold text-secondary-dark"><?php echo htmlspecialchars($attendance['course_name']); ?></span>
                            <span class="text-xs font-black <?php echo $attendance['attendance_percentage'] >= 75 ? 'text-emerald-600' : 'text-rose-600'; ?>">
                                <?php echo $attendance['attendance_percentage']; ?>%
                            </span>
                        </div>
                        <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden">
                            <div class="h-full <?php echo $attendance['attendance_percentage'] >= 75 ? 'bg-emerald-500' : 'bg-rose-500'; ?> transition-all duration-1000" style="width: <?php echo $attendance['attendance_percentage']; ?>%"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Fee Status -->
        <div>
            <div class="flex items-center gap-2 mb-6">
                <span class="material-symbols-outlined text-rose-500">payments</span>
                <h3 class="text-sm font-black text-secondary-dark uppercase tracking-widest">Fee Status</h3>
            </div>
            <div class="space-y-4">
                <?php if (empty($children_fees[$student['id']])): ?>
                    <div class="p-8 rounded-2xl bg-slate-50/50 border border-dashed border-slate-200 text-center">
                        <p class="text-xs text-slate-400 font-medium italic">No fee records found.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($children_fees[$student['id']] as $fee): ?>
                    <div class="p-4 rounded-xl bg-slate-50/50 border border-slate-100 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-bold text-secondary-dark"><?php echo htmlspecialchars($fee['fee_type']); ?></p>
                            <p class="text-[10px] text-slate-400 font-bold">Rs. <?php echo number_format($fee['amount']); ?></p>
                        </div>
                        <?php if($fee['amount'] <= ($fee['paid_amount'] ?? 0)): ?>
                            <span class="bg-emerald-50 text-emerald-600 px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest">Paid</span>
                        <?php else: ?>
                            <span class="bg-rose-50 text-rose-600 px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest animate-pulse">Pending</span>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
<?php endforeach; ?>

<?php require_once '../includes/footer.php'; ?>