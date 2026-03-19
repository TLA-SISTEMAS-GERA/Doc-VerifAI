// Referencias a elementos del DOM
const chatDiv = document.getElementById('chat');
const messageInput = document.getElementById('message');
const sendButton = document.getElementById('send');

let conversationHistory = [];

function appendMessage(role, text, isStreaming = false) {
    let msgDiv;
    if (isStreaming && role === 'model') {
        // Reutilizar el último div del modelo si existe
        const lastDiv = chatDiv.lastElementChild;
        if (lastDiv && lastDiv.className === 'model') {
            msgDiv = lastDiv;
        } else {
            msgDiv = document.createElement('div');
            msgDiv.className = role;
            chatDiv.appendChild(msgDiv);
        }
    } else {
        msgDiv = document.createElement('div');
        msgDiv.className = role;
        chatDiv.appendChild(msgDiv);
    }
    msgDiv.textContent = text;
    chatDiv.scrollTop = chatDiv.scrollHeight;
    return msgDiv;
}

async function sendMessage() {
    const userText = messageInput.value.trim();
    if (!userText) return;

    // Mostrar mensaje del usuario
    appendMessage('user', userText);
    conversationHistory.push({ role: 'user', text: userText });

    messageInput.value = '';

    // Crear div para la respuesta del modelo (streaming)
    const modelDiv = appendMessage('model', '', true);

    const formData = new FormData();
    formData.append('messages', userText);

    try {
        const response = await fetch('api.php', {
            method: 'POST',
            body: formData
        });

        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status}`);
        }

        const reader = response.body.getReader();
        const decoder = new TextDecoder('utf-8');
        while (true) {
            const { done, value } = await reader.read();
            if (done) break;

            // Decodificar el fragmento de texto y añadirlo al div
            const chunk = decoder.decode(value, { stream: true });
            const json = chunk.substring(6); // Eliminar "data: " al inicio
            const response = JSON.parse(json);
            let respuestaIA = response.candidates[0].content.parts[0].text;
            console.log('Chunk recibido:', respuestaIA);
            modelDiv.textContent += respuestaIA;
            chatDiv.scrollTop = chatDiv.scrollHeight;
        }
        console.log('Respuesta completa:');
        // Al terminar, guardar en el historial
        conversationHistory.push({ role: 'model', text: modelDiv.textContent });

    } catch (error) {
        console.error('Error:', error);
        modelDiv.textContent = '[Error al conectar con el servidor]';
        conversationHistory.push({ role: 'model', text: '[Error]' });
    }
}
// Eventos
sendButton.addEventListener('click', sendMessage);
messageInput.addEventListener('keypress', (e) => {
    if (e.key === 'Enter') sendMessage();
});