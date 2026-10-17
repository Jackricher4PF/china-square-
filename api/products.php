<?php
session_start();
// Enable error reporting for debugging
ini_set('display_errors', 0);
header('Content-Type: application/json');

require_once '../config/db.php';

$method = $_SERVER['REQUEST_METHOD'];

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// GET: Fetch products or search
if ($method === 'GET') {
    try {
        $search = $_GET['search'] ?? '';
        if ($search) {
            $stmt = $pdo->prepare('SELECT * FROM products WHERE title LIKE ? OR category LIKE ? ORDER BY id DESC');
            $stmt->execute(["%$search%", "%$search%"]);
        } else {
            $stmt = $pdo->query('SELECT * FROM products ORDER BY id DESC');
        }
        echo json_encode($stmt->fetchAll());
    } catch (Exception $e) {
        echo json_encode([]);
    }
    exit;
}

// Authorization check for write operations
if (!isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized: Admin privileges required']);
    exit;
}

// DELETE: Remove product
if ($method === 'DELETE') {
    $id = $_GET['id'] ?? null;
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'Product ID required']);
        exit;
    }

    try {
        $stmt = $pdo->prepare('DELETE FROM products WHERE id = ?');
        $stmt->execute([$id]);
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// POST: Create or Update product with File Upload and Sizes
if ($method === 'POST') {
    try {
        $id = $_POST['id'] ?? ($_GET['id'] ?? null);
        $title = $_POST['title'] ?? '';
        $category = $_POST['category'] ?? '';
        $price = $_POST['price'] ?? 0;
        $sizes = $_POST['sizes'] ?? 'S,M,L,XL';
        $description = $_POST['description'] ?? '';
        $imageUrl = '';

        // Handle Image File Upload if provided
        if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['image_file']['tmp_name'];
            $fileName = $_FILES['image_file']['name'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            if (in_array($fileExtension, $allowedExtensions)) {
                $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
                
                // Directory target
                $targetDir = __DIR__ . '/../public/uploads/';
                if (!file_exists($targetDir)) {
                    mkdir($targetDir, 0777, true);
                }

                $destPath = $targetDir . $newFileName;

                if (move_uploaded_file($fileTmpPath, $destPath)) {
                    $imageUrl = 'uploads/' . $newFileName;
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file to uploads directory.']);
                    exit;
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid file format. Allowed: jpg, jpeg, png, webp, gif']);
                exit;
            }
        }

        if ($id && $id !== 'null' && $id !== '') {
            // Update existing product
            if ($imageUrl !== '') {
                $stmt = $pdo->prepare('UPDATE products SET title=?, category=?, price=?, sizes=?, description=?, image_url=? WHERE id=?');
                $stmt->execute([$title, $category, $price, $sizes, $description, $imageUrl, $id]);
            } else {
                $stmt = $pdo->prepare('UPDATE products SET title=?, category=?, price=?, sizes=?, description=? WHERE id=?');
                $stmt->execute([$title, $category, $price, $sizes, $description, $id]);
            }
            echo json_encode(['success' => true, 'message' => 'Product updated successfully!']);
        } else {
            // Create new product
            if ($imageUrl === '') {
                echo json_encode(['success' => false, 'message' => 'Image file is required for new items.']);
                exit;
            }
            
            // Check if sizes column exists in DB, fallback gracefully if not
            try {
                $stmt = $pdo->prepare('INSERT INTO products (title, category, price, sizes, description, image_url) VALUES (?, ?, ?, ?, ?, ?)');
                $stmt->execute([$title, $category, $price, $sizes, $description, $imageUrl]);
            } catch (PDOException $ex) {
                // Fallback if 'sizes' column wasn't added to table structure
                $stmt = $pdo->prepare('INSERT INTO products (title, category, price, description, image_url) VALUES (?, ?, ?, ?, ?)');
                $stmt->execute([$title, $category, $price, $description, $imageUrl]);
            }

            echo json_encode(['success' => true, 'message' => 'Product saved successfully!']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}