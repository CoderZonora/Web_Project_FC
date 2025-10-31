<?php
// Redirect if the user is not logged in or is not the original admin
if (!is_logged_in() || !is_original_admin()) {
    header('Location: index.php');
    exit;
}

template_header('Admin Panel');

// Fetch all users
$stmt = $pdo->query("SELECT id, username, role FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch feedback
$stmt_feedback = $pdo->query("
    SELECT f.id, f.user_id, f.subject, f.message, f.created_at, u.username 
    FROM feedback f 
    LEFT JOIN users u ON f.user_id = u.id 
    ORDER BY f.created_at DESC
");
$feedbacks = $stmt_feedback->fetchAll(PDO::FETCH_ASSOC);
?>

<div style="background: linear-gradient(135deg, #47302B, #2E1810); min-height: 100vh; color: #FFECD9; font-family: Arial, sans-serif; padding: 30px;">
    <h1 style="text-align: center; color: #FFD7A8;">Admin Panel</h1>

    <?php if (isset($success_message)): ?>
        <div style="background-color: #3B241E; color: #C1FFB5; padding: 10px; margin-bottom: 15px; border-radius: 8px; border: 1px solid #7F9D6C;">
            <?php echo htmlspecialchars($success_message); ?>
        </div>
    <?php endif; ?>

    <div style="background-color: #3B241E; padding: 20px; border-radius: 12px; box-shadow: 0 0 10px rgba(0,0,0,0.5); margin-bottom: 40px;">
        <h2 style="color: #FFD7A8;">Manage User Roles</h2>
        <form method="post">
            <label for="user_id" style="display: block; margin-top: 10px;">Select User:</label>
            <select name="user_id" id="user_id" required style="padding: 8px; width: 100%; border-radius: 6px; border: none; background-color: #5C3B33; color: #FFECD9;">
                <?php foreach ($users as $user): ?>
                    <option value="<?php echo htmlspecialchars($user['id']); ?>">
                        <?php echo htmlspecialchars($user['username']); ?> (<?php echo htmlspecialchars($user['role']); ?>)
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="role" style="display: block; margin-top: 10px;">New Role:</label>
            <select name="role" id="role" required style="padding: 8px; width: 100%; border-radius: 6px; border: none; background-color: #5C3B33; color: #FFECD9;">
                <option value="agent">Agent</option>
                <option value="admin">Admin</option>
            </select>

            <input type="submit" value="Update Role" style="margin-top: 15px; background-color: #7F4F44; color: #FFECD9; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer;">
        </form>
    </div>

    <div style="background-color: #3B241E; padding: 20px; border-radius: 12px; box-shadow: 0 0 10px rgba(0,0,0,0.5);">
        <h2 style="color: #FFD7A8;">All Users</h2>
        <table border="0" cellpadding="10" style="width: 100%; margin-top: 15px; border-collapse: collapse;">
            <thead style="background-color: #5C3B33;">
                <tr style="color: #FFD7A8;">
                    <th>Username</th>
                    <th>User ID</th>
                    <th>Role</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr style="background-color: #2E1810; color: #FFECD9;">
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['id']); ?></td>
                        <td><?php echo htmlspecialchars($user['role']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- 🟣 User Feedback Section -->
    <div style="background-color: #3B241E; padding: 20px; border-radius: 12px; box-shadow: 0 0 10px rgba(0,0,0,0.5); margin-top: 40px;">
        <h2 style="color: #FFD7A8;">User Feedback Messages</h2>

        <?php if (count($feedbacks) > 0): ?>
            <table border="0" cellpadding="10" style="width: 100%; margin-top: 15px; border-collapse: collapse;">
                <thead style="background-color: #5C3B33;">
                    <tr style="color: #FFD7A8;">
                        <th>ID</th>
                        <th>Username</th>
                        <th>Subject</th>
                        <th>Message</th>
                        <th>Submitted On</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($feedbacks as $feedback): ?>
                        <tr style="background-color: #2E1810; color: #FFECD9; vertical-align: top;">
                            <td><?php echo htmlspecialchars($feedback['id']); ?></td>
                            <td><?php echo htmlspecialchars($feedback['username'] ?? 'Guest'); ?></td>
                            <td><?php echo htmlspecialchars($feedback['subject'] ?? '—'); ?></td>
                            <td><?php echo nl2br(htmlspecialchars($feedback['message'])); ?></td>
                            <td><?php echo htmlspecialchars($feedback['created_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="color: #FFECD9;">No feedback messages found.</p>
        <?php endif; ?>
    </div>

    <p style="margin-top: 30px; text-align: center;">
        <a href="index.php" style="color: #FFD7A8; text-decoration: none;">← Back to Home</a>
    </p>
</div>

<?php template_footer(); ?>
