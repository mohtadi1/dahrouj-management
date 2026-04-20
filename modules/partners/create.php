<?php
require_once '../../includes/config.php';
requireLogin();

$db = getDB();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $company_name = sanitize($_POST['company_name'] ?? '');
    $contact_name = sanitize($_POST['contact_name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $city = sanitize($_POST['city'] ?? '');
    $partner_type = $_POST['partner_type'] ?? 'supplier';
    $notes = sanitize($_POST['notes'] ?? '');
    
    if (empty($company_name)) {
        $error = 'Le nom de la société est obligatoire.';
    } else {
        $lastId = $db->query("SELECT MAX(id) FROM partners")->fetchColumn() + 1;
        $code = generateCode('FRS', $lastId);
        
        $stmt = $db->prepare("INSERT INTO partners (code, company_name, contact_name, email, phone, address, city, partner_type, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$code, $company_name, $contact_name, $email, $phone, $address, $city, $partner_type, $notes])) {
            logActivity('Création fournisseur', 'partner', $db->lastInsertId());
            setFlashMessage('success', 'Fournisseur créé avec succès.');
            redirect('index.php');
        } else {
            $error = 'Erreur lors de la création.';
        }
    }
}

$pageTitle = 'Nouveau Fournisseur';
$currentPage = 'partners';
require_once '../../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-handshake"></i> Nouveau Fournisseur</h1>
        <nav class="breadcrumb">
            <a href="../../index.php">Accueil</a>
            <i class="fas fa-chevron-right"></i>
            <a href="index.php">Fournisseurs</a>
            <i class="fas fa-chevron-right"></i>
            <span>Nouveau</span>
        </nav>
    </div>
</div>

<div class="card">
    <div class="card-header"><h3 class="card-title">Informations</h3></div>
    <div class="card-body">
        <?php if ($error): ?>
        <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="" data-validate>
            <div class="form-row">
                <div class="form-group">
                    <label for="company_name">Société <span class="text-danger">*</span></label>
                    <input type="text" id="company_name" name="company_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="contact_name">Contact</label>
                    <input type="text" id="contact_name" name="contact_name" class="form-control">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control">
                </div>
                <div class="form-group">
                    <label for="phone">Téléphone</label>
                    <input type="tel" id="phone" name="phone" class="form-control">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="address">Adresse</label>
                    <textarea id="address" name="address" class="form-control" rows="2"></textarea>
                </div>
                <div class="form-group">
                    <label for="city">Ville</label>
                    <input type="text" id="city" name="city" class="form-control">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="partner_type">Type</label>
                    <select id="partner_type" name="partner_type" class="form-control">
                        <option value="supplier">Fournisseur</option>
                        <option value="transporter">Transporteur</option>
                        <option value="other">Autre</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label for="notes">Notes</label>
                <textarea id="notes" name="notes" class="form-control" rows="3"></textarea>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Enregistrer</button>
                <a href="index.php" class="btn btn-secondary"><i class="fas fa-times"></i> Annuler</a>
            </div>
        </form>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
