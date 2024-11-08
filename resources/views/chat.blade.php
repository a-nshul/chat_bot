<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ChatBot</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.0.2/dist/tailwind.min.css" rel="stylesheet">
    <style>
        /* Custom Styles for Smooth Animations */
        .chat-box {
            max-height: 500px;
            overflow-y: auto;
        }

        .message-enter {
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .message-exit {
            animation: slideOut 0.3s ease-out;
        }

        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }

            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
@keyframes messageEnter {
    0% { transform: translateY(20px); opacity: 0; }
    100% { transform: translateY(0); opacity: 1; }
}

.message-enter {
    animation: messageEnter 0.4s ease-in-out;
}

/* Message Styling */
.message-box {
    padding: 10px;
    margin: 8px 0;
    border-radius: 8px;
    max-width: 80%;
}

.user-message {
    border: 2px solid #3b82f6;
    /* background-color: #e0f2fe; */
    align-self: flex-end;
}

.bot-message {
    border: 2px solid #10b981;
    align-self: flex-start;
}
/* Animations */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes fadeOut {
    from { opacity: 1; transform: translateY(0); }
    to { opacity: 0; transform: translateY(10px); }
}

/* Delete Button Animation */
.delete-animate {
    animation: fadeOut 0.3s ease-in-out forwards;
}

    </style>
</head>
<body class="bg-gray-100 font-sans h-screen">

<div class="flex h-full">
    <!-- Left Side: Chat History and Search -->
    <div class="w-1/3 bg-black text-white p-4 rounded-lg border border-gray-300 shadow-lg flex flex-col">
        <!-- Search Bar -->
        <h3 class="text-xl font-semibold text-center mb-4">Chat History</h3>
        <div id="chatHistory" class="space-y-4 chat-box flex-1 overflow-y-auto">
            <!-- Chat history will dynamically populate here -->
        </div>
        <button onclick="newChat()" class="mt-4 bg-gray-500 text-white p-2 rounded-full hover:bg-gray-400 transition ease-in-out duration-200 w-full">
            New Chat
        </button>
    </div>

    <!-- Right Side: Chat Messages -->
    <div class="w-2/3 bg-gray-800 text-white p-4 rounded-lg border border-gray-300 flex flex-col">
        <div id="messageArea" class="flex-1 overflow-y-auto space-y-4 ">
            <!-- Dynamic messages will be appended here -->
        </div>

        <!-- Message Input & File Attach -->
        <div class="flex items-center space-x-4 mt-4">
            <input type="file" id="fileUpload" class="hidden" />
            <button onclick="document.getElementById('fileUpload').click()" class="bg-blue-500 text-white p-2 rounded-full hover:bg-blue-400 transition ease-in-out duration-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 12.75l9-9m0 0l9 9m-9-9v18"></path>
                </svg>
            </button>
            <input type="text" id="userMessage" class="w-full p-2 border border-gray-300 bg-gray-600 text-white rounded-lg" placeholder="Type your message...">
            <button onclick="sendMessage()" class="bg-green-500 text-white p-2 rounded-full hover:bg-green-400 transition ease-in-out duration-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v12l8-4l8 4V3H5z"></path>
                </svg>
            </button>
        </div>
    </div>
</div>

<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (!csrfToken) {
        console.error('CSRF Token not found');
    }

    let chatHistory = [];
    let currentChatId = null;

    // Function to render chat history
// function renderChatHistory() {
//     const chatHistoryDiv = document.getElementById('chatHistory');
//     chatHistoryDiv.innerHTML = ''; 

//     chatHistory.forEach((item, index) => {
//         const div = document.createElement('div');
//         div.classList.add('flex', 'items-center', 'justify-between'); 

//         // Check if the message exists and render the correct message
//         const messageText = item.messages && item.messages.length > 0 ? item.messages[0].text : "No Message";
//         const messageDiv = document.createElement('div');
//         messageDiv.textContent = `${item.sender === 'user' ? 'You' : 'Bot'}: ${messageText}`;
//         messageDiv.classList.add(item.sender === 'user' ? 'text-blue-500' : 'text-green-500');
//         messageDiv.classList.add('cursor-pointer');
//         messageDiv.setAttribute('data-chat-id', index);
//         messageDiv.addEventListener('click', () => loadChatHistory(index));

//         // Add delete button
//         const deleteButton = document.createElement('button');
//         deleteButton.innerHTML = '🗑️'; 
//         deleteButton.classList.add('text-red-500', 'ml-4', 'hover:text-red-700', 'transition', 'duration-200');
//         deleteButton.addEventListener('click', (e) => {
//             e.stopPropagation();
//             deleteChat(index); 
//         });

