<?php
// Include the database configuration file
require_once 'config.php';

// Define variables and initialize with empty values
$email = $fullName = $dob = $timezone = $facebookName = $discordUsername = $discordId = $serial = $rpExplanation = $nlrExplanation = $rpExperience = $readRules = $agreeRules = "";
$submission_status = "";
$conn_is_open = true; // Flag to track connection status

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_application'])) {

    // 1. Sanitize and validate inputs
    $email              = trim($_POST["email"]);
    $fullName           = trim($_POST["full-name"]);
    $dob                = trim($_POST["dob"]);
    $timezone           = trim($_POST["timezone"]);
    $facebookName       = trim($_POST["facebook-name"]);
    $discordUsername    = trim($_POST["discord-username"]);
    $discordId          = trim($_POST["discord-id"]);
    $serial             = trim($_POST["serial"]);
    $rpExplanation      = trim($_POST["roleplay-explanation"]);
    $nlrExplanation     = trim($_POST["new-life-rules"]);
    $rpExperience       = trim($_POST["rp-experience"]);
    $readRules          = trim($_POST["read-rules"]);
    $agreeRules         = trim($_POST["agree-rules"]);

    // Simple validation (more robust validation should be added)
    if (empty($email) || empty($fullName) || empty($dob) || empty($discordUsername) || empty($discordId) || empty($serial)) {
        $submission_status = "<div class='message error'>Error: Please fill in all required fields.</div>";
    } else {
        // 2. Prepare an INSERT statement using a prepared statement for security
        $sql = "INSERT INTO whitelistapp (email, fullName, dob, timezone, facebookName, discordUsername, discordId, serial, rpExplanation, nlrExplanation, rpExperience, readRules, agreeRules) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        if ($stmt = $conn->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            // Data types: s = string, d = double, i = integer, b = blob
            $stmt->bind_param("sssssssssssss", 
                $email, 
                $fullName, 
                $dob, 
                $timezone, 
                $facebookName, 
                $discordUsername, 
                $discordId, 
                $serial, 
                $rpExplanation, 
                $nlrExplanation, 
                $rpExperience, 
                $readRules, 
                $agreeRules
            );

            // 3. Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Success: Application submitted
                $submission_status = "<div class='message success'>Application submitted successfully! We will review it shortly.</div>";
            
                // Clear form variables after successful submission
                $email = $fullName = $dob = $timezone = $facebookName = $discordUsername = $discordId = $serial = $rpExplanation = $nlrExplanation = $rpExperience = $readRules = $agreeRules = "";

            } else {
                // Check for duplicate entry error (specifically on the 'serial' UNIQUE key)
                if ($conn->errno == 1062) {
                    $submission_status = "<div class='message error'>Submission Failed: This MTA Serial has already been used to submit an application.</div>";
                } else {
                    // Other errors
                    $submission_status = "<div class='message error'>ERROR: Could not execute query. Please try again later. (" . $stmt->error . ")</div>";
                }
            }

            // Close statement
            $stmt->close();
        } else {
            $submission_status = "<div class='message error'>ERROR: Could not prepare statement. Please check the database structure.</div>";
        }
    }
}

// Re-initialize connection if it was closed or failed initially (only needed for the Admin Utility, which is now removed)
// The connection should remain open throughout the script execution unless explicitly closed.
// We will only close the connection once at the very end of the PHP block.

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Whitelist Application - Asterisk Roleplay</title>
    <!-- NOTE: Assuming your CSS files 'css/style.css' and external libraries are available -->
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Basic styles for PHP messages - customize these in your main CSS */
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-weight: bold;
            text-align: center;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <!-- Header Section (from original HTML) -->
    <header>
        <div class="container">
            <div class="logo">
                <a href="index.html">
                    <img src="images/logo.png" alt="Legion Suiper Logo">
                    <span>Asterisk Roleplay</span>
                </a>
            </div>
            <nav>
                <ul>
                    <li><a href="index.html">Home</a></li>
                    <li><a href="about.html">About</a></li>
                    <li><a href="rules.html">Rules</a></li>
                    <li><a href="join.html">Join</a></li>
                    <li><a href="whitelist-application.php" class="active">Whitelist</a></li>
                    <li><a href="applications.html">Applications</a></li>
                </ul>
            </nav>
            <div class="cta-button">
                <a href="join.html" class="btn-primary">Join Now</a>
            </div>
        </div>
    </header>

<!-- Hero Section -->
<section class="hero">
    <!-- Background Video -->
    <video autoplay muted loop playsinline class="hero-video">
        <source src="videos/background.mp4" type="video/mp4">
        Your browser does not support the video tag.
    </video>
    <!-- Overlay -->
    <div class="overlay"></div>
    <!-- Content -->
    <div class="container">
        <div class="hero-content">
            <h1>WHITELIST Asterisk Roleplay</h1>
            <p>Experience the ultimate GTA V roleplay server with immersive stories, content, and realistic gameplay</p>
            <div class="hero-buttons">
                <a href="join.html" class="btn-primary">How To Join</a>
                <a href="https://discord.gg/DugPSevG5U" class="btn-secondary">Join Discord</a>
            </div>
        </div>
    </div>
