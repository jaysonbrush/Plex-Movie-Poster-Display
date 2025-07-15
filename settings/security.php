<?php
//For feedback, suggestions, or issues please visit https://www.mattsshack.com/plex-movie-poster-display/
include_once('../assets/plexmovieposter/loginCheck.php');
include '../config.php';
include '../assets/plexmovieposter/CommonLib.php';
include '../assets/plexmovieposter/CacheLib.php';
include '../assets/plexmovieposter/setData.php';
include_once '../assets/plexmovieposter/SecurityLib.php';

$passwordMsg = null;
$passwordMsgType = 'info';

// Check for password upgrade prompt
if (isset($_SESSION['needs_password_upgrade']) && $_SESSION['needs_password_upgrade']) {
    $passwordMsg = "Your password is stored in plain text. Please set a new password to upgrade to secure storage.";
    $passwordMsgType = 'warning';
}

// Handle password change
if (!empty($_POST['changePassword'])) {
    // Validate CSRF
    if (!pmpd_csrf_validate()) {
        $passwordMsg = "Security token invalid. Please try again.";
        $passwordMsgType = 'danger';
    } else {
        $currentPassword = $_POST['currentPassword'] ?? '';
        $newPassword = $_POST['newPassword'] ?? '';
        $confirmPassword = $_POST['confirmPassword'] ?? '';

        // Verify current password
        if (!pmpd_verify_password($currentPassword, $pmpPassword)) {
            $passwordMsg = "Current password is incorrect.";
            $passwordMsgType = 'danger';
        } elseif (strlen($newPassword) < 8) {
            $passwordMsg = "New password must be at least 8 characters.";
            $passwordMsgType = 'danger';
        } elseif ($newPassword !== $confirmPassword) {
            $passwordMsg = "New passwords do not match.";
            $passwordMsgType = 'danger';
        } else {
            // Hash the new password
            $hashedPassword = pmpd_hash_password($newPassword);

            // Update config file with hashed password
            $configFile = dirname(__FILE__) . '/../config.php';
            $configContent = file_get_contents($configFile);

            if ($configContent === false || empty($configContent)) {
                $passwordMsg = "Failed to read config file.";
                $passwordMsgType = 'danger';
            } else {
                // Find and replace the password line using string functions
                $oldPasswordLine = "\$pmpPassword = '" . $pmpPassword . "';";
                $newPasswordLine = "\$pmpPassword = '" . $hashedPassword . "';";

                $newConfigContent = str_replace($oldPasswordLine, $newPasswordLine, $configContent);

                if ($newConfigContent === $configContent) {
                    // str_replace didn't find the pattern, try with different quote style or spacing
                    $oldPasswordLine2 = '$pmpPassword = "' . $pmpPassword . '";';
                    $newConfigContent = str_replace($oldPasswordLine2, $newPasswordLine, $configContent);
                }

                if ($newConfigContent === $configContent) {
                    $passwordMsg = "Could not find password line in config. Please update manually.";
                    $passwordMsgType = 'danger';
                } elseif (empty($newConfigContent)) {
                    $passwordMsg = "Error generating new config content.";
                    $passwordMsgType = 'danger';
                } else {
                    if (file_put_contents($configFile, $newConfigContent) !== false) {
                        $passwordMsg = "Password updated successfully with secure hashing!";
                        $passwordMsgType = 'success';
                        unset($_SESSION['needs_password_upgrade']);
                        pmpd_csrf_regenerate();
                        // Update the variable for display
                        $pmpPassword = $hashedPassword;
                    } else {
                        $passwordMsg = "Failed to write config file. Check permissions.";
                        $passwordMsgType = 'danger';
                    }
                }
            }
        }
    }
}

// Save Configuration (username only from this page)
if (!empty($_POST['saveConfig'])) {
    if (!pmpd_csrf_validate()) {
        $passwordMsg = "Security token invalid. Please try again.";
        $passwordMsgType = 'danger';
    } else {
        setData(basename(__FILE__));
    }
}

