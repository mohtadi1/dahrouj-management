<?php
require_once '../../includes/config.php';
requireLogin();

$db = getDB();
$error = '';

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    redirect('index.php');
}

// Get customer
$stmt = $db->prepare("SELECT * FROM customers WHERE id = ?");
$stmt->execute([$id]);
$customer = $stmt->fetch();

if (!$customer) {
    setFlashMessage('error', 'Client non trouvé.');
    redirect('index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $company_name = sanitize($_POST['company_name'] ?? '');
    $contact_name = sanitize($_POST['contact_name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $phone2 = sanitize($_POST['phone2'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $city = sanitize($_POST['city'] ?? '');
    $country = sanitize($_POST['country'] ?? 'Tunisia');
    $tax_number = sanitize($_POST['tax_number'] ?? '');
    $registration_number = sanitize($_POST['registration_number'] ?? '');
    $credit_limit = floatval($_POST['credit_limit'] ?? 0);
    $notes = sanitize($_POST['notes'] ?? '');
    $status = $_POST['status'] ?? 'active';
    
    if (empty($contact_name) && empty($company_name)) {
        $error = 'Le nom du contact ou le nom de la société est obligatoire.';
    } else {
        $stmt = $db->prepare("UPDATE customers SET company_name = ?, contact_name = ?, email = ?, phone = ?, phone2 = ?, address = ?, city = ?, country = ?, tax_number = ?, registration_number = ?, credit_limit = ?, notes = ?, status = ? WHERE id = ?");
        if ($stmt->execute([$company_name, $contact_name, $email, $phone, $phone2, $address, $city, $country, $tax_number, $registration_number, $credit_limit, $notes, $status, $id])) {
            logActivity('Modification client', 'customer', $id);
            setFlashMessage('success', 'Client mis à jour avec succès.');
            redirect('index.php');
        } else {
            $error = 'Erreur lors de la mise à jour du client.';
        }
    }
}

$pageTitle = 'Modifier Client';
$currentPage = 'customers';
require_once '../../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-users"></i> Modifier Client</h1>
        <nav class="breadcrumb">
            <a href="../../index.php">Accueil</a>
            <i class="fas fa-chevron-right"></i>
            <a href="index.php">Clients</a>
            <i class="fas fa-chevron-right"></i>
            <span>Modifier</span>
        </nav>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Informations du Client</h3>
    </div>
    <div class="card-body">
        <?php if ($error): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
        </div>
        <?php endif; ?>
        
        <form method="POST" action="" data-validate>
            <div class="form-row">
                <div class="form-group">
                    <label for="company_name">Nom de la société</label>
                    <input type="text" id="company_name" name="company_name" class="form-control" 
                           value="<?php echo $customer['company_name']; ?>">
                </div>
                
                <div class="form-group">
                    <label for="contact_name">Nom du contact</label>
                    <input type="text" id="contact_name" name="contact_name" class="form-control" 
                           value="<?php echo $customer['contact_name']; ?>">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control" 
                           value="<?php echo $customer['email']; ?>">
                </div>
                
                <div class="form-group">
                    <label for="phone">Téléphone</label>
                    <input type="tel" id="phone" name="phone" class="form-control" 
                           value="<?php echo $customer['phone']; ?>">
                </div>
                
                <div class="form-group">
                    <label for="phone2">Téléphone 2</label>
                    <input type="tel" id="phone2" name="phone2" class="form-control" 
                           value="<?php echo $customer['phone2']; ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label for="address">Adresse</label>
                <textarea id="address" name="address" class="form-control" rows="2"><?php echo $customer['address']; ?></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="city">Ville</label>
                    <input type="text" id="city" name="city" class="form-control" 
                           value="<?php echo $customer['city']; ?>">
                </div>
                
                <div class="form-group">
                    <label for="country">Pays</label>
                    <input type="text" id="country" name="country" class="form-control" 
                           value="<?php echo $customer['country']; ?>">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="tax_number">Matricule fiscal</label>
                    <input type="text" id="tax_number" name="tax_number" class="form-control" 
                           value="<?php echo $customer['tax_number']; ?>">
                </div>
                
                <div class="form-group">
                    <label for="registration_number">Registre de commerce</label>
                    <input type="text" id="registration_number" name="registration_number" class="form-control" 
                           value="<?php echo $customer['registration_number']; ?>">
                </div>
                
                <div class="form-group">
                    <label for="credit_limit">Limite de crédit (DT)</label>
                    <input type="number" id="credit_limit" name="credit_limit" class="form-control" 
                           step="0.001" min="0" value="<?php echo $customer['credit_limit']; ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label for="notes">Notes</label>
                <textarea id="notes" name="notes" class="form-control" rows="3"><?php echo $customer['notes']; ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="status">Statut</label>
                <select id="status" name="status" class="form-control">
                    <option value="active" <?php echo $customer['status'] === 'active' ? 'selected' : ''; ?>>Actif</option>
                    <option value="inactive" <?php echo $customer['status'] === 'inactive' ? 'selected' : ''; ?>>Inactif</option>
                </select>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Mettre à jour
                </button>
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Annuler
                </a>
            </div>
        </form>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
