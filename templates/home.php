<?php template_header('Home'); ?>

<div style="background: linear-gradient(135deg, #1a1a1a, #121212); min-height: 100vh; color: #e6e6e6; font-family: 'Poppins', sans-serif; display: flex; justify-content: center; align-items: center;">
    <div style="max-width: 1000px; width: 90%; margin: 20px; background-color: #202020; color: #e6e6e6; border-radius: 10px; overflow: hidden; box-shadow: 0 8px 20px rgba(0, 0, 0, 0.5); display: flex;">

        <!-- Sidebar -->
        <aside style="width: 220px; background-color: #181818; color: #e6e6e6; padding: 25px; box-shadow: inset -2px 0 4px rgba(0, 0, 0, 0.2);">
            <h3 style="color: #f5f5f5; margin-bottom: 20px; border-bottom: 1px solid #2a2a2a; padding-bottom: 10px;">Menu</h3>
            <a href="index.php?route=settings" style="color: #cfcfcf; display: block; margin-bottom: 14px; text-decoration: none; transition: color 0.3s ease;">Settings</a>
            <a href="index.php?route=profile" style="color: #cfcfcf; display: block; margin-bottom: 14px; text-decoration: none; transition: color 0.3s ease;">Profile</a>
            <a href="index.php?route=help" style="color: #cfcfcf; display: block; margin-bottom: 14px; text-decoration: none; transition: color 0.3s ease;">Help</a>
        </aside>

        <div style="flex-grow: 1; padding: 25px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; background-color: #262626; padding: 12px 20px; color: #f5f5f5; border-radius: 8px;">
                <span>Messaging App</span>
                <a href="index.php?route=logout" style="color: #00aaff; text-decoration: none; font-weight: 500;">Logout</a>
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
                <div style="background-color: #292929; padding: 20px; border-radius: 8px; border: 1px solid #333; box-shadow: 0 2px 6px rgba(0,0,0,0.3);">
                    <h1 style="color: #ffffff; margin-bottom: 10px;">Welcome, <?= htmlspecialchars($user['username']); ?></h1>
                    <p style="color: #cccccc;">Your role: <?= htmlspecialchars($user['role']); ?></p>
                </div>

                <div style="background-color: #262626; border: 1px solid #333; border-radius: 8px; padding: 15px; margin-top: 20px;">
                    <h3 style="color: #00aaff; margin-bottom: 10px;">Your UserID</h3>
                    <small style="color: #aaaaaa;">User ID: <?= htmlspecialchars($current_user_id); ?></small>
                </div>

                <div style="display: flex; justify-content: space-between; margin-top: 25px;">
                    <a href="index.php?route=message" 
                        style="background-color: #00aaff; color: white; padding: 10px 18px; border-radius: 6px; text-decoration: none; font-weight: 600; transition: background-color 0.3s ease;">
                        Send a Message
                    </a>
                </div>

                <?php if ($user['role'] === 'admin'): ?>
                    <div style="display: flex; gap: 15px; justify-content: flex-end; margin-top: 15px; flex-wrap: wrap;">
                        <?php if (is_original_admin()): ?>
                            <a href="index.php?route=admin" style="font-size: 0.9em; background-color: #202020; color: #00aaff; padding: 8px 15px; border: 1px solid #00aaff; border-radius: 6px; text-decoration: none;">Admin Panel</a>
                        <?php endif; ?>
                        <a href="index.php?route=all_messages" style="font-size: 0.9em; background-color: #202020; color: #00aaff; padding: 8px 15px; border: 1px solid #00aaff; border-radius: 6px; text-decoration: none;">View All Messages</a>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <p style="color: #cccccc; background-color: #262626; padding: 10px 15px; border-radius: 6px; margin-top: 20px;">User not found or not logged in.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php template_footer(); ?>
