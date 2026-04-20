<?php
require_once '../../includes/config.php';
requireManager();

$db = getDB();
$error = '';

// Vérifier l'ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    setFlashMessage('danger', 'ID de dépense invalide.');
    redirect('expenses.php');
}

$id = intval($_GET['id']);

// Récupérer la dépense
$stmt = $db->prepare("SELECT * FROM expenses WHERE id = ?");
$stmt->execute([$id]);
$expense = $stmt->fetch();

if (!$expense) {
    setFlashMessage('danger', 'Dépense introuvable.');
    redirect('expenses.php');
}

// Liste des fournisseurs actifs
$partners = $db->query("SELECT id, company_name FROM partners WHERE status = 'active' ORDER BY company_name ASC")->fetchAll();

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category = sanitize($_POST['category'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $amount = floatval($_POST['amount'] ?? 0);
    $expense_date = $_POST['expense_date'] ?? $expense['expense_date'];
    $payment_method = $_POST['payment_method'] ?? 'cash';
    $partner_id = !empty($_POST['partner_id']) ? intval($_POST['partner_id']) : null;
    $status = $_POST['status'] ?? 'approved';

    if (empty($category) || $amount <= 0 || empty($description)) {
        $error = 'Veuillez remplir tous les champs obligatoires (catégorie, montant, description).';
    } else {
        $update = $db->prepare("UPDATE expenses SET 
            category = ?, 
            description = ?, 
            amount = ?, 
            expense_date = ?, 
            payment_method = ?, 
            partner_id = ?, 
            status = ? 
            WHERE id = ?");
        if ($update->execute([$category, $description, $amount, $expense_date, $payment_method, $partner_id, $status, $id])) {
            logActivity('Modification dépense', 'expense', $id);
            setFlashMessage('success', 'Dépense modifiée avec succès.');
            redirect('expenses.php');
        } else {
            $error = 'Erreur lors de la modification.';
        }
    }
}

$pageTitle = 'Modifier la dépense #' . $expense['expense_number'];
$currentPage = 'expenses';
require_once '../../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-edit"></i> Modifier la dépense</h1>
        <nav class="breadcrumb">
            <a href="../../index.php">Accueil</a>
            <i class="fas fa-chevron-right"></i>
            <a href="expenses.php">Dépenses</a>
            <i class="fas fa-chevron-right"></i>
            <span>Modifier</span>
        </nav>
    </div>
</div>

<div class="card">
    <div class="card-header"><h3 class="card-title">Dépense #<?php echo $expense['expense_number']; ?></h3></div>
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
                        <?php
                        $categories = ['Frais généraux', 'Transport', 'Salaires', 'Loyer', 'Services', 'Marketing', 'Autre'];
                        foreach ($categories as $cat):
                            $selected = ($expense['category'] == $cat) ? 'selected' : '';
                        ?>
                        <option value="<?php echo $cat; ?>" <?php echo $selected; ?>><?php echo $cat; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="amount">Montant (DT) <span class="text-danger">*</span></label>
                    <input type="number" id="amount" name="amount" class="form-control" step="0.001" min="0.001" value="<?php echo $expense['amount']; ?>" required>
                </div>
                <div class="form-group">
                    <label for="expense_date">Date</label>
                    <input type="date" id="expense_date" name="expense_date" class="form-control" value="<?php echo $expense['expense_date']; ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="payment_method">Mode de paiement</label>
                    <select id="payment_method" name="payment_method" class="form-control">
                        <option value="cash" <?php echo $expense['payment_method'] == 'cash' ? 'selected' : ''; ?>>Espèces</option>
                        <option value="check" <?php echo $expense['payment_method'] == 'check' ? 'selected' : ''; ?>>Chèque</option>
                        <option value="transfer" <?php echo $expense['payment_method'] == 'transfer' ? 'selected' : ''; ?>>Virement</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="partner_id">Fournisseur (optionnel)</label>
                    <select id="partner_id" name="partner_id" class="form-control">
                        <option value="">-- Aucun --</option>
                        <?php foreach ($partners as $p): ?>
                        <option value="<?php echo $p['id']; ?>" <?php echo ($expense['partner_id'] == $p['id']) ? 'selected' : ''; ?>><?php echo $p['company_name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="status">Statut</label>
                    <select id="status" name="status" class="form-control">
                        <option value="approved" <?php echo $expense['status'] == 'approved' ? 'selected' : ''; ?>>Approuvé</option>
                        <option value="pending" <?php echo $expense['status'] == 'pending' ? 'selected' : ''; ?>>En attente</option>
                        <option value="cancelled" <?php echo $expense['status'] == 'cancelled' ? 'selected' : ''; ?>>Annulé</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label for="description">Description <span class="text-danger">*</span></label>
                <textarea id="description" name="description" class="form-control" rows="3" required><?php echo htmlspecialchars($expense['description']); ?></textarea>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Enregistrer</button>
                <a href="expenses.php" class="btn btn-secondary"><i class="fas fa-times"></i> Annuler</a>
                <a href="expense_view.php?id=<?php echo $id; ?>" class="btn btn-info"><i class="fas fa-eye"></i> Voir</a>
            </div>
        </form>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>