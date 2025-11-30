<?php
require_once 'config.php';
require_once 'layout.php';

$msg = '';
$msgType = '';

// 1. Handle Application Acceptance (Set popawl=1 and status=Accepted)
if (isset($_POST['accept_app_id'])) {
    $appId = $_POST['accept_app_id'];
    
    try {
        $pdo->beginTransaction();

        // A. Fetch application details to get discord_id
        $stmt = $pdo->prepare("SELECT discord_id, discord_username FROM applications WHERE id = ?");
        $stmt->execute([$appId]);
        $app = $stmt->fetch();

        if ($app) {
            // B. Update the application status
            $stmt = $pdo->prepare("UPDATE applications SET status = 'Accepted' WHERE id = ?");
            $stmt->execute([$appId]);
            
            // C. Find the user in the accounts table using discord_id and set popawl = 1
            $stmt = $pdo->prepare("UPDATE accounts SET popawl = 1 WHERE discord_id = ? OR discord_username = ?");
            $stmt->execute([$app['discord_id'], $app['discord_username']]);
            
            $pdo->commit();
            $msg = "Application accepted. Player whitelisted successfully!";
            $msgType = "success";
        } else {
            $pdo->rollBack();
            $msg = "Application not found.";
            $msgType = "error";
        }

    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Accept failed: " . $e->getMessage());
        $msg = "Error processing acceptance. Database transaction failed.";
        $msgType = "error";
    }
}

// 2. Handle Application Decline (Set status=Declined)
if (isset($_POST['decline_app_id'])) {
    $appId = $_POST['decline_app_id'];
    $stmt = $pdo->prepare("UPDATE applications SET status = 'Declined' WHERE id = ?");
    if ($stmt->execute([$appId])) {
        $msg = "Application declined.";
        $msgType = "success";
    } else {
        $msg = "Error declining application.";
        $msgType = "error";
    }
}

// 3. Fetch Pending Applications
$stmt = $pdo->query("SELECT * FROM applications WHERE status = 'Pending' ORDER BY created_at ASC");
$pendingApps = $stmt->fetchAll();

// 4. Fetch Recently Reviewed Applications (Optional: for history)
$stmtReviewed = $pdo->query("SELECT * FROM applications WHERE status != 'Pending' ORDER BY created_at DESC LIMIT 10");
$reviewedApps = $stmtReviewed->fetchAll();

ob_start();
?>

<!-- Notification Message -->
<?php if ($msg): ?>
    <div class="mb-6 p-4 rounded-lg <?php echo $msgType == 'success' ? 'bg-green-500/20 text-green-300 border border-green-500/50' : 'bg-red-500/20 text-red-300 border border-red-500/50'; ?>">
        <?php echo $msg; ?>
    </div>
<?php endif; ?>

<div class="text-white mb-8">
    <h1 class="text-3xl font-bold">Whitelist Application Review Dashboard</h1>
    <p class="text-gray-400">Manage incoming whitelist requests.</p>
</div>


