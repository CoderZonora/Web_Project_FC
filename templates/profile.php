<?php template_header('Profile'); ?>

<h1>Your Profile</h1>

<div style="display:flex; gap:20px;">
    <div style="width:220px;">
        <h3>Avatar</h3>
        <?php if (!empty($current_user['profile_pic'])): ?>
            <img src="<?php echo e($current_user['profile_pic']); ?>" alt="Avatar" style="max-width:200px;border-radius:6px;">
        <?php else: ?>
            <div style="width:200px;height:200px;background:#eee;display:flex;align-items:center;justify-content:center;border-radius:6px;">No avatar</div>
        <?php endif; ?>
    </div>

    <div style="flex:1;">
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo e($_SESSION['csrf_token']); ?>">
            <label>Change profile picture (JPG/PNG/GIF, max 2MB):</label><br>
            <input type="file" name="profile_pic" accept="image/*"><br><br>

            <input type="submit" value="Save Profile">
        </form>

        <hr>
        <h4>Account Info</h4>
        <p><strong>Username:</strong> <?php echo e($current_user['username']); ?></p>
        <p><strong>Role:</strong> <?php echo e($current_user['role']); ?></p>
    </div>
</div>

<?php template_footer(); ?>
