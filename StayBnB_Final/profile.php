<?php
define('STAYBNB_ACCESS', true);
require_once 'config/db_connect.php';
require_login();

$user = get_current_user($conn);

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = sanitize_input($_POST['fullname']);
    $phone = sanitize_input($_POST['phone']);
    $address = sanitize_input($_POST['address']);
    
    $stmt = $conn->prepare("UPDATE users SET fullname = ?, phone = ?, address = ? WHERE user_id = ?");
    $stmt->bind_param("sssi", $fullname, $phone, $address, $_SESSION['user_id']);
    
    if ($stmt->execute()) {
        $_SESSION['user_name'] = $fullname;
        redirect('profile.php', 'Profile updated successfully!', 'success');
    } else {
        $error = 'Failed to update profile';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>My Profile - StayBnB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation here -->
    <div class="container my-5">
        <h2>My Profile</h2>
        <form method="POST" class="mt-4">
            <div class="mb-3">
                <label>Full Name</label>
                <input type="text" name="fullname" class="form-control" value="<?= htmlspecialchars($user['fullname']) ?>" required>
            </div>
            <div class="mb-3">
                <label>Email</label>
                <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" readonly>
            </div>
            <div class="mb-3">
                <label>Phone</label>
                <input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label>Address</label>
                <textarea name="address" class="form-control" rows="3"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Update Profile</button>
        </form>
    </div>
</body>
</html>