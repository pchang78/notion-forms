

document.addEventListener('DOMContentLoaded', function () {
    const copyButton = document.getElementById('copy-shortcode-button');
    const inputField = document.getElementById('form-sync-for-notion-shortcode-input');
    const feedback = document.getElementById('copy-feedback');

    if (copyButton && inputField) {
        copyButton.addEventListener('click', function () {
            inputField.select(); // Highlight the text
            document.execCommand('copy'); // Copy the text
            feedback.style.display = 'inline'; // Show feedback
            setTimeout(() => feedback.style.display = 'none', 2000); // Hide feedback after 2 seconds
        });
    }
});