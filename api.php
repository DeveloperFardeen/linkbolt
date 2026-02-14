<?php
header('Content-Type: application/json');
require 'pdo.php';

$action = $_GET['action'] ?? '';

if ($action === 'create') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $pdo->beginTransaction();
    try {
        // Create user if not exists
        $stmt = $pdo->prepare("INSERT IGNORE INTO users (unique_id) VALUES (?)");
        $stmt->execute([$data['user_id']]);

        // Create Bundle
        $stmt = $pdo->prepare("INSERT INTO bundles (user_id, bundle_name, slug) VALUES (?, ?, ?)");
        $stmt->execute([$data['user_id'], $data['name'], $data['slug']]);
        $bundleId = $pdo->lastInsertId();

        // Create Links
        $stmt = $pdo->prepare("INSERT INTO bundle_links (bundle_id, link_title, destination_url) VALUES (?, ?, ?)");
        foreach ($data['links'] as $link) {
            $stmt->execute([$bundleId, $link['title'], $link['url']]);
        }

        $pdo->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

if ($action === 'list') {
    $uid = $_GET['user_id'] ?? '';
    $stmt = $pdo->prepare("SELECT b.*, (SELECT COUNT(*) FROM bundle_links WHERE bundle_id = b.id) as link_count 
                           FROM bundles b WHERE b.user_id = ? ORDER BY b.created_at DESC");
    $stmt->execute([$uid]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    if ($action === 'toggle_pin') {
    $bundleId = $_GET['bundle_id'] ?? '';

    if (empty($bundleId)) {
        echo json_encode(['success' => false, 'error' => 'Bundle ID required']);
        exit;
    }

    $stmt = $pdo->prepare("UPDATE bundles 
                           SET is_pinned = IF(is_pinned=1, 0, 1) 
                           WHERE id = ?");
    $stmt->execute([$bundleId]);

    echo json_encode(['success' => true]);
}

}
