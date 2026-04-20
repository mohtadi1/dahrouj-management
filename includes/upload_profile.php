<?php
// upload_profile.php
session_start();
header('Content-Type: application/json'); // FORCE la réponse JSON

require_once 'config.php'; // contient getDB(), isLoggedIn(), etc.

// Vérification de l'authentification
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Non authentifié']);
    exit;
}

$userId = $_SESSION['user_id'];
$db = getDB();

// Détermination du chemin absolu vers le dossier d'upload
// Le projet est dans un sous-dossier (ex: /DAHROUJ-MANAGEMENT/)
// On utilise DOCUMENT_ROOT + le chemin relatif depuis la racine du serveur
$projectRoot = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'); // ex: /DAHROUJ-MANAGEMENT
$uploadDir = $_SERVER['DOCUMENT_ROOT'] . $projectRoot . '/uploads/profiles/';
$webPathBase = $projectRoot . '/uploads/profiles/';

// Créer le dossier s'il n'existe pas
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['profile_photo'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Requête invalide']);
    exit;
}

$file = $_FILES['profile_photo'];

// Vérifier les erreurs d'upload
if ($file['error'] !== UPLOAD_ERR_OK) {
    $errors = [
        UPLOAD_ERR_INI_SIZE   => 'Fichier trop volumineux (php.ini)',
        UPLOAD_ERR_FORM_SIZE  => 'Fichier trop volumineux (formulaire)',
        UPLOAD_ERR_PARTIAL    => 'Envoi partiel',
        UPLOAD_ERR_NO_FILE    => 'Aucun fichier',
        UPLOAD_ERR_NO_TMP_DIR => 'Dossier temporaire manquant',
        UPLOAD_ERR_CANT_WRITE => 'Échec écriture disque',
        UPLOAD_ERR_EXTENSION  => 'Extension bloquée',
    ];
    $msg = $errors[$file['error']] ?? 'Erreur inconnue';
    echo json_encode(['error' => $msg]);
    exit;
}

// Vérifier le type MIME
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);
$allowed = ['image/jpeg', 'image/png', 'image/gif'];
if (!in_array($mime, $allowed)) {
    echo json_encode(['error' => 'Format non autorisé (JPEG, PNG, GIF uniquement)']);
    exit;
}

// Vérifier la taille (2 Mo max)
if ($file['size'] > 2 * 1024 * 1024) {
    echo json_encode(['error' => 'L\'image ne doit pas dépasser 2 Mo']);
    exit;
}

// Générer un nom unique
$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$newFileName = 'user_' . $userId . '_' . time() . '.' . $ext;
$destination = $uploadDir . $newFileName;
$webPath = $webPathBase . $newFileName;

// Déplacer le fichier
if (!move_uploaded_file($file['tmp_name'], $destination)) {
    echo json_encode(['error' => 'Impossible de sauvegarder l\'image (erreur serveur)']);
    exit;
}

// Supprimer l'ancienne photo si elle existe
$stmt = $db->prepare("SELECT profile_photo FROM users WHERE id = ?");
$stmt->execute([$userId]);
$oldPhoto = $stmt->fetchColumn();
if ($oldPhoto && file_exists($_SERVER['DOCUMENT_ROOT'] . $oldPhoto)) {
    @unlink($_SERVER['DOCUMENT_ROOT'] . $oldPhoto);
}

// Mettre à jour la base de données
$update = $db->prepare("UPDATE users SET profile_photo = ? WHERE id = ?");
$update->execute([$webPath, $userId]);

// Mettre à jour la session
$webPath = '/DAHROUJ-MANAGEMENT/uploads/profiles/' . $newFileName;
$_SESSION['profile_photo'] = $webPath;

// Succès
echo json_encode(['success' => true, 'new_path' => $webPath]);
exit;
?>