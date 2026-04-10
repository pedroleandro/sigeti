<?= $this->layout('teacher/app', [
        'title' => $title ?? "Professor | Perfil - " . APP_NAME,
        "menuActive" => "conta",
        "submenuActive" => "perfil"
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
                    <h3>Perfil</h3>
                    <p class="text-subtitle text-muted">Aqui você pode alterar as informações do seu perfil</p>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?= url('/entrar') ?>">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Perfil</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <?= \App\Core\Message::render() ?>

        <section class="section">
            <div class="row">

                <!-- Card do Avatar -->
                <div class="col-12 col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-center align-items-center flex-column">
                                <img src="<?= assets_mazer('/assets/compiled/jpg/2.jpg') ?>"
                                     alt="Avatar"
                                     class="rounded-circle"
                                     style="width: 300px; height: 300px; object-fit: cover; border: 4px solid #435ebe;">
                                <h3 class="mt-3"><?= $user->getName() ?></h3>
                                <p class="text-small text-muted"><?= ucfirst($user->getRole()) ?? \App\Models\User::TEACHER ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card do Formulário -->
                <div class="col-12 col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Informações Pessoais</h4>
                        </div>
                        <div class="card-body">
                            <form action="<?= url('/professor/perfil') ?>" method="post">

                                <?= csrf_input() ?>

                                <!-- Nome Completo -->
                                <div class="form-group">
                                    <label for="name" class="form-label">Nome Completo</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="bi bi-person-fill"></i>
                                        </span>
                                        <input type="text" name="name" id="name"
                                               class="form-control"
                                               placeholder="Nome Completo"
                                               value="<?= old('name', $user->getName()) ?>">
                                    </div>
                                </div>

                                <!-- Email (desabilitado) -->
                                <div class="form-group">
                                    <label for="email" class="form-label">Email</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="bi bi-envelope-fill"></i>
                                        </span>
                                        <input disabled type="email" name="email" id="email"
                                               class="form-control"
                                               placeholder="Seu melhor email"
                                               value="<?= old('email', $user->getEmail()) ?>">
                                    </div>
                                    <small class="text-muted">O email não pode ser alterado.</small>
                                </div>

                                <!-- Telefone -->
                                <div class="form-group">
                                    <label for="phone" class="form-label">Telefone / WhatsApp</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="bi bi-whatsapp"></i>
                                        </span>
                                        <input type="text" name="phone" id="phone"
                                               class="form-control"
                                               value="<?php //old('phone', $user->getPhone()) ?>"
                                               placeholder="(00) 00000-0000">
                                    </div>
                                </div>

                                <!-- Data de Nascimento -->
                                <div class="form-group">
                                    <label for="birthday" class="form-label">Data de Nascimento</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="bi bi-calendar-fill"></i>
                                        </span>
                                        <input type="date" name="birthday" id="birthday"
                                               class="form-control"
                                               value="<?php //old('birthday', $user->getBirthday()) ?>">
                                    </div>
                                </div>

                                <!-- Gênero -->
                                <div class="form-group">
                                    <label for="gender" class="form-label">Gênero</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="bi bi-gender-ambiguous"></i>
                                        </span>
                                        <select name="gender" id="gender" class="form-select">
                                            <option value="" disabled selected>Selecione uma opção</option>
                                            <option value="masculino">Masculino</option>
                                            <option value="feminino">Feminino</option>
                                            <option value="prefiro_nao_dizer">Prefiro não dizer</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Perfil (desabilitado) -->
                                <div class="form-group">
                                    <label for="role" class="form-label">Perfil</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="bi bi-shield-fill"></i>
                                        </span>
                                        <input disabled type="text" name="role" id="role"
                                               class="form-control"
                                               value="<?= $user->getRole() ?>">
                                    </div>
                                    <small class="text-muted">O perfil é definido pelo administrador.</small>
                                </div>

                                <!-- Escola (desabilitado) -->
                                <div class="form-group">
                                    <label for="school" class="form-label">Escola(s)</label>
                                    <div class="input-group">
                                        <?php if ($user->schools()): ?>

                                            <?php foreach ($user->schools() as $school): ?>
                                                <span class="input-group-text">
                                                        <i class="bi bi-building-fill"></i>
                                                    </span>
                                                <input disabled type="text" name="school" id="school"
                                                       class="form-control"
                                                       value="<?= $school->getName() ?>">
                                            <?php endforeach; ?>

                                        <?php else: ?>

                                        <?php endif; ?>
                                    </div>
                                    <small class="text-muted">A escola é definida pelo administrador.</small>
                                </div>

                                <!-- Botão -->
                                <div class="form-group mt-4">
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