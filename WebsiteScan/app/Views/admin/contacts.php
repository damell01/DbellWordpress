<div class="admin-card">
    <div class="admin-card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Service</th>
                        <th>Message</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($contactsData['items'] ?? [] as $cr): ?>
                    <tr>
                        <td class="text-muted small"><?= $cr['id'] ?></td>
                        <td class="small"><?= e($cr['name']) ?></td>
                        <td class="small"><a href="mailto:<?= e($cr['email']) ?>"><?= e($cr['email']) ?></a></td>
                        <td class="small"><?= e($cr['service_type'] ?: '—') ?></td>
                        <td class="small text-truncate" style="max-width:200px"><?= e($cr['message']) ?></td>
                        <td class="text-muted small"><?= timeAgo($cr['created_at']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($contactsData['items'])): ?>
                    <tr><td colspan="6" class="text-center py-4 text-muted">No contact requests yet</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if (($contactsData['last_page'] ?? 1) > 1): ?>
<nav class="mt-3">
    <ul class="pagination pagination-sm justify-content-center">
        <?php for ($p = 1; $p <= $contactsData['last_page']; $p++): ?>
        <li class="page-item <?= $p === ($contactsData['current_page'] ?? 1) ? 'active' : '' ?>">
            <a class="page-link" href="?page=<?= $p ?>"><?= $p ?></a>
        </li>
        <?php endfor; ?>
    </ul>
</nav>
<?php endif; ?>
