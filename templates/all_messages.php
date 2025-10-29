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

        echo "<div class='message-entry' style='background-color: #f9f9f9; padding: 15px; margin-bottom: 15px; border-radius: 6px; border: 1px solid #ddd;'>";
        
        // Header (Sender → Receiver)
        echo "<div style='display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;'>";
        echo "<strong style='color: #2E1810;'>From: " . htmlspecialchars($sender['username'] ?? 'Unknown') . " → To: " . htmlspecialchars($receiver['username'] ?? 'Unknown') . "</strong>";
        echo "<small style='color: #666;'>" . htmlspecialchars($msg['created_at']) . "</small>";
        echo "</div>";

        // Message text
        if (!empty($msg['message'])) {
            echo "<pre style='background-color: #fff; padding: 10px; border-radius: 4px; border: 1px solid #eee; white-space: pre-wrap; word-wrap: break-word; margin-bottom: 10px;'>" 
                . htmlspecialchars($msg['message']) . "</pre>";
        }

        // Attachment section
        if (!empty($msg['file_path'])) {
            $fileType = $msg['file_type'] ?? '';
            $filePath = htmlspecialchars($msg['file_path']);
            
            if (str_starts_with($fileType, 'image/')) {
                // Display image preview
                echo "<div style='margin-top: 8px;'>";
                echo "<img src='{$filePath}' alt='Attachment' style='max-width: 200px; border-radius: 6px; border: 1px solid #ccc;'>";
                echo "</div>";
            } else {
                // Display file download link
                $fileName = basename($filePath);
                echo "<div style='margin-top: 8px;'>";
                echo "📎 <a href='{$filePath}' target='_blank' style='color: #007BFF; text-decoration: none;'>View Attachment ({$fileName})</a>";
                echo "</div>";
            }
        }

        // Footer info
        echo "<small style='color: #999; display: block; margin-top: 8px;'>Message ID: " . htmlspecialchars($msg['id'] ?? 'N/A') . "</small>";
        echo "</div>";
    }
} else {
    echo "<p>No messages found in the system.</p>";
}
?>
</div>

<?php template_footer(); ?>
