<?php
session_start();

// Connect to MySQL server (without selecting a database initially)
$mysqli = new mysqli('localhost', 'root', '');
if ($mysqli->connect_error) {
    die('Database connection failed: ' . $mysqli->connect_error);
}

// --- CSRF helper (add once near top) ---
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
function verify_csrf($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token ?? '');
}
function e($s) { return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

// Create database if it doesn't exist
$db_name = 'charlie_db';
$mysqli->query("CREATE DATABASE IF NOT EXISTS `{$db_name}`");
$mysqli->select_db($db_name);

// Create users table if it doesn't exist
$create_users_table = "
CREATE TABLE IF NOT EXISTS users (
  id VARCHAR(64) PRIMARY KEY,
  username VARCHAR(50) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  role ENUM('agent','admin') DEFAULT 'agent',
  signature TEXT DEFAULT '',
  profile_pic VARCHAR(255) DEFAULT NULL
)";
$mysqli->query($create_users_table);

// Create messages table if it doesn't exist
$create_messages_table = "
CREATE TABLE IF NOT EXISTS messages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sender_id VARCHAR(128) NOT NULL,
  receiver_id VARCHAR(128) NOT NULL,
  message TEXT NOT NULL,
  file_path VARCHAR(255) DEFAULT NULL,
  file_type VARCHAR(100) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$mysqli->query($create_messages_table);

$create_feedback_table = "
CREATE TABLE IF NOT EXISTS feedback (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id VARCHAR(128) NULL,
  subject VARCHAR(255) DEFAULT NULL,
  message TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$mysqli->query($create_feedback_table);

// Config
$SALT = 'random_salt_asasasssasa';
$admin_username = getenv('ADMIN_USERNAME') ?: 'adminz';
$admin_password = getenv('ADMIN_PASSWORD') ?: 'ajdlaeahardadminpassword0987afjafh';
$original_admin_id = hash('sha256', $admin_username . $SALT);

// Ensure admin user exists
$stmt = $mysqli->prepare("SELECT id FROM users WHERE username=?");
$stmt->bind_param('s', $admin_username);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    $admin_id = $original_admin_id;
    $hashed_pass = password_hash($admin_password, PASSWORD_DEFAULT);
    $stmt_ins = $mysqli->prepare("INSERT INTO users (id, username, password, role) VALUES (?, ?, ?, 'admin')");
    $stmt_ins->bind_param('sss', $admin_id, $admin_username, $hashed_pass);
    $stmt_ins->execute();
    $stmt_ins->close();
}
$stmt->close();

// Helper functions
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function is_original_admin() {
    global $original_admin_id;
    return isset($_SESSION['user_id']) && $_SESSION['user_id'] === $original_admin_id;
}

function is_admin($mysqli) {
    if (!is_logged_in()) return false;
    $stmt = $mysqli->prepare("SELECT role FROM users WHERE id=?");
    $stmt->bind_param('s', $_SESSION['user_id']);
    $stmt->execute();
    $role = $stmt->get_result()->fetch_assoc()['role'] ?? '';
    $stmt->close();
    return $role === 'admin';
}

function get_user_by_username($mysqli, $username) {
    $stmt = $mysqli->prepare("SELECT * FROM users WHERE username=?");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $user;
}

function get_user_by_id($mysqli, $id) {
    $stmt = $mysqli->prepare("SELECT * FROM users WHERE id=?");
    $stmt->bind_param('s', $id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $user;
}

function generate_user_id($username) {
    return hash('sha256', $username . time() . random_bytes(16));
}

function handleUploadError($error) {
    switch ($error) {
        case UPLOAD_ERR_INI_SIZE: return "File exceeds upload_max_filesize.";
        case UPLOAD_ERR_FORM_SIZE: return "File exceeds MAX_FILE_SIZE directive.";
        case UPLOAD_ERR_PARTIAL: return "File partially uploaded.";
        case UPLOAD_ERR_NO_FILE: return "No file uploaded.";
        default: return "Upload error.";
    }
}

// Route control
$route = $_GET['route'] ?? 'home';
// header("Content-Security-Policy: script-src 'self'");

switch ($route) {
    case 'login':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $password = trim($_POST['password'] ?? '');
            $user = get_user_by_username($mysqli, $username);

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                header('Location: index.php');
                exit;
            } else {
                $error = "Invalid credentials.";
            }
        }
        include 'templates/login.php';
        break;

    case 'register':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username']);
            $password = trim($_POST['password']);

            if (empty($username) || empty($password)) {
                $error = "Username and password required.";
            } else {
                $existing = get_user_by_username($mysqli, $username);
                if ($existing) {
                    $error = "Username already exists.";
                } else {
                    $user_id = generate_user_id($username);
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $mysqli->prepare("INSERT INTO users (id, username, password) VALUES (?, ?, ?)");
                    $stmt->bind_param('sss', $user_id, $username, $hash);
                    $stmt->execute();
                    $stmt->close();
                    header("Location: index.php?route=login");
                    exit;
                }
            }
        }
        include 'templates/register.php';
        break;

    case 'logout':
        session_destroy();
        header('Location: index.php');
        exit;

    case 'message':
        if (!is_logged_in()) {
            header('Location: index.php?route=login');
            exit;
        }

        $requested_user_id = $_GET['user_id'] ?? null;
        $target_user_id = $requested_user_id ?: $_SESSION['user_id'];
        $target_user = get_user_by_id($mysqli, $target_user_id);
        
        // Get current user info
        $current_user = get_user_by_id($mysqli, $_SESSION['user_id']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $signature = $_POST['signature'] ?? '';
            $messageText = trim($_POST['message'] ?? '');
            $receiver_id = $_POST['receiver_id'] ?? '';
            $filePath = null;
            $fileType = null;

            // Handle file upload (if present)
            if (!empty($_FILES['attachment']['name'])) {
                $uploadDir = __DIR__ . '/uploads/';
                $fileName = time() . '_' . basename($_FILES['attachment']['name']);
                $targetPath = $uploadDir . $fileName;

                // Validate size and type (allow only images/docs)
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
                $fileType = mime_content_type($_FILES['attachment']['tmp_name']);

                if (!in_array($fileType, $allowedTypes)) {  
                    die("Invalid file type. Only JPG, PNG, GIF, and PDF allowed.");
                }

                if ($_FILES['attachment']['size'] > 5 * 1024 * 1024) { // 5MB
                    die("File too large. Max size: 5MB");
                }

                if (move_uploaded_file($_FILES['attachment']['tmp_name'], $targetPath)) {
                    $filePath = 'uploads/' . $fileName;
                } else {
                    die("Error uploading file.");
                }
            }

            // Save signature
            $stmt = $mysqli->prepare("UPDATE users SET signature=? WHERE id=?");
            $stmt->bind_param('ss', $signature, $_SESSION['user_id']);
            $stmt->execute();
            $stmt->close();

            // Save message (text or file or both)
            if (($messageText || $filePath) && $receiver_id && $receiver_id !== $_SESSION['user_id']) {
                $stmt = $mysqli->prepare("INSERT INTO messages (sender_id, receiver_id, message, file_path, file_type) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param('sssss', $_SESSION['user_id'], $receiver_id, $messageText, $filePath, $fileType);
                $stmt->execute();
                $stmt->close();
            }

            header("Location: index.php?route=message&user_id=" . urlencode($receiver_id ?: $_SESSION['user_id']));
            exit;
        }

        // Load all users except current user (for message recipient selection)
        $stmt = $mysqli->prepare("SELECT id, username FROM users WHERE id != ?");
        $stmt->bind_param('s', $_SESSION['user_id']);
        $stmt->execute();
        $all_users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Load all messages for current user
        $stmt = $mysqli->prepare("SELECT * FROM messages WHERE sender_id=? OR receiver_id=? ORDER BY created_at DESC");
        $stmt->bind_param('ss', $_SESSION['user_id'], $_SESSION['user_id']);
        $stmt->execute();
        $messages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        include 'templates/message.php';
        break;

    case 'all_messages':
        if (!is_logged_in() || !is_admin($mysqli)) {
            header('Location: index.php');
            exit;
        }

        // Load ALL messages for admin view
        $stmt = $mysqli->prepare("SELECT * FROM messages ORDER BY created_at DESC");
        $stmt->execute();
        $all_messages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        include 'templates/all_messages.php';
        break;

    case 'admin':
        if (!is_original_admin()) {
            header('Location: index.php');
            exit;
        }

        // Load all users for the admin panel
        $stmt = $mysqli->prepare("SELECT id, username, role FROM users");
        $stmt->execute();
        $users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_POST['user_id'] ?? '';
            $newRole = $_POST['role'] ?? '';
            $stmt = $mysqli->prepare("UPDATE users SET role=? WHERE id=?");
            $stmt->bind_param('ss', $newRole, $userId);
            $stmt->execute();
            $stmt->close();
            $success_message = "User role updated successfully!";
            
            // Reload users after update
            $stmt = $mysqli->prepare("SELECT id, username, role FROM users");
            $stmt->execute();
            $users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
        }
        include 'templates/admin.php';
        break;

    case 'flag':
        if (!is_logged_in() || !is_admin($mysqli)) {
            header('Location: index.php');
            exit;
        }
        include 'templates/flag.php';
        break;
    case 'profile':
        if (!is_logged_in()) {
            header('Location: index.php?route=login'); exit;
        }
        $current_user = get_user_by_id($mysqli, $_SESSION['user_id']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // CSRF check
            if (!verify_csrf($_POST['csrf_token'] ?? '')) { die('CSRF validation failed'); }

            // Update signature
            $signature = $_POST['signature'] ?? '';
            // Handle profile pic upload
            $profile_pic_path = $current_user['profile_pic'] ?? null;
            if (!empty($_FILES['profile_pic']['name'])) {
                $uploadDir = __DIR__ . '/uploads/avatars/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

                $tmp = $_FILES['profile_pic']['tmp_name'];
                $mime = mime_content_type($tmp);
                $allowed = ['image/jpeg','image/png','image/gif'];
                if (!in_array($mime, $allowed)) {
                    $error = "Invalid profile image type. Use JPG/PNG/GIF.";
                } elseif ($_FILES['profile_pic']['size'] > 2 * 1024 * 1024) {
                    $error = "Profile picture must be under 2MB.";
                } else {
                    $ext = pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION);
                    $newname = 'avatar_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
                    $target = $uploadDir . $newname;
                    if (move_uploaded_file($tmp, $target)) {
                        $profile_pic_path = 'uploads/avatars/' . $newname;
                        // Optional: delete old avatar
                        if (!empty($current_user['profile_pic']) && file_exists(__DIR__ . '/' . $current_user['profile_pic'])) {
                            @unlink(__DIR__ . '/' . $current_user['profile_pic']);
                        }
                    } else {
                        $error = "Error saving profile image.";
                    }
                }
            }

            // Save changes
            $stmt = $mysqli->prepare("UPDATE users SET signature=?, profile_pic=? WHERE id=?");
            $stmt->bind_param('sss', $signature, $profile_pic_path, $_SESSION['user_id']);
            $stmt->execute();
            $stmt->close();

            header('Location: index.php?route=profile');
        exit;
        }

        include 'templates/profile.php';
        break;
    case 'settings':
        if (!is_logged_in()) {
            header('Location: index.php?route=login'); exit;
        }
        $current_user = get_user_by_id($mysqli, $_SESSION['user_id']);
        $success = $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verify_csrf($_POST['csrf_token'] ?? '')) { die('CSRF failed'); }

            // Change password
            if (!empty($_POST['change_password'])) {
                $old = $_POST['old_password'] ?? '';
                $new = $_POST['new_password'] ?? '';
                if (!password_verify($old, $current_user['password'])) {
                    $error = "Old password is incorrect.";
                } elseif (strlen($new) < 8) {
                    $error = "New password must be at least 8 characters.";
                } else {
                    $hash = password_hash($new, PASSWORD_DEFAULT);
                    $stmt = $mysqli->prepare("UPDATE users SET password=? WHERE id=?");
                    $stmt->bind_param('ss', $hash, $_SESSION['user_id']);
                    $stmt->execute();
                    $stmt->close();
                    $success = "Password changed successfully.";
                }
            }

            // Preferences: theme, notify_pref, can_receive_from
            if (isset($_POST['save_prefs'])) {
                $theme = in_array($_POST['theme'] ?? '', ['light','dark']) ? $_POST['theme'] : 'light';
                $notify = ($_POST['notify_pref'] ?? 'off') === 'on' ? 'on' : 'off';
                $can_recv = in_array($_POST['can_receive_from'] ?? '', ['all','admins_only']) ? $_POST['can_receive_from'] : 'all';
                $stmt = $mysqli->prepare("UPDATE users SET theme=?, notify_pref=?, can_receive_from=? WHERE id=?");
                $stmt->bind_param('ssss', $theme, $notify, $can_recv, $_SESSION['user_id']);
                $stmt->execute();
                $stmt->close();
                $success = ($success ? $success . ' ' : '') . "Preferences saved.";
            }
        }

        include 'templates/settings.php';
        break;
    
    case 'help':
        // public help page and optional feedback form
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verify_csrf($_POST['csrf_token'] ?? '')) { die('CSRF failed'); }
            $subject = trim($_POST['subject'] ?? '');
            $message = trim($_POST['message'] ?? '');
            $uid = $_SESSION['user_id'] ?? null;
            if ($message) {
                $stmt = $mysqli->prepare("INSERT INTO feedback (user_id, subject, message) VALUES (?, ?, ?)");
                $stmt->bind_param('sss', $uid, $subject, $message);
                $stmt->execute();
                $stmt->close();
                $feedback_success = "Thanks — your message has been submitted.";
            } else {
                $feedback_error = "Please enter a message.";
            }
        }
        include 'templates/help.php';
        break;

    default:
        if (is_logged_in()) {
            include 'templates/home.php';
        } else {
            header('Location: index.php?route=login');
        }
        break;
}

function template_header($title) {
    $user = null;
    if (isset($_SESSION['user_id'])) {
        // $mysqli is in global scope - safe here because template_header called within same file
        global $mysqli;
        $user = get_user_by_id($mysqli, $_SESSION['user_id']);
    }
    $nav = '';
    if ($user) {
        $nav = '
        <nav style="margin-bottom:10px;">
            <a href="index.php">Home</a> |
            <a href="index.php?route=message">Messages</a> |
            <a href="index.php?route=profile">Profile</a> |
            <a href="index.php?route=settings">Settings</a> |
            <a href="index.php?route=help">Help</a> |
            <a href="index.php?route=logout">Logout</a>
        </nav>';
    } else {
        $nav = '<nav style="margin-bottom:10px;"><a href="index.php?route=login">Login</a> | <a href="index.php?route=register">Register</a> | <a href="index.php?route=help">Help</a></nav>';
    }
    echo "<!DOCTYPE html>
    <html><head><title>$title</title>
    <meta charset='utf-8'>
    <link rel='stylesheet' type='text/css' href='/css/styles.css'></head><body>
    {$nav}";
}


function template_footer() {
    echo "</body></html>";
}
?>  