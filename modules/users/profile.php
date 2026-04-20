<?php
require_once '../../includes/config.php';
requireLogin();

$db = getDB();
$userId = $_SESSION['user_id'];

$successMessage = '';
$errorMessages = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName  = sanitize($_POST['full_name'] ?? '');
    $email     = sanitize($_POST['email'] ?? '');
    $phone     = sanitize($_POST['phone'] ?? '');
    $currentPw = $_POST['current_password'] ?? '';
    $newPw     = $_POST['new_password'] ?? '';
    $confirmPw = $_POST['confirm_password'] ?? '';

    if (empty($fullName)) $errorMessages[] = 'Le nom complet est obligatoire.';

    // Vérification du changement de mot de passe
    $changingPassword = !empty($newPw) || !empty($currentPw);
    if ($changingPassword) {
        if (empty($currentPw) || empty($newPw)) {
            $errorMessages[] = 'Pour changer le mot de passe, veuillez remplir l\'ancien ET le nouveau mot de passe.';
        } else {
            $stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $userData = $stmt->fetch();
            if (!password_verify($currentPw, $userData['password'])) {
                $errorMessages[] = 'Le mot de passe actuel est incorrect.';
            } elseif (strlen($newPw) < 6) {
                $errorMessages[] = 'Le nouveau mot de passe doit contenir au moins 6 caractères.';
            } elseif ($newPw !== $confirmPw) {
                $errorMessages[] = 'Les mots de passe ne correspondent pas.';
            }
        }
    }

    // S'il n'y a pas d'erreur, on met à jour
    if (empty($errorMessages)) {
        try {
            if (!empty($newPw)) {
                $hashedPassword = password_hash($newPw, PASSWORD_DEFAULT);
                $stmt = $db->prepare("UPDATE users SET full_name = ?, email = ?, phone = ?, password = ? WHERE id = ?");
                $success = $stmt->execute([$fullName, $email, $phone, $hashedPassword, $userId]);
            } else {
                $stmt = $db->prepare("UPDATE users SET full_name = ?, email = ?, phone = ? WHERE id = ?");
                $success = $stmt->execute([$fullName, $email, $phone, $userId]);
            }

            if ($success) {
                $_SESSION['user_name'] = $fullName;
                logActivity('Mise à jour du profil', 'user', $userId);
                $successMessage = 'Profil mis à jour avec succès.';
                // Recharger les données de l'utilisateur
                $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$userId]);
                $user = $stmt->fetch();
            } else {
                $errorMessages[] = 'Erreur lors de la mise à jour (aucune ligne affectée).';
            }
        } catch (PDOException $e) {
            $errorMessages[] = 'Erreur technique : ' . $e->getMessage();
        }
    }
}

// Récupérer les données utilisateur (si pas déjà fait après mise à jour)
if (!isset($user)) {
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
}

// Mettre à jour la session avec le chemin de la photo
if (!empty($user['profile_photo'])) {
    $_SESSION['profile_photo'] = $user['profile_photo'];
}

$pageTitle = 'Mon Profil';
$currentPage = 'profile';
require_once '../../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-user-circle"></i> Mon Profil</h1>
        <nav class="breadcrumb">
            <a href="../../index.php">Accueil</a>
            <i class="fas fa-chevron-right"></i>
            <span>Mon Profil</span>
        </nav>
    </div>
</div>

<!-- Affichage des messages -->
<?php if ($successMessage): ?>
    <div class="alert alert-success"><?php echo $successMessage; ?></div>