<!-- Pending Applications Section -->
<div class="bg-slate-800 rounded-xl border border-blue-700 overflow-hidden shadow-2xl mb-8">
    <div class="p-6 border-b border-blue-700 bg-blue-900/20 flex justify-between items-center">
        <h3 class="text-xl font-bold text-white">Pending Requests (<?php echo count($pendingApps); ?>)</h3>
        <span class="text-sm text-blue-300">New applications awaiting review.</span>
    </div>

    <?php if (count($pendingApps) > 0): ?>
        <?php foreach ($pendingApps as $app): ?>
            <div class="p-6 border-b border-slate-700 last:border-b-0">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <p class="text-2xl font-extrabold text-blue-400"><?php echo htmlspecialchars($app['full_name']); ?></p>
                        <p class="text-md text-gray-300">Discord: <span class="font-mono text-white"><?php echo htmlspecialchars($app['discord_username']); ?> (ID: <?php echo htmlspecialchars($app['discord_id']); ?>)</span></p>
                        <p class="text-sm text-gray-500">Submitted: <?php echo date('Y-m-d H:i', strtotime($app['created_at'])); ?></p>
                    </div>
                    <div class="flex gap-3">
                        <!-- Accept Button -->
                        <form method="POST" onsubmit="return confirm('Confirm Acceptance for <?php echo htmlspecialchars($app['discord_username']); ?>? This will whitelist the player.');">
                            <input type="hidden" name="accept_app_id" value="<?php echo $app['id']; ?>">
                            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-lg font-bold transition flex items-center shadow-md">
                                <i class="fas fa-check mr-2"></i> Accept
                            </button>
                        </form>
                        <!-- Decline Button -->
                        <form method="POST" onsubmit="return confirm('Confirm Decline for <?php echo htmlspecialchars($app['discord_username']); ?>?');">
                            <input type="hidden" name="decline_app_id" value="<?php echo $app['id']; ?>">
                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-5 py-2 rounded-lg font-bold transition flex items-center shadow-md">
                                <i class="fas fa-times mr-2"></i> Decline
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Application Details Section -->
                <div class="bg-slate-900 p-4 rounded-lg border border-slate-700/50">
                    <h4 class="text-lg font-semibold text-gray-200 mb-2">Answers:</h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="font-medium text-gray-400">Roleplay Explanation:</p>
                            <p class="text-gray-200 bg-slate-700/30 p-2 rounded"><?php echo nl2br(htmlspecialchars($app['roleplay_explanation'])); ?></p>
                        </div>
                        <div>
                            <p class="font-medium text-gray-400">New Life Rules Explanation:</p>
                            <p class="text-gray-200 bg-slate-700/30 p-2 rounded"><?php echo nl2br(htmlspecialchars($app['new_life_rules'])); ?></p>
                        </div>
                    </div>
                    
                    <button onclick="document.getElementById('details_<?php echo $app['id']; ?>').classList.toggle('hidden')" class="mt-3 text-blue-400 hover:text-blue-300 text-sm font-semibold">
                        Show More Details (Email, DOB, etc.)
                    </button>
                    
                    <!-- Hidden Detailed Information -->
                    <div id="details_<?php echo $app['id']; ?>" class="hidden mt-4 pt-4 border-t border-slate-700">
                        <?php
                            $otherData = json_decode($app['other_answers'], true);
                        ?>
                        <p class="text-sm text-gray-400">Email: <span class="text-white"><?php echo htmlspecialchars($app['email']); ?></span></p>
                        <p class="text-sm text-gray-400">DOB: <span class="text-white"><?php echo htmlspecialchars($otherData['dob'] ?? 'N/A'); ?></span></p>
                        <p class="text-sm text-gray-400">RP Experience: <span class="text-white"><?php echo htmlspecialchars($otherData['rp_experience'] ?? 'N/A'); ?></span></p>
                        <p class="text-sm text-gray-400">Read Rules: <span class="text-white"><?php echo htmlspecialchars($otherData['read_rules'] ?? 'N/A'); ?></span></p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="p-6 text-center text-gray-500 bg-slate-900/50">
            <i class="fas fa-clipboard-check text-4xl mb-3 text-green-500"></i><br>
            No pending applications found.
        </p>
    <?php endif; ?>
</div>


<!-- Recently Reviewed Applications Section -->
<div class="bg-slate-800 rounded-xl border border-slate-700 overflow-hidden shadow-2xl">
    <div class="p-6 border-b border-slate-700">
        <h3 class="text-xl font-bold text-white">Recently Reviewed Applications</h3>
        <p class="text-sm text-gray-400">Last 10 accepted or declined applications.</p>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full text-left text-gray-400">
            <thead class="bg-slate-900 uppercase text-xs font-semibold text-gray-500">
                <tr>
                    <th class="px-6 py-4">ID</th>
                    <th class="px-6 py-4">Full Name</th>
                    <th class="px-6 py-4">Discord User</th>
                    <th class="px-6 py-4">Review Status</th>
                    <th class="px-6 py-4">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-700">
                <?php foreach ($reviewedApps as $app): ?>
                <tr class="hover:bg-slate-700/50 transition">
                    <td class="px-6 py-4">#<?php echo $app['id']; ?></td>
                    <td class="px-6 py-4 text-white font-medium"><?php echo htmlspecialchars($app['full_name']); ?></td>
                    <td class="px-6 py-4"><?php echo htmlspecialchars($app['discord_username']); ?></td>
                    <td class="px-6 py-4">
                        <?php if($app['status'] == 'Accepted'): ?>
                            <span class="inline-block px-3 py-1 text-xs font-bold rounded-full bg-green-500/20 text-green-400">Accepted</span>
                        <?php else: ?>
                            <span class="inline-block px-3 py-1 text-xs font-bold rounded-full bg-red-500/20 text-red-400">Declined</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 text-sm"><?php echo date('Y-m-d', strtotime($app['created_at'])); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$content = ob_get_clean();
print_layout('Application Manager', $content, 'appmanage');
?>