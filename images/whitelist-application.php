<?php
require_once 'config.php';
require_once 'layout.php';

$msg = '';
$msgType = '';

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_submitted'])) {
    
    // 1. Gather Required Data (Sanitize inputs for security)
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $fullName = filter_input(INPUT_POST, 'full-name', FILTER_SANITIZE_STRING);
    $discordUsername = filter_input(INPUT_POST, 'discord-username', FILTER_SANITIZE_STRING);
    $discordId = filter_input(INPUT_POST, 'discord-id', FILTER_SANITIZE_STRING);
    $roleplayExplanation = filter_input(INPUT_POST, 'roleplay-explanation', FILTER_SANITIZE_STRING);
    $newLifeRules = filter_input(INPUT_POST, 'new-life-rules', FILTER_SANITIZE_STRING);

    // 2. Gather Optional/Other Answers into a JSON string
    $otherAnswers = json_encode([
        'dob' => filter_input(INPUT_POST, 'dob', FILTER_SANITIZE_STRING),
        'timezone' => filter_input(INPUT_POST, 'timezone', FILTER_SANITIZE_STRING),
        'facebook_name' => filter_input(INPUT_POST, 'facebook-name', FILTER_SANITIZE_STRING),
        'rp_experience' => filter_input(INPUT_POST, 'rp-experience', FILTER_SANITIZE_STRING),
        'read_rules' => filter_input(INPUT_POST, 'read-rules', FILTER_SANITIZE_STRING),
        'agree_rules' => filter_input(INPUT_POST, 'agree-rules', FILTER_SANITIZE_STRING),
    ]);

    // Simple validation
    if (empty($discordId) || empty($discordUsername)) {
        $msg = "Error: Discord Username and ID are required.";
        $msgType = "error";
    } else {
        try {
            // 3. Prepare SQL statement for insertion into the 'applications' table
            $stmt = $pdo->prepare("INSERT INTO applications 
                (email, full_name, discord_username, discord_id, roleplay_explanation, new_life_rules, other_answers, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending')");
            
            // 4. Execute the statement
            if ($stmt->execute([$email, $fullName, $discordUsername, $discordId, $roleplayExplanation, $newLifeRules, $otherAnswers])) {
                $msg = "Application submitted successfully! Please wait for admin review.";
                $msgType = "success";
            } else {
                $msg = "Error submitting application. Please try again.";
                $msgType = "error";
            }
        } catch (PDOException $e) {
            // Log the error (for debug) and show a generic message
            error_log("Application submission failed: " . $e->getMessage());
            $msg = "Database error occurred. Please contact support.";
            $msgType = "error";
        }
    }
}

ob_start();
?>

<!-- Notification Message -->
<?php if ($msg): ?>
    <div class="mb-6 p-4 rounded-lg <?php echo $msgType == 'success' ? 'bg-green-500/20 text-green-300 border border-green-500/50' : 'bg-red-500/20 text-red-300 border border-red-500/50'; ?>">
        <?php echo $msg; ?>
    </div>
<?php endif; ?>

<!-- Whitelist Application Form -->
<div class="bg-slate-800 p-8 rounded-xl border border-slate-700 shadow-2xl">
    <div class="application-intro mb-6">
        <h2 class="text-3xl font-extrabold text-white mb-2">Whitelist Application</h2>
        <p class="text-gray-400">Complete this form to apply for whitelist access. All fields marked with * are required.</p>
    </div>
    
    <!-- Added method="POST" and action="" to submit to the same file -->
    <form method="POST" action="" class="grid grid-cols-1 gap-6">
        <input type="hidden" name="form_submitted" value="1">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="form-group">
                <label for="email" class="block text-gray-300 mb-1">Email*</label>
                <input type="email" id="email" name="email" required class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2 text-white focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <div class="form-group">
                <label for="full-name" class="block text-gray-300 mb-1">1.1 Full Name (Real)*</label>
                <input type="text" id="full-name" name="full-name" required class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2 text-white focus:ring-blue-500 focus:border-blue-500">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="form-group">
                <label for="dob" class="block text-gray-300 mb-1">1.2 Date of Birth* (Month, day, year)</label>
                <input type="date" id="dob" name="dob" required class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2 text-white focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <div class="form-group">
                <label for="timezone" class="block text-gray-300 mb-1">1.3 Country and Time Zone</label>
                <input type="text" id="timezone" name="timezone" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2 text-white focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <div class="form-group">
                <label for="facebook-name" class="block text-gray-300 mb-1">1.4 What is your Facebook name</label>
                <input type="text" id="facebook-name" name="facebook-name" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2 text-white focus:ring-blue-500 focus:border-blue-500">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="form-group">
                <label for="discord-username" class="block text-gray-300 mb-1">1.5 Discord Username (e.g., User#1234)*</label>
                <input type="text" id="discord-username" name="discord-username" required class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2 text-white focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <div class="form-group">
                <label for="discord-id" class="block text-gray-300 mb-1">1.6 Discord ID*</label>
                <input type="text" id="discord-id" name="discord-id" required class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2 text-white focus:ring-blue-500 focus:border-blue-500">
            </div>
        </div>

        <div class="form-group">
            <label for="roleplay-explanation" class="block text-gray-300 mb-1">1.7 Explain what is Roleplay in your own words.*</label>
            <textarea id="roleplay-explanation" name="roleplay-explanation" rows="4" required class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2 text-white focus:ring-blue-500 focus:border-blue-500"></textarea>
        </div>
        
        <div class="form-group">
            <label for="new-life-rules" class="block text-gray-300 mb-1">1.8 What are New life rules? explain in your own words? (Please explain one situation)*</label>
            <textarea id="new-life-rules" name="new-life-rules" rows="4" required class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2 text-white focus:ring-blue-500 focus:border-blue-500"></textarea>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="form-group">
                <label class="block text-gray-300 mb-2">1.9 Previous role-play experience?*</label>
                <div class="radio-group flex gap-4">
                    <label class="inline-flex items-center text-white">
                        <input type="radio" name="rp-experience" value="Yes" required class="form-radio text-blue-600 bg-slate-900 border-slate-700 focus:ring-blue-500"> Yes
                    </label>
                    <label class="inline-flex items-center text-white">
                        <input type="radio" name="rp-experience" value="No" class="form-radio text-blue-600 bg-slate-900 border-slate-700 focus:ring-blue-500"> No
                    </label>
                </div>
            </div>
            
            <div class="form-group">
                <label class="block text-gray-300 mb-2">1.10 Read the Region City rule book?*</label>
                <div class="radio-group flex gap-4">
                    <label class="inline-flex items-center text-white">
                        <input type="radio" name="read-rules" value="Yes" required class="form-radio text-blue-600 bg-slate-900 border-slate-700 focus:ring-blue-500"> Yes
                    </label>
                    <label class="inline-flex items-center text-white">
                        <input type="radio" name="read-rules" value="No" class="form-radio text-blue-600 bg-slate-900 border-slate-700 focus:ring-blue-500"> No
                    </label>
                </div>
            </div>
            
            <div class="form-group">
                <label class="block text-gray-300 mb-2">1.11 Agree to follow server rules?*</label>
                <div class="radio-group flex gap-4">
                    <label class="inline-flex items-center text-white">
                        <input type="radio" name="agree-rules" value="Yes" required class="form-radio text-blue-600 bg-slate-900 border-slate-700 focus:ring-blue-500"> Yes
                    </label>
                    <label class="inline-flex items-center text-white">
                        <input type="radio" name="agree-rules" value="No" class="form-radio text-blue-600 bg-slate-900 border-slate-700 focus:ring-blue-500"> No
                    </label>
                </div>
            </div>
        </div>

        <div class="form-submit mt-4">
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200 uppercase tracking-wider">
                <i class="fas fa-paper-plane mr-2"></i> SUBMIT APPLICATION
            </button>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
print_layout('Whitelist Application', $content, 'apply');
?>