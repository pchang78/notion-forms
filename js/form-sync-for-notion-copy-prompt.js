document.getElementById('copyButton').addEventListener('click', function () {
    event.preventDefault();
    const textarea = document.getElementById('ai_prompt');
    const ai_description = document.getElementById('ai_description');
    textarea.select();
    textarea.setSelectionRange(0, 99999); // For mobile devices

    ai_description_value = ai_description.value.trim();
    prompt_value = textarea.value;

    if(ai_description_value.length === 0) {
        ai_description_value = "clean";
    }
    prompt_value = prompt_value + "Generate CSS to create a " + ai_description_value + " online form.  Have it scoped to #form-sync-for-notion-container";
    // Copy the text to the clipboard
    navigator.clipboard.writeText(prompt_value)
      .then(() => {
        alert('Prompt copied to clipboard!  Paste this prompt into your AI tool and then paste the generated CSS code into the text box above.');
      })
      .catch(err => {
        console.error('Failed to copy text: ', err);
      });
  });