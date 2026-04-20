<?php
require_once '../../includes/config.php';
requireManager();

$db = getDB();
$error = '';

$partners = $db->query("SELECT id, company_name FROM partners WHERE status = 'active' ORDER BY company_name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category = sanitize($_POST['category'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $amount = floatval($_POST['amount'] ?? 0);
    $expense_date = $_POST['expense_date'] ?: date('Y-m-d');
    $payment_method = $_POST['payment_method'] ?? 'cash';
    $partner_id = !empty($_POST['partner_id']) ? intval($_POST['partner_id']) : null;
    
    if (empty($category) || $amount <= 0) {
        $error = 'Veuillez remplir tous les champs obligatoires.';
    } else {
        $lastId = $db->query("SELECT MAX(id) FROM expenses")->fetchColumn() + 1;
        $expense_number = generateCode('DEP', $lastId);
        
        $stmt = $db->prepare("INSERT INTO expenses (expense_number, category, description, amount, expense_date, payment_method, partner_id, user_id, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'approved')");
        if ($stmt->execute([$expense_number, $category, $description, $amount, $expense_date, $payment_method, $partner_id, $_SESSION['user_id']])) {
            logActivity('Création dépense', 'expense', $db->lastInsertId());
            setFlashMessage('success', 'Dépense enregistrée avec succès.');
            redirect('expenses.php');
        } else {
            $error = 'Erreur lors de l\'enregistrement.';
        }
    }
}

$pageTitle = 'Nouvelle Dépense';
$currentPage = 'expenses';
require_once '../../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-money-bill-wave"></i> Nouvelle Dépense</h1>
        <nav class="breadcrumb">
            <a href="../../index.php">Accueil</a>
            <i class="fas fa-chevron-right"></i>
            <a href="expenses.php">Dépenses</a>
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
                    <label for="category">Catégorie <span class="text-danger">*</span></label>
                    <select id="category" name="category" class="form-control" required>
                        <option value="">-- Sélectionner --</option>
                        <option value="Frais généraux">Frais généraux</option>
                        <option value="Transport">Transport</option>
                        <option value="Salaires">Salaires</option>
                        <option value="Loyer">Loyer</option>
                        <option value="Services">Services</option>
                        <option value="Marketing">Marketing</option>
                        <option value="Autre">Autre</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="amount">Montant (DT) <span class="text-danger">*</span></label>
                    <input type="number" id="amount" name="amount" class="form-control" step="0.001" min="0.001" required>
                </div>
                <div class="form-group">
                    <label for="expense_date">Date</label>
                    <input type="date" id="expense_date" name="expense_date" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="payment_method">Mode de paiement</label>
                    <select id="payment_method" name="payment_method" class="form-control">
                        <option value="cash">Espèces</option>
                        <option value="check">Chèque</option>
                        <option value="transfer">Virement</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="partner_id">Fournisseur (optionnel)</label>
                    <select id="partner_id" name="partner_id" class="form-control">
                        <option value="">-- Aucun --</option>
                        <?php foreach ($partners as $p): ?>
                        <option value="<?php echo $p['id']; ?>"><?php echo $p['company_name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" class="form-control" rows="3" required></textarea>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Enregistrer</button>
                <a href="expenses.php" class="btn btn-secondary"><i class="fas fa-times"></i> Annuler</a>
            </div>
        </form>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
