<?php 
require_once(__DIR__."/autoload.php");


$data_file = __DIR__."/data/data.json";
$contacts = [];
$search_query = $_GET['search'] ?? '';
$category_filter = $_GET['category'] ?? '';
$gender_filter = $_GET['gender'] ?? '';
$filtered_contacts = [];

if(file_exists($data_file)) {
    $contacts = json_decode(file_get_contents($data_file), true) ?? [];
    
    
}

// Use comprehensive search for better partial matching
function comSearch($contacts, $query = '', $category = '', $gender = '') {
    $text_results = [];
    
    // If there's a search query, use advanced name search
    if(!empty(trim($query))) {
        $text_results = advancedNameSearch($contacts, $query);
    } else {
        $text_results = $contacts;
    }
    
    // Now apply category and gender filters
    $final_results = [];
    
    foreach($text_results as $contact) {
        $match = true;
        
        // Category filter
        if(!empty($category) && $match) {
            if(!isset($contact['category']) || $contact['category'] !== $category) {
                $match = false;
            }
        }
        
        // Gender filter
        if(!empty($gender) && $match) {
            if(!isset($contact['gender']) || $contact['gender'] !== $gender) {
                $match = false;
            }
        }
        
        if($match) {
            $final_results[] = $contact;
        }
    }
    
    return $final_results;
}
function advancedNameSearch($contacts, $query) {
    $results = [];
    $query = strtolower(trim($query));
    
    foreach($contacts as $contact) {
        $first_name = strtolower($contact['first_name'] ?? '');
        $last_name = strtolower($contact['last_name'] ?? '');
        $full_name = trim($first_name . ' ' . $last_name);
        
        // Check if query matches beginning of first name (e.g., "asm" matches "asmaul")
        if(strpos($first_name, $query) === 0) {
            $results[] = $contact;
            continue;
        }
        
        // Check if query matches any part of first name
        if(strpos($first_name, $query) !== false) {
            $results[] = $contact;
            continue;
        }
        
        // Check if query matches beginning of last name
        if(strpos($last_name, $query) === 0) {
            $results[] = $contact;
            continue;
        }
        
        // Check if query matches any part of full name
        if(strpos($full_name, $query) !== false) {
            $results[] = $contact;
            continue;
        }
        
        // Check combined first letters (e.g., "ah" matches "Asmaul Hasan")
        $name_initials = substr($first_name, 0, 1) . substr($last_name, 0, 1);
        if(strpos($name_initials, $query) === 0) {
            $results[] = $contact;
            continue;
        }
    }
    
    return $results;
}
function highlightSearchTerm($text, $query) {
    if(empty($query) || empty(trim($query))) return $text;
    
    $pattern = '/(' . preg_quote(trim($query), '/') . ')/i';
    return preg_replace($pattern, '<mark class="bg-warning">$1</mark>', $text);
}
$filtered_contacts = comSearch($contacts, $search_query, $category_filter, $gender_filter);
usort($filtered_contacts, function($a, $b) {
    return strcmp($a['first_name'], $b['first_name']);
});