//         div.appendChild(messageDiv);
//         div.appendChild(deleteButton);
//         chatHistoryDiv.appendChild(div);
//     });
// }
function renderChatHistory() {
    const chatHistoryDiv = document.getElementById('chatHistory');
    chatHistoryDiv.innerHTML = ''; 

    chatHistory.forEach((item, index) => {
        const div = document.createElement('div');
        div.classList.add('flex', 'items-center', 'justify-between', 'message-box'); // Add animation and box styling

        // Add specific styling for user and bot messages
        if (item.sender === 'user') {
            div.classList.add('user-message');
        } else {
            div.classList.add('bot-message');
        }

        // Check if the message exists and render the correct message
        const messageText = item.messages && item.messages.length > 0 ? item.messages[0].text : "No Message";
        const messageDiv = document.createElement('div');
        messageDiv.textContent = `${item.sender === 'user' ? 'You' : 'Bot'}: ${messageText}`;
        messageDiv.classList.add('cursor-pointer');
        messageDiv.setAttribute('data-chat-id', index);
        messageDiv.addEventListener('click', () => loadChatHistory(index));

        // Add delete button with animation
        const deleteButton = document.createElement('button');
        deleteButton.innerHTML = '🗑️'; 
        deleteButton.classList.add('text-red-500', 'ml-4', 'hover:text-red-700', 'transition', 'duration-200');
        deleteButton.addEventListener('click', (e) => {
            e.stopPropagation();
            deleteChat(index, div); // Pass the div to animate before deletion
        });

        div.appendChild(messageDiv);
        div.appendChild(deleteButton);
        chatHistoryDiv.appendChild(div);
    });
}

function deleteChat(index, element) {
    // Apply fade-out animation
    element.classList.add('delete-animate');
    
    // Wait for the animation to complete, then delete
    setTimeout(() => {
        chatHistory.splice(index, 1); // Remove from chat history
        renderChatHistory(); // Re-render chat history
    }, 300);
}

// Initial rendering of chat history
renderChatHistory();


    // Search Functionality
    document.getElementById('searchChat').addEventListener('input', (e) => {
        const query = e.target.value.toLowerCase();
        const filteredHistory = chatHistory.filter(item => item.message.toLowerCase().includes(query));
        renderChatHistory(filteredHistory);
    });

    // Function to delete chat
    function deleteChat(chatId) {
        chatHistory.splice(chatId, 1); 
        renderChatHistory(); 
        const messageArea = document.getElementById('messageArea');
        messageArea.innerHTML = ''; 
        if (currentChatId === chatId) {
            currentChatId = null; 
        }
    }

    // Function to load a specific chat
    function loadChatHistory(chatId) {
        currentChatId = chatId;
        const selectedChat = chatHistory[chatId];
        const messageArea = document.getElementById('messageArea');
        messageArea.innerHTML = ''; 
        selectedChat.messages.forEach(msg => {
            appendMessage(msg.text, msg.sender);
        });
    }

    // Send message to Laravel backend
function sendMessage() {
    const message = document.getElementById('userMessage').value;
    const file = document.getElementById('fileUpload').files[0];

    if (message.trim() !== "" || file) {
        if (currentChatId === null) {
            currentChatId = chatHistory.length;
            chatHistory.push({ sender: 'user', message: message, messages: [] }); // Ensure 'message' is set correctly
        }

        appendMessage("You: " + message, "user");
        chatHistory[currentChatId].messages.push({ sender: 'user', text: message });

        const formData = new FormData();
        formData.append('message', message);
        if (file) {
            formData.append('file', file);
        }

        fetch('/send-message', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
            },
            body: formData,
        })
        .then(response => response.json())
        .then(data => {
            const botMessage = data.reply || "Sorry, I couldn't understand that.";
            appendMessage("ChatBot: " + botMessage, "bot");
            chatHistory[currentChatId].messages.push({ sender: 'bot', text: botMessage });
            renderChatHistory(); // Ensure the chat history is re-rendered
        })
        .catch(error => {
            console.error('Error:', error);
            appendMessage("ChatBot: Sorry, there was an error processing your message.", "bot");
            chatHistory[currentChatId].messages.push({ sender: 'bot', text: "Sorry, there was an error processing your message." });
        });
        document.getElementById('userMessage').value = '';
    }
}

    // Append message to chat area
    // function appendMessage(text, sender) {
    //     const messageArea = document.getElementById('messageArea');
    //     const messageDiv = document.createElement('div');
    //     messageDiv.classList.add(sender === 'user' ? 'text-blue-500' : 'text-green-500');
    //     messageDiv.textContent = text;
    //     messageDiv.classList.add('message-enter');
    //     messageArea.appendChild(messageDiv);
    //     messageArea.scrollTop = messageArea.scrollHeight;
    // }
    function appendMessage(text, sender) {
    const messageArea = document.getElementById('messageArea');
    const messageDiv = document.createElement('div');

    // Apply common styles and animation
    messageDiv.classList.add('message-box', 'message-enter');

    // Add specific styles for user or bot messages
    if (sender === 'user') {
        messageDiv.classList.add('user-message');
    } else {
        messageDiv.classList.add('bot-message');
    }

    // Set message text
    messageDiv.textContent = text;

    // Append message and scroll to the bottom
    messageArea.appendChild(messageDiv);
    messageArea.scrollTop = messageArea.scrollHeight;
}

    // New Chat function
    function newChat() {
        currentChatId = null; 
        document.getElementById('messageArea').innerHTML = ''; 
    }

    // Initial chat history render
    renderChatHistory();
</script>
</body>
</html>
