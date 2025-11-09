document.addEventListener('DOMContentLoaded', () => {
    const chatBox = document.getElementById('chat-messages');
    const messageInput = document.getElementById('message-input');
    const chatForm = document.getElementById('chat-form');
    let activeReceiver = null;

    document.querySelectorAll('.chat-item').forEach(item => {
        item.addEventListener('click', () => {
            activeReceiver = item.dataset.id;
            loadMessages(activeReceiver);
        });
    });

    chatForm.addEventListener('submit', e => {
        e.preventDefault();
        if (!activeReceiver || !messageInput.value.trim()) return;

        fetch('/chat/sendMessage', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                receiver_id: activeReceiver,
                message: messageInput.value.trim()
            })
        }).then(() => {
            messageInput.value = '';
            loadMessages(activeReceiver);
        });
    });

    function loadMessages(receiverId) {
        fetch(`/chat/getMessages/${receiverId}`)
            .then(res => res.json())
            .then(messages => {
                chatBox.innerHTML = messages.map(m => `
                    <div class="mb-2 ${m.sender_id == receiverId ? 'text-start' : 'text-end'}">
                        <div class="d-inline-block p-2 rounded ${m.sender_id == receiverId ? 'bg-white' : 'bg-primary text-white'}">
                            ${m.message}
                        </div>
                    </div>
                `).join('');
                chatBox.scrollTop = chatBox.scrollHeight;
            });
    }

    // realtime (polling)
    setInterval(() => {
        if (activeReceiver) loadMessages(activeReceiver);
    }, 4000);
});
