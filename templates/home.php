<?php template_header('Home'); ?>

<div style="background: linear-gradient(135deg, #47302B, #2E1810); min-height: 100vh; color: #FFECD9; font-family: Arial, sans-serif; display: flex; justify-content: center; align-items: center;">
    <div style="max-width: 1000px; width: 90%; margin: 20px; background-color: #FFECD9; color: #2E1810; border-radius: 8px; overflow: hidden; box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2); display: flex;">

        <!-- Sidebar -->
        <aside style="width: 200px; background-color: #2E1810; color: #FFECD9; padding: 20px; box-shadow: inset -2px 0 4px rgba(0, 0, 0, 0.1);">
            <h3 style="color: #FFECD9; margin-bottom: 15px;">Menu</h3>
            <a href="index.php?route=settings" style="color: #FFE6CC; display: block; margin-bottom: 10px; text-decoration: none;">Settings</a>
            <a href="index.php?route=profile" style="color: #FFE6CC; display: block; margin-bottom: 10px; text-decoration: none;">Profile</a>
            <a href="index.php?route=help" style="color: #FFE6CC; display: block; margin-bottom: 10px; text-decoration: none;">Help</a>
        </aside>

        <div style="flex-grow: 1; padding: 20px;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 20px; background-color: #47302B; padding: 10px 20px; color: #FFECD9; border-radius: 4px;">
                <span>Messaging App</span>
                <a href="index.php?route=logout" style="color: #FFECD9; text-decoration: none;">Logout</a>
            </div>

            <?php
            global $mysqli;
            $current_user_id = $_SESSION['user_id'] ?? null;
            $user = null;

            if ($current_user_id) {
                $stmt = $mysqli->prepare("SELECT id, username, role FROM users WHERE id = ?");
                $stmt->bind_param('s', $current_user_id);
                $stmt->execute();
                $user = $stmt->get_result()->fetch_assoc();
                $stmt->close();
            }

            if ($user): ?>
                <div style="background-color: #FFE6CC; padding: 20px; border-radius: 4px; border: 1px solid #8B4513;">
                    <h1 style="color: #47302B; margin-bottom: 15px;">Welcome, <?= htmlspecialchars($user['username']); ?></h1>
                    <p>Your role: <?= htmlspecialchars($user['role']); ?></p>
                </div>

                <div style="background-color: #FFECD9; border: 1px solid #8B4513; border-radius: 4px; padding: 15px; margin-top: 20px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">
                    <h3 style="color: #47302B; margin-bottom: 10px;">Your UserID</h3>
                    <small>User ID: <?= htmlspecialchars($current_user_id); ?></small>
                </div>

                <div style="display: flex; justify-content: space-between; margin-top: 20px;">
                    <a href="index.php?route=message" 
                        style="background-color: #47302B; color: #FFECD9; padding: 10px 15px; text-decoration: none; border-radius: 4px; text-align: center; transition: background-color 0.3s ease;">
                        Send a Message
                    </a>
                </div>

                <?php if ($user['role'] === 'admin'): ?>
                    <div style="display: flex; gap: 15px; justify-content: flex-end; margin-top: 10px; flex-wrap: wrap;">
                        <?php if (is_original_admin()): ?>
                            <a href="index.php?route=admin" style="font-size: 0.9em; background-color: #FFE6CC; color: #2E1810; padding: 8px 15px; border: 1px solid #8B4513; border-radius: 4px; text-decoration: none;">Admin Panel</a>
                        <?php endif; ?>
                        <a href="index.php?route=all_messages" style="font-size: 0.9em; background-color: #FFE6CC; color: #2E1810; padding: 8px 15px; border: 1px solid #8B4513; border-radius: 4px; text-decoration: none;">View All Messages</a>
                        <a href="index.php?route=flag" style="font-size: 0.9em; background-color: #FFE6CC; color: #2E1810; padding: 8px 15px; border: 1px solid #8B4513; border-radius: 4px; text-decoration: none;">Get Flag!</a>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <p style="color: #47302B; background-color: #FFE6CC; padding: 8px; border-radius: 4px; margin-top: 20px;">User not found or not logged in.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php template_footer(); ?>