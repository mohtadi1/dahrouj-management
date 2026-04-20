<?php
require_once '../../includes/config.php';
requireManager();

$db = getDB();
$error = '';

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    redirect('index.php');
}

// Get category
$stmt = $db->prepare("SELECT * FROM categories WHERE id = ?");
$stmt->execute([$id]);
$category = $stmt->fetch();

if (!$category) {
    setFlashMessage('error', 'Catégorie non trouvée.');
    redirect('index.php');
}

// Get all categories for parent selection (excluding current and its children)
$parentCategories = $db->prepare("SELECT id, name FROM categories WHERE status = 'active' AND id != ? AND parent_id != ? ORDER BY name ASC");
$parentCategories->execute([$id, $id]);
$parentCategories = $parentCategories->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $parent_id = !empty($_POST['parent_id']) ? intval($_POST['parent_id']) : null;
    $status = $_POST['status'] ?? 'active';
    
    if (empty($name)) {
        $error = 'Le nom de la catégorie est obligatoire.';
    } else {
        $stmt = $db->prepare("UPDATE categories SET name = ?, description = ?, parent_id = ?, status = ? WHERE id = ?");
        if ($stmt->execute([$name, $description, $parent_id, $status, $id])) {
            logActivity('Modification catégorie', 'category', $id);
            setFlashMessage('success', 'Catégorie mise à jour avec succès.');
            redirect('index.php');
        } else {
            $error = 'Erreur lors de la mise à jour de la catégorie.';
        }
    }
}

$pageTitle = 'Modifier Catégorie';
$currentPage = 'categories';
require_once '../../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-tags"></i> Modifier Catégorie</h1>
        <nav class="breadcrumb">
            <a href="../../index.php">Accueil</a>
            <i class="fas fa-chevron-right"></i>
            <a href="index.php">Catégories</a>
            <i class="fas fa-chevron-right"></i>
            <span>Modifier</span>
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
                           value="<?php echo $category['name']; ?>">
                </div>
                
                <div class="form-group">
                    <label for="parent_id">Catégorie Parent</label>
                    <select id="parent_id" name="parent_id" class="form-control">
                        <option value="">-- Aucune --</option>
                        <?php foreach ($parentCategories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo $category['parent_id'] == $cat['id'] ? 'selected' : ''; ?>>
                            <?php echo $cat['name']; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" class="form-control" rows="3"><?php echo $category['description']; ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="status">Statut</label>
                <select id="status" name="status" class="form-control">
                    <option value="active" <?php echo $category['status'] === 'active' ? 'selected' : ''; ?>>Actif</option>
                    <option value="inactive" <?php echo $category['status'] === 'inactive' ? 'selected' : ''; ?>>Inactif</option>
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
