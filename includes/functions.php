<?php
require_once __DIR__ . '/../config/config.php';

function getCategories($activeOnly = true) {
    $pdo = getConnection();
    $sql = "SELECT * FROM categories";
    if ($activeOnly) {
        $sql .= " WHERE status = 1";
    }
    $sql .= " ORDER BY sort_order ASC";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
}

function getProducts($categoryId = null, $limit = null, $featured = false, $bestseller = false) {
    $pdo = getConnection();
    $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.status = 1";
    
    $params = [];
    
    if ($categoryId) {
        $sql .= " AND p.category_id = ?";
        $params[] = $categoryId;
    }
    
    if ($featured) {
        $sql .= " AND p.is_featured = true";
    }
    
    if ($bestseller) {
        $sql .= " AND p.is_bestseller = true";
    }
    
    $sql .= " ORDER BY p.sort_order ASC, p.id DESC";
    
    if ($limit) {
        $sql .= " LIMIT " . (int)$limit;
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function getProductBySlug($slug) {
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.slug = ?");
    $stmt->execute([$slug]);
    return $stmt->fetch();
}

function getBranches() {
    return [
        [
            'id' => 1,
            'name' => 'Pizzano Bathinda',
            'slug' => 'bathinda',
            'city' => 'Bathinda',
            'address' => BRANCH_BATHINDA['address'],
            'phone' => BRANCH_BATHINDA['phone'],
            'hours' => BRANCH_BATHINDA['hours'],
            'map_link' => BRANCH_BATHINDA['map_link']
        ],
        [
            'id' => 2,
            'name' => 'Pizzano Dabwali',
            'slug' => 'dabwali',
            'city' => 'Dabwali',
            'address' => BRANCH_DABWALI['address'],
            'phone' => BRANCH_DABWALI['phone'],
            'hours' => BRANCH_DABWALI['hours'],
            'map_link' => BRANCH_DABWALI['map_link']
        ]
    ];
}

function getBanners($position = 'hero') {
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT * FROM banners WHERE position = ? AND status = 1 ORDER BY sort_order ASC");
    $stmt->execute([$position]);
    return $stmt->fetchAll();
}

function getTestimonials($limit = 10) {
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT * FROM testimonials WHERE status = 1 ORDER BY sort_order ASC LIMIT ?");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

function getGallery($type = null, $limit = 12) {
    $pdo = getConnection();
    $sql = "SELECT * FROM gallery WHERE status = 1";
    $params = [];
    
    if ($type) {
        $sql .= " AND type = ?";
        $params[] = $type;
    }
    
    $sql .= " ORDER BY sort_order ASC LIMIT " . (int)$limit;
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function getOffers($activeOnly = true) {
    $pdo = getConnection();
    $sql = "SELECT * FROM offers WHERE status = 1";
    if ($activeOnly) {
        $sql .= " AND (valid_until IS NULL OR valid_until >= CURRENT_DATE)";
    }
    $sql .= " ORDER BY id DESC";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
}

function getHomepageSection($key) {
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT * FROM homepage_sections WHERE section_key = ? AND status = 1");
    $stmt->execute([$key]);
    return $stmt->fetch();
}

function getSeoData($pageSlug) {
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT * FROM seo_pages WHERE page_slug = ?");
    $stmt->execute([$pageSlug]);
    return $stmt->fetch();
}

function getInstagramReels($limit = 6) {
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT * FROM instagram_reels WHERE status = 1 ORDER BY sort_order ASC LIMIT ?");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

function getBlogPosts($limit = 3) {
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE status = 1 ORDER BY created_at DESC LIMIT ?");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

function saveContactInquiry($data) {
    $pdo = getConnection();
    $stmt = $pdo->prepare("INSERT INTO contact_inquiries (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)");
    return $stmt->execute([
        sanitize($data['name']),
        sanitize($data['email']),
        sanitize($data['phone']),
        sanitize($data['subject']),
        sanitize($data['message'])
    ]);
}

function saveFranchiseInquiry($data) {
    $pdo = getConnection();
    $stmt = $pdo->prepare("INSERT INTO franchise_inquiries (name, email, phone, city, state, investment_capacity, experience, message) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    return $stmt->execute([
        sanitize($data['name']),
        sanitize($data['email']),
        sanitize($data['phone']),
        sanitize($data['city']),
        sanitize($data['state']),
        sanitize($data['investment']),
        sanitize($data['experience']),
        sanitize($data['message'])
    ]);
}

function formatPrice($price) {
    return '₹' . number_format($price, 0);
}

function getProductImage($image) {
    if (empty($image)) {
        return '/assets/images/gallery/pepperoni_pizza_product_shot.png';
    }
    if (strpos($image, 'http') === 0) {
        return $image;
    }
    
    $imageMap = [
        'margherita.jpg' => 'pepperoni_pizza_product_shot.png',
        'pepperoni.jpg' => 'pepperoni_pizza_product_shot.png',
        'farmhouse.jpg' => 'farmhouse_veggie_pizza_shot.png',
        'bbq-chicken.jpg' => 'bbq_chicken_pizza_shot.png',
        'penne-alfredo.jpg' => 'penne_alfredo_pasta_dish.png',
        'spaghetti.jpg' => 'penne_alfredo_pasta_dish.png',
        'garlic-bread.jpg' => 'garlic_bread_appetizer.png',
        'cheesy-garlic-bread.jpg' => 'garlic_bread_appetizer.png',
        'brownie.jpg' => 'chocolate_brownie_dessert.png',
        'tiramisu.jpg' => 'chocolate_brownie_dessert.png',
    ];
    
    if (isset($imageMap[$image])) {
        return '/assets/images/gallery/' . $imageMap[$image];
    }
    
    if (file_exists(__DIR__ . '/../public/assets/images/products/' . $image)) {
        return '/assets/images/products/' . $image;
    }
    
    return '/assets/images/gallery/pepperoni_pizza_product_shot.png';
}
?>
