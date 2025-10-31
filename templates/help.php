<?php template_header('Help'); ?>

<h1>Help & Support</h1>
<p>Welcome! Here are some quick tips:</p>
<ul>
    <li>To send messages: go to Messages → pick recipient → type message → attach file (optional).</li>
    <li>Allowed file types for messages: JPG, PNG, GIF, PDF (max 5MB).</li>
    <li>Profile picture: JPG/PNG/GIF (max 2MB).</li>
    <li>Admins can view all messages under All Messages.</li>
</ul>

<hr>

<h3>Report an issue / Contact Support</h3>
<?php if (!empty($feedback_success)) echo '<div style="color:green;">' . e($feedback_success) . '</div>'; ?>
<?php if (!empty($feedback_error)) echo '<div style="color:red;">' . e($feedback_error) . '</div>'; ?>

<form method="post">
    <input type="hidden" name="csrf_token" value="<?php echo e($_SESSION['csrf_token']); ?>">
    <label>Subject (optional)</label><br>
    <input type="text" name="subject" style="width:100%;"><br><br>
    <label>Message</label><br>
    <textarea name="message" rows="5" style="width:100%;" required></textarea><br><br>
    <input type="submit" value="Send to Support">
</form>

<?php template_footer(); ?>
