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
    $phone2 = sanitize($_POST['phone2'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $city = sanitize($_POST['city'] ?? '');
    $country = sanitize($_POST['country'] ?? 'Tunisia');
    $tax_number = sanitize($_POST['tax_number'] ?? '');
    $registration_number = sanitize($_POST['registration_number'] ?? '');
    $credit_limit = floatval($_POST['credit_limit'] ?? 0);
    $notes = sanitize($_POST['notes'] ?? '');
    
    // Generate code
    $lastId = $db->query("SELECT MAX(id) FROM customers")->fetchColumn() + 1;
    $code = generateCode('CLI', $lastId);
    
    if (empty($contact_name) && empty($company_name)) {
        $error = 'Le nom du contact ou le nom de la société est obligatoire.';
    } else {
        $stmt = $db->prepare("INSERT INTO customers (code, company_name, contact_name, email, phone, phone2, address, city, country, tax_number, registration_number, credit_limit, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$code, $company_name, $contact_name, $email, $phone, $phone2, $address, $city, $country, $tax_number, $registration_number, $credit_limit, $notes])) {
            $newId = $db->lastInsertId();
            logActivity('Création client', 'customer', $newId);
            setFlashMessage('success', 'Client créé avec succès.');
            redirect('index.php');
        } else {
            $error = 'Erreur lors de la création du client.';
        }
    }
}

$pageTitle = 'Nouveau Client';
$currentPage = 'customers';
require_once '../../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-user-plus"></i> Nouveau Client</h1>
        <nav class="breadcrumb">
            <a href="../../index.php">Accueil</a>
            <i class="fas fa-chevron-right"></i>
            <a href="index.php">Clients</a>
            <i class="fas fa-chevron-right"></i>
            <span>Nouveau</span>
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
                           placeholder="Nom de la société">
                </div>
                
                <div class="form-group">
                    <label for="contact_name">Nom du contact</label>
                    <input type="text" id="contact_name" name="contact_name" class="form-control" 
                           placeholder="Nom et prénom du contact">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control" 
                           placeholder="email@exemple.com">
                </div>
                
                <div class="form-group">
                    <label for="phone">Téléphone</label>
                    <input type="tel" id="phone" name="phone" class="form-control" 
                           placeholder="+216 XX XXX XXX">
                </div>
                
                <div class="form-group">
                    <label for="phone2">Téléphone 2</label>
                    <input type="tel" id="phone2" name="phone2" class="form-control" 
                           placeholder="+216 XX XXX XXX">
                </div>
            </div>
            
            <div class="form-group">
                <label for="address">Adresse</label>
                <textarea id="address" name="address" class="form-control" rows="2"
                          placeholder="Adresse complète..."></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="city">Ville</label>
                    <input type="text" id="city" name="city" class="form-control" 
                           placeholder="Ville">
                </div>
                
                <div class="form-group">
                    <label for="country">Pays</label>
                    <input type="text" id="country" name="country" class="form-control" 
                           value="Tunisia">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="tax_number">Matricule fiscal</label>
                    <input type="text" id="tax_number" name="tax_number" class="form-control" 
                           placeholder="Numéro fiscal">
                </div>
                
                <div class="form-group">
                    <label for="registration_number">Registre de commerce</label>
                    <input type="text" id="registration_number" name="registration_number" class="form-control" 
                           placeholder="Numéro de registre">
                </div>
                
                <div class="form-group">
                    <label for="credit_limit">Limite de crédit (DT)</label>
                    <input type="number" id="credit_limit" name="credit_limit" class="form-control" 
                           step="0.001" min="0" placeholder="0.000">
                </div>
            </div>
            
            <div class="form-group">
                <label for="notes">Notes</label>
                <textarea id="notes" name="notes" class="form-control" rows="3"
                          placeholder="Notes additionnelles..."></textarea>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Enregistrer
                </button>
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Annuler
                </a>
            </div>
        </form>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
