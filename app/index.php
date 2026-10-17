<?php
require_once __DIR__ . '/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $name = trim($_POST['full_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $package = trim($_POST['package_name'] ?? '');
        $start = $_POST['start_date'] ?? '';
        $end = $_POST['end_date'] ?? '';

        if (
            $name !== '' &&
            $package !== '' &&
            $start !== '' &&
            $end !== '' &&
            $end >= $start
        ) {
            $stmt = $pdo->prepare(
                'INSERT INTO members
                (full_name, phone, email, package_name, start_date, end_date)
                VALUES (?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $name, $phone, $email, $package, $start, $end
            ]);
            header('Location: /?status=added');
            exit;
        }

        header('Location: /?status=invalid');
        exit;
    }

    if ($action === 'delete') {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

        if ($id && $id > 0) {
            $stmt = $pdo->prepare('DELETE FROM members WHERE id = ?');
            $stmt->execute([$id]);
        }

        header('Location: /?status=deleted');
        exit;
    }
}

$members = $pdo->query(
    'SELECT * FROM members ORDER BY id DESC'
)->fetchAll();

$memberCount = count($members);

$activeCount = $pdo->query(
    'SELECT COUNT(*) FROM members
     WHERE start_date <= CURDATE() AND end_date >= CURDATE()'
)->fetchColumn();

$trainerCount = $pdo->query(
    'SELECT COUNT(*) FROM trainers'
)->fetchColumn();

$packages = $pdo->query(
    'SELECT * FROM packages ORDER BY price'
)->fetchAll();

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GymFit - Quản lý phòng gym</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
    <style>
        body { background: #f4f7fb; color: #1e293b; }
        .navbar { background: #14213d; }
        .hero {
            background: linear-gradient(120deg, #14213d, #2563eb);
            color: white; border-radius: 18px; padding: 32px;
        }
        .stat-card, .panel {
            border: 0; border-radius: 14px;
            box-shadow: 0 4px 18px #0f172a0d;
        }
        .stat-number { font-size: 28px; font-weight: 700; }
        .section-title { font-weight: 700; }
    </style>
</head>
<body>
<nav class="navbar navbar-dark">
    <div class="container">
        <a class="navbar-brand fw-bold" href="/">GYMFIT MANAGEMENT</a>
        <a class="btn btn-outline-light btn-sm"
           href="http://localhost:8081" target="_blank"
           rel="noopener noreferrer">Quản trị Database</a>
    </div>
</nav>

<main class="container py-4">
    <section class="hero mb-4">
        <p class="text-uppercase mb-2">Fitness Center</p>
        <h1 class="fw-bold">Hệ thống quản lý phòng Gym</h1>
        <p class="mb-0">Quản lý hội viên và theo dõi hoạt động phòng tập.</p>
    </section>

    <?php if (isset($_GET['status'])): ?>
        <div class="alert alert-info">
            <?php
            $messages = [
                'added' => 'Đã thêm hội viên thành công.',
                'deleted' => 'Đã xử lý yêu cầu xóa hội viên.',
                'invalid' => 'Dữ liệu không hợp lệ. Vui lòng kiểm tra lại.'
            ];
            echo e($messages[$_GET['status']] ?? 'Thao tác hoàn tất.');
            ?>
        </div>
    <?php endif; ?>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card stat-card p-3">
                <div class="text-secondary">Tổng hội viên</div>
                <div class="stat-number"><?= $memberCount ?></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card p-3">
                <div class="text-secondary">Hội viên còn hạn</div>
                <div class="stat-number"><?= (int)$activeCount ?></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card p-3">
                <div class="text-secondary">Huấn luyện viên</div>
                <div class="stat-number"><?= (int)$trainerCount ?></div>
            </div>
        </div>
    </div>

    <section class="card panel p-4 mb-4">
        <h2 class="section-title h4 mb-3">Thêm hội viên</h2>
        <form method="post" class="row g-3">
            <input type="hidden" name="action" value="add">

            <div class="col-md-6">
                <label class="form-label">Họ và tên</label>
                <input class="form-control" name="full_name"
                       maxlength="100" required>
            </div>

            <div class="col-md-6">
                <label class="form-label">Số điện thoại</label>
                <input class="form-control" name="phone"
                       maxlength="20">
            </div>

            <div class="col-md-6">
                <label class="form-label">Email</label>
                <input class="form-control" type="email" name="email"
                       maxlength="120">
            </div>

            <div class="col-md-6">
                <label class="form-label">Gói tập</label>
                <select class="form-select" name="package_name" required>
                    <option value="">-- Chọn gói tập --</option>
                    <?php foreach ($packages as $package): ?>
                        <option value="<?= e($package['package_name']) ?>">
                            <?= e($package['package_name']) ?> -
                            <?= number_format((float)$package['price']) ?> VNĐ
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">Ngày bắt đầu</label>
                <input class="form-control" type="date"
                       name="start_date" required>
            </div>

            <div class="col-md-6">
                <label class="form-label">Ngày kết thúc</label>
                <input class="form-control" type="date"
                       name="end_date" required>
            </div>

            <div class="col-12">
                <button class="btn btn-primary" type="submit">
                    Thêm hội viên
                </button>
            </div>
        </form>
    </section>

    <section class="card panel p-4 mb-4">
        <h2 class="section-title h4 mb-3">Danh sách hội viên</h2>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Họ tên</th>
                        <th>Điện thoại</th>
                        <th>Gói tập</th>
                        <th>Bắt đầu</th>
                        <th>Kết thúc</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!$members): ?>
                    <tr>
                        <td colspan="6" class="text-center text-secondary">
                            Chưa có hội viên. Hãy thêm hội viên đầu tiên.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($members as $member): ?>
                        <tr>
                            <td><?= e($member['full_name']) ?></td>
                            <td><?= e($member['phone']) ?></td>
                            <td><?= e($member['package_name']) ?></td>
                            <td><?= e($member['start_date']) ?></td>
                            <td><?= e($member['end_date']) ?></td>
                            <td>
                                <form method="post"
                                      onsubmit="return confirm('Xóa hội viên này?')">
                                    <input type="hidden" name="action"
                                           value="delete">
                                    <input type="hidden" name="id"
                                           value="<?= (int)$member['id'] ?>">
                                    <button class="btn btn-sm btn-danger"
                                            type="submit">Xóa</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="card panel p-4">
        <h2 class="section-title h4 mb-3">Gói tập hiện có</h2>
        <div class="row g-3">
            <?php foreach ($packages as $package): ?>
                <div class="col-md-6">
                    <div class="border rounded-3 p-3 h-100">
                        <h3 class="h5"><?= e($package['package_name']) ?></h3>
                        <p class="mb-1">
                            Thời hạn: <?= (int)$package['duration_months'] ?> tháng
                        </p>
                        <strong class="text-primary">
                            <?= number_format((float)$package['price']) ?> VNĐ
                        </strong>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
</main>

<footer class="text-center text-secondary py-4">
    GymFit Management - Đề tài thực hành 23
</footer>
</body>
</html>
