<?php
if (!defined('KIRPI_CORE_ENTRY')) {
    exit;
}

$queueReady = kirpi_queue_table_ready();
$stats = [
    'queued' => 0,
    'processing' => 0,
    'completed' => 0,
    'failed' => 0,
];
$jobs = [];

if ($queueReady) {
    try {
        $statStmt = db()->query("\n            SELECT status, COUNT(id) AS cnt\n            FROM jobs_queue\n            GROUP BY status\n        ");

        foreach ($statStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $status = (string) ($row['status'] ?? '');
            if (array_key_exists($status, $stats)) {
                $stats[$status] = (int) ($row['cnt'] ?? 0);
            }
        }

        $jobsStmt = db()->query("\n            SELECT id, queue_name, job_type, attempts, max_attempts, status, last_error, available_at, reserved_at, finished_at, created_at\n            FROM jobs_queue\n            ORDER BY id DESC\n            LIMIT 50\n        ");
        $jobs = $jobsStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (Throwable $e) {
        error_log('queue view page error: ' . $e->getMessage());
    }
}
?>

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">Sistem Yonetimi</div>
                <h2 class="page-title">Jobs Queue</h2>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <?php if (!$queueReady): ?>
            <div class="alert alert-warning">
                Queue tablosu kurulu degil. Kurulum icin setup veya db:install calistirin.
            </div>
        <?php endif; ?>

        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3"><div class="card card-body"><div class="text-secondary small">Queued</div><div class="h2 mb-0"><?php echo (int) $stats['queued']; ?></div></div></div>
            <div class="col-6 col-md-3"><div class="card card-body"><div class="text-secondary small">Processing</div><div class="h2 mb-0"><?php echo (int) $stats['processing']; ?></div></div></div>
            <div class="col-6 col-md-3"><div class="card card-body"><div class="text-secondary small">Completed</div><div class="h2 mb-0"><?php echo (int) $stats['completed']; ?></div></div></div>
            <div class="col-6 col-md-3"><div class="card card-body"><div class="text-secondary small">Failed</div><div class="h2 mb-0 text-red"><?php echo (int) $stats['failed']; ?></div></div></div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title">Queue Islemleri</h3>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12 col-lg-6">
                        <form action="<?php echo base_url('queue/actions/enqueue-test-mail'); ?>" method="post" data-ajax="true">
                            <input type="hidden" name="csrf_token" value="<?php echo e(get_csrf_token()); ?>">
                            <div class="row g-2">
                                <div class="col-12">
                                    <label class="form-label">Test Mail Alici</label>
                                    <input type="email" name="recipient_email" class="form-control" required value="<?php echo e((string) (current_user()['email'] ?? '')); ?>" <?php echo !$queueReady ? 'disabled' : ''; ?>>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary" <?php echo !$queueReady ? 'disabled' : ''; ?>>Mail Job Kuyruga Ekle</button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="col-12 col-lg-6">
                        <div class="d-flex gap-2 flex-wrap">
                            <form action="<?php echo base_url('queue/actions/work-once'); ?>" method="post" data-ajax="true">
                                <input type="hidden" name="csrf_token" value="<?php echo e(get_csrf_token()); ?>">
                                <button type="submit" class="btn btn-outline-primary" <?php echo !$queueReady ? 'disabled' : ''; ?>>Worker Run Once</button>
                            </form>

                            <form action="<?php echo base_url('queue/actions/retry-failed'); ?>" method="post" data-ajax="true">
                                <input type="hidden" name="csrf_token" value="<?php echo e(get_csrf_token()); ?>">
                                <button type="submit" class="btn btn-outline-warning" <?php echo !$queueReady ? 'disabled' : ''; ?>>Failed Joblari Retry</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Son 50 Job</h3>
            </div>
            <div class="table-responsive">
                <table class="table table-vcenter card-table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Queue</th>
                            <th>Type</th>
                            <th>Attempts</th>
                            <th>Status</th>
                            <th>Error</th>
                            <th>Tarih</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($jobs)): ?>
                        <tr><td colspan="7" class="text-secondary text-center py-4">Kayit bulunamadi.</td></tr>
                    <?php else: ?>
                        <?php foreach ($jobs as $job): ?>
                            <tr>
                                <td><?php echo (int) ($job['id'] ?? 0); ?></td>
                                <td><code><?php echo e((string) ($job['queue_name'] ?? '')); ?></code></td>
                                <td><code><?php echo e((string) ($job['job_type'] ?? '')); ?></code></td>
                                <td><?php echo (int) ($job['attempts'] ?? 0); ?> / <?php echo (int) ($job['max_attempts'] ?? 0); ?></td>
                                <td><span class="badge <?php echo ($job['status'] ?? '') === 'failed' ? 'bg-red-lt' : (($job['status'] ?? '') === 'completed' ? 'bg-green-lt' : 'bg-blue-lt'); ?>"><?php echo e((string) ($job['status'] ?? '')); ?></span></td>
                                <td class="text-secondary"><?php echo e((string) ($job['last_error'] ?? '')); ?></td>
                                <td><?php echo e((string) ($job['created_at'] ?? '')); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
