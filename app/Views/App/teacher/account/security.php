<?= $this->layout('teacher/app', [
    'title' => $title ?? "Professor | Segurança - " . APP_NAME,
        "menuActive" => "conta",
        "submenuActive" => "seguranca"
]) ?>

<div id="main">
    <header class="mb-3">
        <a href="#" class="burger-btn d-block d-xl-none">
            <i class="bi bi-justify fs-3"></i>
        </a>
    </header>
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-6 order-md-1 order-last">
                    <h3>Segurança</h3>
                    <p class="text-subtitle text-muted">Altere a senha da sua conta</p>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?= url('/entrar') ?>">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Segurança</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <?= \App\Core\Message::render() ?>

        <section class="section">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Alterar Senha</h5>
                        </div>
                        <div class="card-body">
                            <form action="<?= url('/professor/seguranca') ?>" method="post">

                                <?= csrf_input() ?>

                                <!-- Senha Atual -->
                                <div class="form-group my-2">
                                    <label for="current_password" class="form-label">Senha Atual</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="bi bi-lock-fill"></i>
                                        </span>
                                        <input type="password" name="current_password" id="current_password"
                                               class="form-control"
                                               placeholder="Digite sua senha atual">
                                        <button class="btn btn-outline-secondary" type="button" id="toggle-current"
                                                onclick="togglePassword('current_password', 'icon-current')">
                                            <i class="bi bi-eye-fill" id="icon-current"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Nova Senha -->
                                <div class="form-group my-2">
                                    <label for="password" class="form-label">Nova Senha</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="bi bi-key-fill"></i>
                                        </span>
                                        <input type="password" name="password" id="password"
                                               class="form-control"
                                               placeholder="Digite a nova senha">
                                        <button class="btn btn-outline-secondary" type="button"
                                                onclick="togglePassword('password', 'icon-new')">
                                            <i class="bi bi-eye-fill" id="icon-new"></i>
                                        </button>
                                    </div>
                                    <small class="text-muted">A senha deve ter entre 8 e 16 caracteres.</small>
                                </div>

                                <!-- Confirmar Nova Senha -->
                                <div class="form-group my-2">
                                    <label for="confirm_password" class="form-label">Confirmar Nova Senha</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="bi bi-key-fill"></i>
                                        </span>
                                        <input type="password" name="confirm_password" id="confirm_password"
                                               class="form-control"
                                               placeholder="Confirme a nova senha">
                                        <button class="btn btn-outline-secondary" type="button"
                                                onclick="togglePassword('confirm_password', 'icon-confirm')">
                                            <i class="bi bi-eye-fill" id="icon-confirm"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Botão -->
                                <div class="form-group my-4 d-flex justify-content-start">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-circle-fill me-1"></i>
                                        Atualizar
                                    </button>
                                </div>

                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <footer>
        <div class="footer clearfix mb-0 text-muted">
            <div class="float-start">
                <p><?= date('Y') . " - " . APP_NAME ?></p>
            </div>
            <div class="float-end">
                <p>
                    Desenvolvido com
                    <span class="text-danger">
                        <i class="bi bi-heart-fill icon-mid"></i>
                    </span>
                    por
                    <a href="" target="_blank"><?= APP_DEVELOPER ?></a>
                </p>
            </div>
        </div>
    </footer>
</div>

<script>
    function togglePassword(fieldId, iconId) {
        const field = document.getElementById(fieldId);
        const icon  = document.getElementById(iconId);

        if (field.type === 'password') {
            field.type = 'text';
            icon.classList.replace('bi-eye-fill', 'bi-eye-slash-fill');
        } else {
            field.type = 'password';
            icon.classList.replace('bi-eye-slash-fill', 'bi-eye-fill');
        }
    }
</script>