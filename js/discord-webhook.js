/**
 * Discord Webhook Integration for Legion Suiper Application Forms
 * This file handles sending application form data to Discord via webhooks
 */

// Configuration for different application types
const webhookConfig = {
    police: {
        url: "https://discord.com/api/webhooks/1444606224929914940/jOkfmwrZGwZnCs5OnBHAVQCythay0qpCpht7wZIqYwmmWZeQAPAlSifgFkiAkBuuM-W-", // Replace with actual Discord webhook URL
        color: 3447003, // Blue color for police
        title: "New Police Application Submitted",
        thumbnail: "https://cdn.discordapp.com/attachments/1438931576414273697/1444607109672210483/lgpd.png?ex=692d52b6&is=692c0136&hm=a533efaafc223fd46294b3c65e6c67c0e703fbc5ea05a1e1dbee2a671d7b3f03&"
    },
    medical: {
        url: "https://discord.com/api/webhooks/1444606321273077923/REiCfs7F7-6Lkn3_j52Ct-JLRC3AmcoXrwr7i8bx38-QKyWvGiXLCmuEo8enbTOWSvsJ", // Replace with actual Discord webhook URL
        color: 15158332, // Red color for medical
        title: "New Medical Application Submitted",
        thumbnail: "https://cdn.discordapp.com/attachments/1438931576414273697/1444607109319626893/lgmd.png?ex=692d52b6&is=692c0136&hm=012f443a8fea7780c66de876c226f438cee2a3091d71ddadaa0c55ec1bdd6989&"
    },
    whitelist: {
        url: "https://discord.com/api/webhooks/1444606131509919795/UfwL0SWVOEb2mqT4CR7bmjKTfL8SkG-MmD4IsFKxDFUhDJBxybpZScocaXKGA_FzgEvo", // Replace with actual Discord webhook URL
        color: 7506394, // Green color for whitelist
        title: "New Whitelist Application Submitted",
        thumbnail: "https://cdn.discordapp.com/attachments/1438931576414273697/1444607109974065272/logo.png?ex=692d52b6&is=692c0136&hm=b31f93352f3d51c50b7fe4191e93992110cd7e6d085716f53390d042a62a3490&" // Replace with actual thumbnail URL
    }
};

/**
 * Send application data to Discord webhook
 * @param {string} type - Application type (police, medical)
 * @param {Object} formData - Form data to send
 * @returns {Promise} - Promise resolving to the fetch response
 */
async function sendToDiscord(type, formData) {
    // Get config for this application type
    const config = webhookConfig[type];
    if (!config) {
        throw new Error(`Invalid application type: ${type}`);
    }

    // Create fields array from form data
    const fields = [];
    for (const [key, value] of Object.entries(formData)) {
        // Format the key for better readability
        const formattedKey = key
            .split('-')
            .map(word => word.charAt(0).toUpperCase() + word.slice(1))
            .join(' ');

        fields.push({
            name: formattedKey,
            value: value,
            inline: false
        });
    }

    // Create the webhook payload
    const payload = {
        embeds: [{
            title: config.title,
            color: config.color,
            thumbnail: {
                url: config.thumbnail
            },
            fields: fields,
            timestamp: new Date().toISOString(),
            footer: {
                text: "Asterisk Roleplay Application System"
            }
        }]
    };

    try {
        // Send the webhook request
        const response = await fetch(config.url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(payload)
        });

        if (!response.ok) {
            throw new Error(`Discord webhook error: ${response.status}`);
        }

        return response;
    } catch (error) {
        console.error("Error sending to Discord:", error);
        throw error;
    }
}

/**
 * Process form submission and send to Discord
 * @param {HTMLFormElement} form - The form element
 * @param {string} type - Application type (police, medical)
 * @returns {Promise} - Promise resolving when submission is complete
 */
async function processFormSubmission(form, type) {
    // Create an object from form data
    const formData = {};
    const formElements = form.elements;
    
    for (let i = 0; i < formElements.length; i++) {
        const element = formElements[i];
        if (element.name && element.name !== "" && element.type !== "submit") {
            formData[element.name] = element.value;
        }
    }

    // Add submission timestamp
    formData['submission-time'] = new Date().toLocaleString();
    
    try {
        // Send to Discord
        await sendToDiscord(type, formData);
        return true;
    } catch (error) {
        console.error("Form submission error:", error);
        return false;
    }
}

// Export functions for use in main.js
window.DiscordWebhook = {
    processFormSubmission
};