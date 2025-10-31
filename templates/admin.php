<?php
// Redirect if the user is not logged in or is not the original admin
if (!is_logged_in() || !is_original_admin()) {
    header('Location: index.php');
    exit;
}

// Fetch all users
$stmt = $mysqli->prepare("SELECT id, username, role FROM users");
$stmt->execute();
$users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch feedback
$stmt_feedback = $mysqli->prepare("
    SELECT f.id, f.user_id, f.subject, f.message, f.created_at, u.username 
    FROM feedback f 
    LEFT JOIN users u ON f.user_id = u.id 
    ORDER BY f.created_at DESC
");
$stmt_feedback->execute();
$feedbacks = $stmt_feedback->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_feedback->close();

template_header('Admin Panel');
?>

<div style="background: linear-gradient(135deg, #000000, #1a1a1a); min-height: 100vh; color: #f5f5f5; font-family: Arial, sans-serif; padding: 30px;">
    <h1 style="text-align: center; color: #ffffff;">Admin Panel</h1>

    <?php if (isset($success_message)): ?>
        <div style="background-color: #1f1f1f; color: #aaffaa; padding: 10px; margin-bottom: 15px; border-radius: 8px; border: 1px solid #333;">
            <?php echo htmlspecialchars($success_message); ?>
        </div>
    <?php endif; ?>

    <!-- Manage User Roles -->
    <div style="background-color: #121212; padding: 20px; border-radius: 12px; box-shadow: 0 0 10px rgba(255,255,255,0.1); margin-bottom: 40px;">
        <h2 style="color: #ffffff;">Manage User Roles</h2>
        <form method="post">
            <label for="user_id" style="display: block; margin-top: 10px;">Select User:</label>
            <select name="user_id" id="user_id" required style="padding: 8px; width: 100%; border-radius: 6px; border: 1px solid #444; background-color: #1e1e1e; color: #f5f5f5;">
                <?php foreach ($users as $user): ?>
                    <option value="<?php echo htmlspecialchars($user['id']); ?>">
                        <?php echo htmlspecialchars($user['username']); ?> (<?php echo htmlspecialchars($user['role']); ?>)
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="role" style="display: block; margin-top: 10px;">New Role:</label>
            <select name="role" id="role" required style="padding: 8px; width: 100%; border-radius: 6px; border: 1px solid #444; background-color: #1e1e1e; color: #f5f5f5;">
                <option value="agent">Agent</option>
                <option value="admin">Admin</option>
            </select>

            <input type="submit" value="Update Role" style="margin-top: 15px; background-color: #333; color: #fff; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer;">
        </form>
    </div>

    <!-- All Users -->
    <div style="background-color: #121212; padding: 20px; border-radius: 12px; box-shadow: 0 0 10px rgba(255,255,255,0.1);">
        <h2 style="color: #ffffff;">All Users</h2>
        <table border="0" cellpadding="10" style="width: 100%; margin-top: 15px; border-collapse: collapse;">
            <thead style="background-color: #1f1f1f;">
                <tr style="color: #ffffff;">
                    <th>Username</th>
                    <th>User ID</th>
                    <th>Role</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr style="background-color: #0d0d0d; color: #f5f5f5; border-bottom: 1px solid #333;">
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['id']); ?></td>
                        <td><?php echo htmlspecialchars($user['role']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Feedback Messages -->
    <div style="background-color: #121212; padding: 20px; border-radius: 12px; box-shadow: 0 0 10px rgba(255,255,255,0.1); margin-top: 40px;">
        <h2 style="color: #ffffff;">User Feedback</h2>

        <?php if (count($feedbacks) > 0): ?>
            <table border="0" cellpadding="10" style="width: 100%; margin-top: 15px; border-collapse: collapse;">
                <thead style="background-color: #1f1f1f;">
                    <tr style="color: #ffffff;">
                        <th>ID</th>
                        <th>Username</th>
                        <th>Subject</th>
                        <th>Message</th>
                        <th>Submitted On</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($feedbacks as $fb): ?>
                        <tr style="background-color: #0d0d0d; color: #f5f5f5; border-bottom: 1px solid #333; vertical-align: top;">
                            <td><?php echo htmlspecialchars($fb['id']); ?></td>
                            <td><?php echo htmlspecialchars($fb['username'] ?? 'Guest'); ?></td>
                            <td><?php echo htmlspecialchars($fb['subject'] ?? '—'); ?></td>
                            <td style="white-space: pre-wrap;"><?php echo htmlspecialchars($fb['message']); ?></td>
                            <td><?php echo htmlspecialchars($fb['created_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="color: #ccc;">No feedback submitted yet.</p>
        <?php endif; ?>
    </div>

    <p style="margin-top: 30px; text-align: center;">
        <a href="index.php" style="color: #ffffff; text-decoration: none;">← Back to Home</a>
    </p>
</div>

<?php template_footer(); ?>
