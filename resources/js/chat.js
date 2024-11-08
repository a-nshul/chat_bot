document.getElementById('send-message').addEventListener('click', function() {
    let message = document.getElementById('user-message').value;
    if (message.trim() === '') return;

    // Display the user's message
    addMessageToChat(message, 'user');

    // Send the message to the Laravel backend
    fetch('/send-message', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ message: message })
    })
    .then(response => response.json())
    .then(data => {
        // Display the bot's response
        addMessageToChat(data.botResponse, 'bot');
    });

    // Clear the input field
    document.getElementById('user-message').value = '';
});

function addMessageToChat(message, sender) {
    let chatHistory = document.getElementById('chat-history');
    let messageDiv = document.createElement('div');
    messageDiv.classList.add(sender);
    messageDiv.textContent = message;
    chatHistory.appendChild(messageDiv);
    chatHistory.scrollTop = chatHistory.scrollHeight;
}
