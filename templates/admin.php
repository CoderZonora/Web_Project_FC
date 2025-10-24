<?php
// Redirect if the user is not logged in or is not the original admin
if (!is_logged_in() || !is_original_admin()) {
    header('Location: index.php');
    exit;
}

template_header('Admin Panel');
?>

<h1>Admin Panel</h1>

<?php if (isset($success_message)): ?>
    <div class="success-message" style="background-color: #d4edda; color: #155724; padding: 10px; margin-bottom: 15px; border-radius: 4px;">
        <?php echo htmlspecialchars($success_message); ?>
    </div>
<?php endif; ?>

<h2>Manage User Roles</h2>
<form method="post">
    <label for="user_id">Select User:</label>
    <select name="user_id" id="user_id" required>
        <?php foreach ($users as $user): ?>
            <option value="<?php echo htmlspecialchars($user['id']); ?>">
                <?php echo htmlspecialchars($user['username']); ?> (<?php echo htmlspecialchars($user['role']); ?>)
            </option>
        <?php endforeach; ?>
    </select>

    <label for="role">New Role:</label>
    <select name="role" id="role" required>
        <option value="agent">Agent</option>
        <option value="admin">Admin</option>
    </select>

    <input type="submit" value="Update Role">
</form>

<h2>All Users</h2>
<table border="1" cellpadding="10" style="width: 100%; margin-top: 20px;">
    <thead>
        <tr>
            <th>Username</th>
            <th>User ID</th>
            <th>Role</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?php echo htmlspecialchars($user['username']); ?></td>
                <td><?php echo htmlspecialchars($user['id']); ?></td>
                <td><?php echo htmlspecialchars($user['role']); ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<p style="margin-top: 20px;"><a href="index.php">Back to Home</a></p>
<?php template_footer(); ?>