// Get unique categories
$categories = array_unique(array_column($contacts, 'category'));
sort($categories);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Management System</title>
    <link rel="stylesheet" href="bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container-fluid p-0">
        <header class="bg-primary text-white p-3 shadow-sm">
            <div class="container">
                <div class="d-flex justify-content-between align-items-center">
                    <h1 class="h4 mb-0">My Contacts</h1>
                    <a href="add.php" class="btn btn-light">
                        + Add New Contact
                    </a>
                </div>
            </div>
        </header>

        <main class="container py-4">
            <!-- Search Section -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="bg-light p-4 rounded shadow-sm">
                        <form action="" method="GET" class="row g-3 align-items-end">
                            <div class="col-md-6">
                                <label for="search" class="form-label fw-bold">Search Contacts</label>
                                <div class="input-group">
                                    <input type="text" 
                                           name="search" 
                                           id="search" 
                                           class="form-control" 
                                           placeholder="Try 'asm' for Asmaul, 'has' for Hasan..."
                                           value="<?php echo htmlspecialchars($search_query); ?>">
                                    <button class="btn btn-primary" type="submit">
                                        🔍 Search
                                    </button>
                                    <?php if(!empty($search_query)): ?>
                                        <a href="index.php" class="btn btn-outline-secondary">Clear</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <label for="category_filter" class="form-label fw-bold">Filter by Category</label>
                                <select name="category" id="category_filter" class="form-select">
                                    <option value="">All Categories</option>
                                    <?php foreach($categories as $cat): ?>
                                        <option value="<?php echo $cat; ?>" 
                                            <?php echo ($category_filter === $cat) ? 'selected' : ''; ?>>
                                            <?php echo ucfirst($cat); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-3">
                                <label for="gender_filter" class="form-label fw-bold">Filter by Gender</label>
                                <select name="gender" id="gender_filter" class="form-select">
                                    <option value="">All Genders</option>
                                    <option value="male" <?php echo ($gender_filter === 'male') ? 'selected' : ''; ?>>Male</option>
                                    <option value="female" <?php echo ($gender_filter === 'female') ? 'selected' : ''; ?>>Female</option>
                                </select>
                            </div>
                            
                            <div class="col-12">
                                <div class="d-flex gap-2 justify-content-end">
                                    <button type="submit" class="btn btn-primary">Apply Search & Filters</button>
                                    <a href="index.php" class="btn btn-outline-secondary">Show All Contacts</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Results Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="h5 text-muted mb-0">
                    <?php if(!empty($search_query)): ?>
                        Search Results for "<span class="text-primary"><?php echo htmlspecialchars($search_query); ?></span>" 
                    <?php else: ?>
                        All Contacts
                    <?php endif; ?>
                    <span class="badge bg-primary ms-2"><?php echo count($filtered_contacts); ?> found</span>
                </h2>
            </div>

            <!-- Search Tips -->
            <?php if(!empty($search_query) && count($filtered_contacts) > 0): ?>
                <div class="alert alert-info d-flex align-items-center">
                    <div>
                        <strong>💡 Search Tips:</strong> 
                        Found <?php echo count($filtered_contacts); ?> contact(s) matching "<strong><?php echo htmlspecialchars($search_query); ?></strong>"
                        <?php if(strlen($search_query) < 3): ?>
                            <br><small>Try typing more letters for better results </small>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if(!empty($search_query) && count($filtered_contacts) === 0): ?>
                <div class="alert alert-warning">
                    <strong>No contacts found for "<?php echo htmlspecialchars($search_query); ?>"</strong>
                    <br>
                    <small>Try:</small>
                    <ul class="mb-0 mt-1">
                        <li>Searching with fewer letters (e.g., "asm" instead of "asmaul")</li>
                        <li>Checking for typos</li>
                        <li>Searching by phone number or category</li>
                        <li><a href="index.php" class="alert-link">View all contacts</a></li>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Contacts List -->
            <div class="contacts-list">
                <?php if(empty($filtered_contacts)): ?>
                    <div class="text-center py-5 bg-light rounded">
                        <h4 class="text-muted">No contacts found</h4>
                        <?php if(empty($contacts)): ?>
                            <p class="text-muted">Get started by adding your first contact</p>
                            <a href="add.php" class="btn btn-primary">Add First Contact</a>
                        <?php else: ?>
                            <p class="text-muted">No contacts match your search criteria</p>
                            <a href="index.php" class="btn btn-outline-primary">View All Contacts</a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <!-- Desktop Table View -->
                    <div class="d-none d-md-block">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th width="60px">Photo</th>
                                        <th>Name</th>
                                        <th>Phone</th>
                                        <th>Category</th>
                                        <th>Gender</th>
                                        <th>ID</th>
                                        <th width="150px">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($filtered_contacts as $contact): ?>
                                    <tr class="contact-row">
                                        <td>
                                            <img class="userImg" id="cover" src="<?php echo ($contact['photo']); ?>" 
                                            >
                                        </td>
                                        <td>
                                            <div class="fw-bold">
                                                <?php 
                                                $full_name = $contact['first_name'] . ' ' . $contact['last_name'];
                                                if(!empty($search_query)) {
                                                    echo highlightSearchTerm(htmlspecialchars($full_name), $search_query);
                                                } else {
                                                    echo htmlspecialchars($full_name);
                                                }
                                                ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-primary">
                                                <?php 
                                                if(!empty($search_query)) {
                                                    echo highlightSearchTerm(htmlspecialchars($contact['phone']), $search_query);
                                                } else {
                                                    echo htmlspecialchars($contact['phone']);
                                                }
                                                ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary"><?php echo ucfirst($contact['category']); ?></span>
                                        </td>
                                        <td>
                                            <span class="badge bg-info"><?php echo ucfirst($contact['gender']); ?></span>
                                        </td>
                                        <td>
                                            <small class="text-muted"><?php echo htmlspecialchars($contact['id'] ?? 'N/A'); ?></small>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="view.php?id=<?php echo urlencode($contact['id']); ?>" 
                                                   class="btn btn-outline-primary btn-sm">View</a>
                                                <a href="edit.php?id=<?php echo urlencode($contact['id']); ?>" 
                                                   class="btn btn-outline-warning btn-sm">Edit</a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Mobile List View -->
                    <div class="d-md-none">
                        <div class="list-group">
                            <?php foreach($filtered_contacts as $contact): ?>
                            <div class="list-group-item">
                                <div class="d-flex align-items-start">
                                    <img class="userImg " id="cover "src="<?php echo htmlspecialchars($contact['photo']); ?>" 
                                        
                                    >
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <h6 class="fw-bold mb-1">
                                                <?php 
                                                $full_name = $contact['first_name'] . ' ' . $contact['last_name'];
                                                if(!empty($search_query)) {
                                                    echo highlightSearchTerm(htmlspecialchars($full_name), $search_query);
                                                } else {
                                                    echo htmlspecialchars($full_name);
                                                }
                                                ?>
                                            </h6>
                                            <small class="text-muted"><?php echo htmlspecialchars($contact['id'] ?? 'N/A'); ?></small>
                                        </div>
                                        
                                        <p class="mb-1 text-primary">
                                            📞 <?php 
                                            if(!empty($search_query)) {
                                                echo highlightSearchTerm(htmlspecialchars($contact['phone']), $search_query);
                                            } else {
                                                echo htmlspecialchars($contact['phone']);
                                            }
                                            ?>
                                        </p>
                                        
                                        <div class="d-flex gap-2 mb-2">
                                            <span class="badge bg-secondary"><?php echo ucfirst($contact['category']); ?></span>
                                            <span class="badge bg-info"><?php echo ucfirst($contact['gender']); ?></span>
                                        </div>
                                        
                                        <div class="d-flex gap-2">
                                            <a href="view.php?id=<?php echo urlencode($contact['id']); ?>" 
                                               class="btn btn-outline-primary btn-sm">View</a>
                                            <a href="edit.php?id=<?php echo urlencode($contact['id']); ?>" 
                                               class="btn btn-outline-warning btn-sm">Edit</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Pagination (Optional) -->
            <?php if(count($filtered_contacts) > 10): ?>
            <div class="d-flex justify-content-between align-items-center mt-4">
                <div class="text-muted">
                    Showing <?php echo min(10, count($filtered_contacts)); ?> of <?php echo count($filtered_contacts); ?> contacts
                </div>
                <nav>
                    <ul class="pagination pagination-sm">
                        <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                        <li class="page-item"><a class="page-link" href="#">Next</a></li>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>
        </main>
    </div>
    
    <script src="bootstrap/bootstrap.bundle.js"></script>
    
</body>
</html>