// Check if password is hashed (bcrypt hashes start with $2y$, $2a$, or $2b$)
$isPasswordHashed = (strlen($pmpPassword) >= 4 && in_array(substr($pmpPassword, 0, 4), ['$2y$', '$2a$', '$2b$']));

?>

<!doctype html>
<html lang="en">
<head>
    <?php HeaderInfo(basename(__FILE__)); ?>
    <script> ShowHideAdvanced(); </script>
    <script> ShowHideSideBar(); </script>
</head>

<body>
    <div id="plex" class="application">
        <div class="background-container">
            <div class="settings-core"></div>
        </div>
        <?php NavBar() ;?>
        <div id="content" class="scroll-container dark-scrollbar">
            <div class="FullPage-container-17Y0cs">
                <?php sidebarInfo(basename(__FILE__)) ;?>
                <div class="Page-page-aq7i_X Scroller-scroller-3GqQcZ Scroller-vertical-VScFLT  ">
                    <div id="MainPage" class="SettingsPage-content-1vKVEr PageContent-pageContent-16mK6G">
                        <h2 class="SettingsPageHeader-header-1ugtIL">
                            Security Configuration
                        </h2>
                        <?php AdvancedBar() ;?>

                        <?php if ($passwordMsg): ?>
                        <div class="alert alert-<?php echo $passwordMsgType; ?>" role="alert">
                            <?php echo htmlspecialchars($passwordMsg); ?>
                        </div>
                        <?php endif; ?>

                        <!-- Password Status -->
                        <div class="form-group">
                            <strong>Password Security:</strong>
                            <?php if ($isPasswordHashed): ?>
                                <span style="color: #28a745;">Secured (bcrypt hashed)</span>
                            <?php else: ?>
                                <span style="color: #dc3545;">Not Secured (plain text) - Please change your password below</span>
                            <?php endif; ?>
                        </div>

                        <!-- Session Info -->
                        <div class="form-group">
                            <strong>Session Status:</strong>
                            <?php
                            $remaining = pmpd_session_remaining();
                            $mins = floor($remaining / 60);
                            $secs = $remaining % 60;
                            echo "Expires in {$mins}m {$secs}s (30 min timeout)";
                            ?>
                        </div>

                        <hr>

                        <!-- Username Change Form -->
                        <h3>Account Settings</h3>
                        <form id="server-settings-form" method="post" class="needs-validation" novalidate enctype="multipart/form-data">
                            <div class="form-group">
                                Username:&nbsp;
                                <input type="text" class="fieldInfo-username form-control form-inline" id="pmpUsername" name="pmpUsername" placeholder="Username" value="<?php echo htmlspecialchars($pmpUsername); ?>" required>
                            </div>

                            <!-- Hidden password field to preserve current password -->
                            <input type="hidden" id="pmpPassword" name="pmpPassword" value="<?php echo htmlspecialchars($pmpPassword); ?>">

                            <?php ghostData(basename(__FILE__)); ?>
                            <?php echo pmpd_csrf_field(); ?>

                            <?php submitForm(TRUE); ?>
                        </form>

                        <hr>

                        <!-- Password Change Form -->
                        <h3>Change Password</h3>
                        <form method="post" class="needs-validation" novalidate>
                            <div class="form-group">
                                Current Password:&nbsp;
                                <input type="password" class="form-control form-inline" id="currentPassword" name="currentPassword" placeholder="Current Password" required style="max-width: 300px;">
                            </div>

                            <div class="form-group">
                                New Password:&nbsp;
                                <input type="password" class="form-control form-inline" id="newPassword" name="newPassword" placeholder="New Password (min 8 chars)" required minlength="8" style="max-width: 300px;">
                            </div>

                            <div class="form-group">
                                Confirm Password:&nbsp;
                                <input type="password" class="form-control form-inline" id="confirmPassword" name="confirmPassword" placeholder="Confirm New Password" required style="max-width: 300px;">
                            </div>

                            <?php echo pmpd_csrf_field(); ?>

                            <button type="submit" name="changePassword" value="1" class="btn btn-primary">Change Password</button>
                        </form>

                        <?php FooterInfo() ; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php safariJSSide(); ?>
</body>
</html>
