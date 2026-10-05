<?php
use App\Core\Session;

$title = 'Dashboard Pegawai';

ob_start();
?>

<h3>Dashboard Pegawai</h3>
<p class="text-muted">
    Halo, <?= htmlspecialchars(Session::get('nama', ''), ENT_QUOTES, 'UTF-8') ?>.
    Dashboard lengkap dibuat di Tahap 12.
</p>

<?php
$content = ob_get_clean();

require dirname(__DIR__) . '/layouts/pegawai.php';