</section>

    <!-- Whitelist Application Form -->
    <section class="application-form-section dark-theme">
        <div class="container">
            
            <div class="application-intro">
                <h2>Whitelist Application</h2>
                <p>Complete this form to apply for whitelist access to our roleplay server. Please provide accurate information to help us process your application quickly.</p>
            </div>
            
            <!-- PHP Submission Status Display -->
            <?php echo $submission_status; ?>

            <!-- FORM ACTION POINTS TO THE SAME FILE -->
            <form id="whitelist-application-form" class="application-form specialized-form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                
                <div class="form-group">
                    <label for="email">Email*</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="full-name">1.1 Full Name (Real)*</label>
                    <input type="text" id="full-name" name="full-name" value="<?php echo htmlspecialchars($fullName); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="dob">1.2 Date of Birth* (Month, day, year)</label>
                    <input type="date" id="dob" name="dob" value="<?php echo htmlspecialchars($dob); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="timezone">1.3 Country and Time Zone</label>
                    <input type="text" id="timezone" name="timezone" value="<?php echo htmlspecialchars($timezone); ?>">
                </div>
                                
                <div class="form-group">
                    <label for="facebook-name">1.4 What is your Facebook name</label>
                    <input type="text" id="facebook-name" name="facebook-name" value="<?php echo htmlspecialchars($facebookName); ?>">
                </div>
                
                <div class="form-group">
                    <label for="discord-username">1.5 Discord Username (e.g., User#1234)*</label>
                    <input type="text" id="discord-username" name="discord-username" value="<?php echo htmlspecialchars($discordUsername); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="discord-id">1.6 Discord ID*</label>
                    <input type="text" id="discord-id" name="discord-id" value="<?php echo htmlspecialchars($discordId); ?>" required>
                </div>

                <!-- MTA SERIAL FIELD -->
                <div class="form-group">
                    <label for="serial">1.7 MTA Serial* (Unique 64-character serial)</label>
                    <input type="text" id="serial" name="serial" maxlength="64" value="<?php echo htmlspecialchars($serial); ?>" required placeholder="e.g., A1B2C3D4E5F67890...">
                </div>

                <div class="form-group">
                    <label for="roleplay-explanation">1.8 Explain what is Roleplay in your own words.</label>
                    <textarea id="roleplay-explanation" name="roleplay-explanation" rows="4"><?php echo htmlspecialchars($rpExplanation); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="new-life-rules">1.9 What are New life rules? explain in your own words? (Please explain one situation)</label>
                    <textarea id="new-life-rules" name="new-life-rules" rows="4"><?php echo htmlspecialchars($nlrExplanation); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>1.10 Do you have any previous role-play experience?*</label>
                    <div class="radio-group">
                        <label class="radio-label">
                            <input type="radio" name="rp-experience" value="Yes" <?php echo ($rpExperience == 'Yes') ? 'checked' : ''; ?> required> Yes
                        </label>
                        <label class="radio-label">
                            <input type="radio" name="rp-experience" value="No" <?php echo ($rpExperience == 'No') ? 'checked' : ''; ?>> No
                        </label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>1.11 Have you read the Region City rule book?*</label>
                    <div class="radio-group">
                        <label class="radio-label">
                            <input type="radio" name="read-rules" value="Yes" <?php echo ($readRules == 'Yes') ? 'checked' : ''; ?> required> Yes
                        </label>
                        <label class="radio-label">
                            <input type="radio" name="read-rules" value="No" <?php echo ($readRules == 'No') ? 'checked' : ''; ?>> No
                        </label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>1.12 Do you understand and agree to follow the server rules and guidelines?*</label>
                    <div class="radio-group">
                        <label class="radio-label">
                            <input type="radio" name="agree-rules" value="Yes" <?php echo ($agreeRules == 'Yes') ? 'checked' : ''; ?> required> Yes
                        </label>
                        <label class="radio-label">
                            <input type="radio" name="agree-rules" value="No" <?php echo ($agreeRules == 'No') ? 'checked' : ''; ?>> No
                        </label>
                    </div>
                </div>
                
                <div class="form-submit">
                    <!-- Note: The name attribute 'submit_application' is used by PHP to detect form submission -->
                    <button type="submit" name="submit_application" class="btn-primary">SUBMIT APPLICATION</button>
                </div>
            </form>
        </div>
    </section>

    <!-- Footer (from original HTML) -->
    <footer>
        <div class="container">
            <div class="footer-top">
                <div class="footer-logo">
                    <img src="images/logo.png" alt="Legion Suiper Logo">
                    <span>Asterisk Roleplay</span>
                </div>
                <div class="footer-links">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="index.html">Home</a></li>
                        <li><a href="about.html">About</a></li>
                        <li><a href="rules.html">Server Rules</a></li>
                        <li><a href="join.html">How to Join</a></li>
                        <li><a href="staff.html">Our Team</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>© 2025 Asterisk Roleplay. All Rights Reserved | Designed by POPA</p>
                <div class="social-links">
                    <a href="https://discord.gg/DugPSevG5U"><i class="fab fa-discord"></i></a>
                    <a href="https://twitter.com/Asterisk Roleplay"><i class="fab fa-twitter"></i></a>
                    <a href="https://instagram.com/Asterisk Roleplay"><i class="fab fa-instagram"></i></a>
                    <a href="https://youtube.com/Asterisk Roleplay"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
<?php
// Close the database connection once at the very end of the script execution.
if (isset($conn) && $conn && !$conn->connect_error) {
    $conn->close();
}
?>