<?php
require_once '../../includes/config.php';
requireLogin();

$db = getDB();
$error = '';

$id = intval($_GET['id'] ?? 0);
if (!$id) redirect('index.php');

$stmt = $db->prepare("SELECT * FROM partners WHERE id = ?");
$stmt->execute([$id]);
$partner = $stmt->fetch();

if (!$partner) {
    setFlashMessage('error', 'Fournisseur non trouvé.');
    redirect('index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $company_name = sanitize($_POST['company_name'] ?? '');
    $contact_name = sanitize($_POST['contact_name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $city = sanitize($_POST['city'] ?? '');
    $partner_type = $_POST['partner_type'] ?? 'supplier';
    $status = $_POST['status'] ?? 'active';
    $notes = sanitize($_POST['notes'] ?? '');
    
    if (empty($company_name)) {
        $error = 'Le nom de la société est obligatoire.';
    } else {
        $stmt = $db->prepare("UPDATE partners SET company_name = ?, contact_name = ?, email = ?, phone = ?, address = ?, city = ?, partner_type = ?, status = ?, notes = ? WHERE id = ?");
        if ($stmt->execute([$company_name, $contact_name, $email, $phone, $address, $city, $partner_type, $status, $notes, $id])) {
            logActivity('Modification fournisseur', 'partner', $id);
            setFlashMessage('success', 'Fournisseur mis à jour avec succès.');
            redirect('index.php');
        } else {
            $error = 'Erreur lors de la mise à jour.';
        }
    }
}

$pageTitle = 'Modifier Fournisseur';
$currentPage = 'partners';
require_once '../../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-handshake"></i> Modifier Fournisseur</h1>
        <nav class="breadcrumb">
            <a href="../../index.php">Accueil</a>
            <i class="fas fa-chevron-right"></i>
            <a href="index.php">Fournisseurs</a>
            <i class="fas fa-chevron-right"></i>
            <span>Modifier</span>
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
                    <input type="text" id="company_name" name="company_name" class="form-control" value="<?php echo $partner['company_name']; ?>" required>
                </div>
                <div class="form-group">
                    <label for="contact_name">Contact</label>
                    <input type="text" id="contact_name" name="contact_name" class="form-control" value="<?php echo $partner['contact_name']; ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control" value="<?php echo $partner['email']; ?>">
                </div>
                <div class="form-group">
                    <label for="phone">Téléphone</label>
                    <input type="tel" id="phone" name="phone" class="form-control" value="<?php echo $partner['phone']; ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="address">Adresse</label>
                    <textarea id="address" name="address" class="form-control" rows="2"><?php echo $partner['address']; ?></textarea>
                </div>
                <div class="form-group">
                    <label for="city">Ville</label>
                    <input type="text" id="city" name="city" class="form-control" value="<?php echo $partner['city']; ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="partner_type">Type</label>
                    <select id="partner_type" name="partner_type" class="form-control">
                        <option value="supplier" <?php echo $partner['partner_type'] === 'supplier' ? 'selected' : ''; ?>>Fournisseur</option>
                        <option value="transporter" <?php echo $partner['partner_type'] === 'transporter' ? 'selected' : ''; ?>>Transporteur</option>
                        <option value="other" <?php echo $partner['partner_type'] === 'other' ? 'selected' : ''; ?>>Autre</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="status">Statut</label>
                    <select id="status" name="status" class="form-control">
                        <option value="active" <?php echo $partner['status'] === 'active' ? 'selected' : ''; ?>>Actif</option>
                        <option value="inactive" <?php echo $partner['status'] === 'inactive' ? 'selected' : ''; ?>>Inactif</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label for="notes">Notes</label>
                <textarea id="notes" name="notes" class="form-control" rows="3"><?php echo $partner['notes']; ?></textarea>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Mettre à jour</button>
                <a href="index.php" class="btn btn-secondary"><i class="fas fa-times"></i> Annuler</a>
            </div>
        </form>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