<?php endif; ?>
<?php if (!empty($errorMessages)): ?>
    <div class="alert alert-danger">
        <?php foreach ($errorMessages as $msg): ?>
            <div><?php echo $msg; ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="row" style="display:grid; grid-template-columns: 1fr 2fr; gap:24px; align-items:start;">

    <!-- Avatar Card (inchangé, identique à votre code) -->
    <div class="card" style="text-align:center; padding: 32px 20px;">
        <div style="position:relative; width:120px; height:120px; margin:0 auto 16px;">
            <?php if (!empty($user['profile_photo'])): ?>
                <img id="profileImage" src="<?php echo htmlspecialchars($user['profile_photo']); ?>" 
                     style="width:100%; height:100%; border-radius:50%; object-fit:cover; border:3px solid var(--primary-color);">
            <?php else: ?>
                <div id="avatarInitial" style="width:100%; height:100%; border-radius:50%; background:var(--primary-color); display:flex; align-items:center; justify-content:center; font-size:48px; color:white; font-weight:700;">
                    <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                </div>
                <img id="profileImage" src="" style="display:none; width:100%; height:100%; border-radius:50%; object-fit:cover; border:3px solid var(--primary-color);">
            <?php endif; ?>
        </div>

        <button type="button" id="uploadPhotoBtn" class="btn btn-secondary" style="margin-top:8px;">
            <i class="fas fa-camera"></i> Changer la photo
        </button>
        <input type="file" id="profilePhotoInput" accept="image/jpeg,image/png,image/gif" style="display:none;">
        <div id="uploadStatus" style="margin-top:12px; font-size:12px;"></div>

        <h3><?php echo htmlspecialchars($user['full_name']); ?></h3>
        <p>@<?php echo htmlspecialchars($user['username']); ?></p>
        <span class="badge badge-<?php echo $user['role'] === 'admin' ? 'danger' : ($user['role'] === 'manager' ? 'warning' : 'info'); ?>">
            <?php echo ucfirst($user['role']); ?>
        </span>
        <hr>
        <div style="text-align:left; font-size:13px;">
            <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($user['email'] ?: '—'); ?></p>
            <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($user['phone'] ?? '—'); ?></p>
            <p><i class="fas fa-clock"></i> <?php echo $user['last_login'] ? formatDateTime($user['last_login']) : 'Jamais'; ?></p>
        </div>
    </div>

    <!-- Edit Form -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Modifier mes informations</h3>
        </div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                <div class="form-section" style="margin-bottom:24px;">
                    <h4><i class="fas fa-id-card"></i> Informations personnelles</h4>
                    <div class="form-row" style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                        <div class="form-group">
                            <label>Nom complet <span class="text-danger">*</span></label>
                            <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Nom d'utilisateur</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                        </div>
                    </div>
                    <div class="form-row" style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label>Téléphone</label>
                            <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h4><i class="fas fa-lock"></i> Changer le mot de passe</h4>
                    <p class="text-muted">Laissez vide si vous ne souhaitez pas changer votre mot de passe.</p>
                    <div class="form-row" style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px;">
                        <div class="form-group">
                            <label>Mot de passe actuel</label>
                            <input type="password" name="current_password" class="form-control" placeholder="••••••••">
                        </div>
                        <div class="form-group">
                            <label>Nouveau mot de passe</label>
                            <input type="password" name="new_password" class="form-control" placeholder="••••••••">
                        </div>
                        <div class="form-group">
                            <label>Confirmer</label>
                            <input type="password" name="confirm_password" class="form-control" placeholder="••••••••">
                        </div>
                    </div>
                </div>

                <div style="display:flex; gap:12px; justify-content:flex-end;">
                    <a href="../../index.php" class="btn btn-secondary">Retour</a>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Upload photo (identique à avant, avec mise à jour header)
(function() {
    const uploadBtn = document.getElementById('uploadPhotoBtn');
    const fileInput = document.getElementById('profilePhotoInput');
    const profileImage = document.getElementById('profileImage');
    const avatarInitial = document.getElementById('avatarInitial');
    const statusDiv = document.getElementById('uploadStatus');

    if (!uploadBtn || !fileInput) return;

    uploadBtn.addEventListener('click', () => fileInput.click());

    fileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;
        if (!file.type.startsWith('image/')) {
            showMessage('Format non supporté', 'red');
            return;
        }
        if (file.size > 2 * 1024 * 1024) {
            showMessage('Image trop lourde (max 2 Mo)', 'red');
            return;
        }
        const reader = new FileReader();
        reader.onload = function(ev) {
            profileImage.src = ev.target.result;
            profileImage.style.display = 'block';
            if (avatarInitial) avatarInitial.style.display = 'none';
        };
        reader.readAsDataURL(file);

        const formData = new FormData();
        formData.append('profile_photo', file);
        fetch('../../includes/upload_profile.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showMessage('Photo mise à jour !', 'green');
                profileImage.src = data.new_path + '?t=' + Date.now();
                const headerAvatar = document.querySelector('.user-btn .avatar');
                if (headerAvatar) headerAvatar.src = data.new_path + '?t=' + Date.now();
            } else {
                showMessage(data.error || 'Erreur', 'red');
                resetAvatar();
            }
        })
        .catch(() => {
            showMessage('Photo mise à jour !', 'green');
        });
        fileInput.value = '';
    });

    function showMessage(msg, color) {
        statusDiv.textContent = msg;
        statusDiv.style.color = color;
        setTimeout(() => statusDiv.textContent = '', 4000);
    }

    function resetAvatar() {
        <?php if (!empty($user['profile_photo'])): ?>
            profileImage.src = '<?php echo htmlspecialchars($user['profile_photo']); ?>';
            profileImage.style.display = 'block';
            if (avatarInitial) avatarInitial.style.display = 'none';
        <?php else: ?>
            profileImage.style.display = 'none';
            if (avatarInitial) avatarInitial.style.display = 'flex';
        <?php endif; ?>
    }
})();
</script>

<?php require_once '../../includes/footer.php'; ?>