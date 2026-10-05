<?php
use App\Core\Session;
use App\Helpers\CsrfHelper;

$errors = Session::flash('errors') ?? [];
$old    = Session::flash('old') ?? [];

$title = 'Login';

ob_start();
?>

<div class="row justify-content-center">
    <div class="col-md-5 col-lg-4">
        <div class="card shadow-sm">
            <div class="card-body p-4">

                <h4 class="mb-1">Login</h4>
                <p class="text-muted small mb-4">Khusus Owner dan Pegawai Studio Flamboyan.</p>

                <form action="/login" method="POST" novalidate>
                    <?= CsrfHelper::field() ?>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                            value="<?= htmlspecialchars($old['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                            autofocus
                        >
                        <?php if (isset($errors['email'])): ?>
                            <div class="invalid-feedback">
                                <?= htmlspecialchars($errors['email'], ENT_QUOTES, 'UTF-8') ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                            >

                            <button 
                                type="button"
                                class="btn btn-outline-secondary"
                                id="togglePassword"
                                aria-label="Tampilkan password"
                            >
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <?php if (isset($errors['password'])): ?>
                            <div class="invalid-feedback">
                                <?= htmlspecialchars($errors['password'], ENT_QUOTES, 'UTF-8') ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Masuk</button>
                </form>

            </div>
        </div>
    </div>
</div>

<script>
const togglePassword = document.querySelector('#togglePassword');
const password = document.querySelector('#password');
const icon = togglePassword.querySelector('i');

togglePassword.addEventListener('click', function () {

    const isPassword = password.type === 'password';

    password.type = isPassword ? 'text' : 'password';

    icon.classList.toggle('bi-eye');
    icon.classList.toggle('bi-eye-slash');

});
</script>

<?php
$content = ob_get_clean();

require dirname(__DIR__) . '/layouts/guest.php';