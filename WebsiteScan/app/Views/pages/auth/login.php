<section class="py-6 min-vh-50 d-flex align-items-center bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-5">
                <div class="card border-0 shadow">
                    <div class="card-body p-4 p-md-5 text-center">
                        <i class="bi bi-graph-up-arrow text-primary fs-1 mb-3 d-block"></i>
                <h2 class="fw-bold mb-1"><?= e($appName ?? 'VerityScan') ?></h2>
                        <p class="text-muted mb-4">Admin Login</p>

                        <?php $flashError = get_flash('error'); ?>
                        <?php if ($flashError): ?>
                        <div class="alert alert-danger text-start small"><?= e($flashError) ?></div>
                        <?php endif; ?>

                        <form action="<?= url('admin/login') ?>" method="POST">
                            <?= csrf_field() ?>
                            <div class="mb-3 text-start">
                                <label for="email" class="form-label fw-semibold">Email Address</label>
                                <input type="email" class="form-control form-control-lg" id="email" name="email"
                                       placeholder="admin@yourdomain.com" required autofocus>
                            </div>
                            <div class="mb-4 text-start">
                                <label for="password" class="form-label fw-semibold">Password</label>
                                <input type="password" class="form-control form-control-lg" id="password" name="password"
                                       placeholder="••••••••••" required>
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                            </button>
                        </form>
                    </div>
                </div>
                <p class="text-center text-muted small mt-3">
                    <a href="<?= url('/') ?>" class="text-decoration-none">← Back to website</a>
                </p>
            </div>
        </div>
    </div>
</section>
