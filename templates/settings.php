<?php template_header('Settings'); ?>

<h1>Settings</h1>

<?php if (!empty($error)) echo '<div style="color:red;">' . e($error) . '</div>'; ?>
<?php if (!empty($success)) echo '<div style="color:green;">' . e($success) . '</div>'; ?>

<!-- Change password -->
<section style="margin-bottom:20px;">
    <h3>Change Password</h3>
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?php echo e($_SESSION['csrf_token']); ?>">
        <input type="hidden" name="change_password" value="1">
        <label>Old password:</label><br>
        <input type="password" name="old_password" required><br><br>
        <label>New password:</label><br>
        <input type="password" name="new_password" required><br><br>
        <input type="submit" value="Change Password">
    </form>
</section>

<?php template_footer(); ?>
