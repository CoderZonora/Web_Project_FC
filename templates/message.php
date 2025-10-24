<?php template_header('Messages'); ?>
<h1>Messaging — <?php echo htmlspecialchars($target_user['username']); ?></h1>

<form method="post" enctype="multipart/form-data" onsubmit="return validateForm()">
    <div class="signature-form">
        <div class="message-box">
            <label for="message">Send Message to <?php echo htmlspecialchars($target_user['username']); ?>:</label>
            <textarea name="message" id="message" rows="4" placeholder="Enter your message here"></textarea>
        </div>

        <label for="signature">Your Signature:</label>
        <textarea name="signature" id="signature" rows="2"><?php echo htmlspecialchars($target_user['signature']); ?></textarea>

        <input type="hidden" name="user_hash" value="<?php echo htmlspecialchars($_SESSION['user_id']); ?>">

        <div class="buttons">
            <input type="submit" value="Send Message">
            <a href="index.php" class="button">Back to Home</a>
        </div>
    </div>
</form>

<hr>
<h3>Your Conversations</h3>
<div class="message-list">
<?php
if (!empty($messages)) {
    foreach ($messages as $msg) {
        $isSent = $msg['sender_id'] === $_SESSION['user_id'];
        $userRef = $isSent ? get_user($msg['receiver_id']) : get_user($msg['sender_id']);
        $direction = $isSent ? '→' : '←';
        echo "<div class='message-entry'>";
        echo "<strong>{$direction} " . htmlspecialchars($userRef['username'] ?? 'Unknown') . ":</strong><br>";
        echo "<pre>" . htmlspecialchars($msg['message']) . "</pre>";
        echo "<small>" . htmlspecialchars($msg['created_at']) . "</small>";
        echo "</div><hr>";
    }
} else {
    echo "<p>No messages yet.</p>";
}
?>
</div>

<script>
function validateForm() {
    var msg = document.getElementById('message').value.trim();
    return true;
}
</script>

<?php template_footer(); ?>
