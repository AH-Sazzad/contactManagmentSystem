<?php
require_once(__DIR__."/autoload.php");

$errors = [];
$success = false;

if(isset($_POST["save"])){
    // Sanitize inputs
    $first_name = trim($_POST["first_name"] ?? "");
    $last_name = trim($_POST["last_name"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $category = $_POST["category"] ?? "friends";
    $gender = $_POST["gender"] ?? "";
    $photo = trim($_POST["photo"] ?? "");
    
    // Validation
    if(empty($first_name)) {
        $errors[] = "First name is required";
    }
    
    if(empty($phone)) {
        $errors[] = "Phone number is required";
    } elseif(!preg_match('/^[0-9]{10,15}$/', $phone)) {
        $errors[] = "Please enter a valid phone number";
    }
    
    if(empty($gender) || !in_array($gender, ['male', 'female'])) {
        $errors[] = "Please select a valid gender";
    }
    
    // If no errors, save to file
    if(empty($errors)) {
        $data_file = __DIR__."/data/data.json";
        
        // Check if phone already exists
        $contacts = [];
        if(file_exists($data_file)) {
            $contacts = json_decode(file_get_contents($data_file), true) ?? [];
            
            // Check for duplicate phone
            foreach($contacts as $contact) {
                if($contact['phone'] === $phone) {
                    $errors[] = "Phone number already exists";
                    break;
                }
            }
        }
        function id($length = 10) {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $uid = '';
    
    for ($i = 0; $i < $length; $i++) {
        $uid .= $characters[rand(0, $charactersLength - 1)];
    }
    
    return $uid;
}
        $uid=id();
        if(empty($errors)) {
            // Add new contact
            $contacts[] = [
                "id"=>$uid,
                "first_name" => $first_name,
                "last_name"  => $last_name,
                "phone"      => $phone,
                "category"   => $category,
                "gender"     => $gender,
                "photo"      => $photo ?: 'placeholder.png',
            ];
            
            if(file_put_contents($data_file, json_encode($contacts, JSON_PRETTY_PRINT))) {
                $success = true;
                // Clear form fields
                $_POST = [];
            } else {
                $errors[] = "Failed to save contact";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Contact - Contact Management System</title>
    <link rel="stylesheet" href="bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container my-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h1 class="h4 mb-0">Create a New Contact</h1>
                    </div>
                    <div class="card-body">
                        <?php if($success): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                Contact added successfully!
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if(!empty($errors)): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <ul class="mb-0">
                                    <?php foreach($errors as $error): ?>
                                        <li><?php echo htmlspecialchars($error); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <form action="" method="POST">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="first_name" class="form-label">First Name *</label>
                                    <input type="text" class="form-control" name="first_name" id="first_name" 
                                           value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>" 
                                           placeholder="Enter First Name" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="last_name" class="form-label">Last Name</label>
                                    <input type="text" class="form-control" name="last_name" id="last_name"
                                           value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>"
                                           placeholder="Enter Last Name">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number *</label>
                                <input type="tel" name="phone" id="phone" class="form-control" 
                                       value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>"
                                       placeholder="Enter Phone Number" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="photo" class="form-label">Profile Image URL</label>
                                <input type="url" name="photo" id="photo" class="form-control"
                                       value="<?php echo htmlspecialchars($_POST['photo'] ?? ''); ?>"
                                       placeholder="Enter image URL">
                                <div class="form-text">Leave empty for default placeholder image</div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="category" class="form-label">Category</label>
                                    <select name="category" id="category" class="form-select">
                                        <option value="friends" <?php echo ($_POST['category'] ?? '') === 'friends' ? 'selected' : ''; ?>>Friends</option>
                                        <option value="family" <?php echo ($_POST['category'] ?? '') === 'family' ? 'selected' : ''; ?>>Family</option>
                                        <option value="office" <?php echo ($_POST['category'] ?? '') === 'office' ? 'selected' : ''; ?>>Office</option>
                                        <option value="relatives" <?php echo ($_POST['category'] ?? '') === 'relatives' ? 'selected' : ''; ?>>Relatives</option>
                                        <option value="others" <?php echo ($_POST['category'] ?? '') === 'others' ? 'selected' : ''; ?>>Others</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Gender *</label>
                                    <div class="d-flex gap-4">
                                        <div class="form-check">
                                            <input type="radio" id="male" name="gender" value="male" class="form-check-input"
                                                   <?php echo ($_POST['gender'] ?? '') === 'male' ? 'checked' : ''; ?> required>
                                            <label for="male" class="form-check-label">Male</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="radio" id="female" name="gender" value="female" class="form-check-input"
                                                   <?php echo ($_POST['gender'] ?? '') === 'female' ? 'checked' : ''; ?> required>
                                            <label for="female" class="form-check-label">Female</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button type="submit" name="save" class="btn btn-primary px-4">Save Contact</button>
                                <a href="index.php" class="btn btn-outline-secondary">Back to Contacts</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="bootstrap/bootstrap.bundle.js"></script>
</body>
</html>