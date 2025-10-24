<?php template_header('All Messages - Admin View'); ?>

<h1>All Messages (Admin View)</h1>
<p style="color: #666; font-size: 0.9em;">As an admin, you can view all messages sent between all users.</p>

<div class="buttons" style="margin-bottom: 20px;">
    <a href="index.php" class="button">Back to Home</a>
</div>

<hr>

<div class="message-list">
<?php
if (!empty($all_messages)) {
    foreach ($all_messages as $msg) {
        $sender = get_user_by_id($mysqli, $msg['sender_id']);
        $receiver = get_user_by_id($mysqli, $msg['receiver_id']);
        
        echo "<div class='message-entry' style='background-color: #f9f9f9; padding: 15px; margin-bottom: 15px; border-radius: 4px; border: 1px solid #ddd;'>";
        echo "<div style='display: flex; justify-content: space-between; margin-bottom: 10px;'>";
        echo "<strong style='color: #2E1810;'>From: " . htmlspecialchars($sender['username'] ?? 'Unknown') . " → To: " . htmlspecialchars($receiver['username'] ?? 'Unknown') . "</strong>";
        echo "<small style='color: #666;'>" . htmlspecialchars($msg['created_at']) . "</small>";
        echo "</div>";
        echo "<pre style='background-color: #fff; padding: 10px; border-radius: 4px; border: 1px solid #eee; white-space: pre-wrap; word-wrap: break-word;'>" . htmlspecialchars($msg['message']) . "</pre>";
        echo "<small style='color: #999;'>Message ID: " . htmlspecialchars($msg['id'] ?? 'N/A') . "</small>";
        echo "</div>";
    }
} else {
    echo "<p>No messages found in the system.</p>";
}
?>
</div>

<?php template_footer(); ?>