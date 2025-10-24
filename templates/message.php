<?php template_header('Messages'); ?>
<h1>Send Message</h1>

<form method="post" enctype="multipart/form-data" onsubmit="return validateForm()">
    <div class="signature-form">
        <div class="message-box">
            <label for="receiver_id">Send Message to:</label>
            <select name="receiver_id" id="receiver_id" required style="width: 100%; padding: 8px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;">
                <option value="">-- Select a user --</option>
                <?php foreach ($all_users as $user): ?>
                    <option value="<?php echo htmlspecialchars($user['id']); ?>" 
                        <?php echo (isset($target_user) && $target_user['id'] === $user['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($user['username']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="message">Your Message:</label>
            <textarea name="message" id="message" rows="4" placeholder="Enter your message here" required></textarea>
        </div>

        <label for="signature">Your Signature:</label>
        <textarea name="signature" id="signature" rows="2" placeholder="Optional signature"><?php echo htmlspecialchars($current_user['signature'] ?? ''); ?></textarea>

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
        $userRef = $isSent ? get_user_by_id($mysqli, $msg['receiver_id']) : get_user_by_id($mysqli, $msg['sender_id']);
        $direction = $isSent ? '→' : '←';
        $bgColor = $isSent ? '#e3f2fd' : '#f1f8e9';
        
        echo "<div class='message-entry' style='background-color: {$bgColor}; padding: 12px; margin-bottom: 10px; border-radius: 4px; border: 1px solid #ddd;'>";
        echo "<strong>{$direction} " . htmlspecialchars($userRef['username'] ?? 'Unknown') . ":</strong><br>";
        echo "<pre style='margin: 10px 0; white-space: pre-wrap; word-wrap: break-word;'>" . htmlspecialchars($msg['message']) . "</pre>";
        echo "<small style='color: #666;'>" . htmlspecialchars($msg['created_at']) . "</small>";
        echo "</div>";
    }
} else {
    echo "<p>No messages yet. Start a conversation by sending a message!</p>";
}
?>
</div>

<script>
function validateForm() {
    var msg = document.getElementById('message').value.trim();
    var receiver = document.getElementById('receiver_id').value;
    
    if (!receiver) {
        alert('Please select a recipient!');
        return false;
    }
    
    if (!msg) {
        alert('Please enter a message!');
        return false;
    }
    
    return true;
}
</script>

<?php template_footer(); ?>