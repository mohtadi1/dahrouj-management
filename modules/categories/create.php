<?php
require_once '../../includes/config.php';
requireManager();

$db = getDB();
$error = '';

// Get all categories for parent selection
$parentCategories = $db->query("SELECT id, name FROM categories WHERE status = 'active' ORDER BY name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $parent_id = !empty($_POST['parent_id']) ? intval($_POST['parent_id']) : null;
    $status = $_POST['status'] ?? 'active';
    
    if (empty($name)) {
        $error = 'Le nom de la catégorie est obligatoire.';
    } else {
        $stmt = $db->prepare("INSERT INTO categories (name, description, parent_id, status) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$name, $description, $parent_id, $status])) {
            $newId = $db->lastInsertId();
            logActivity('Création catégorie', 'category', $newId);
            setFlashMessage('success', 'Catégorie créée avec succès.');
            redirect('index.php');
        } else {
            $error = 'Erreur lors de la création de la catégorie.';
        }
    }
}

$pageTitle = 'Nouvelle Catégorie';
$currentPage = 'categories';
require_once '../../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-tags"></i> Nouvelle Catégorie</h1>
        <nav class="breadcrumb">
            <a href="../../index.php">Accueil</a>
            <i class="fas fa-chevron-right"></i>
            <a href="index.php">Catégories</a>
            <i class="fas fa-chevron-right"></i>
            <span>Nouveau</span>
        </nav>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Informations de la Catégorie</h3>
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
                    <label for="name">Nom de la catégorie <span class="text-danger">*</span></label>
                    <input type="text" id="name" name="name" class="form-control" required 
                           placeholder="Ex: Tissus, Fil, Accessoires...">
                </div>
                
                <div class="form-group">
                    <label for="parent_id">Catégorie Parent</label>
                    <select id="parent_id" name="parent_id" class="form-control">
                        <option value="">-- Aucune --</option>
                        <?php foreach ($parentCategories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>"><?php echo $cat['name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" class="form-control" rows="3"
                          placeholder="Description de la catégorie..."></textarea>
            </div>
            
            <div class="form-group">
                <label for="status">Statut</label>
                <select id="status" name="status" class="form-control">
                    <option value="active">Actif</option>
                    <option value="inactive">Inactif</option>
                </select>
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
