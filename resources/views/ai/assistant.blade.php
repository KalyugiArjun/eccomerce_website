@extends('layouts.app')

@section('title', 'ShopKart AI Shopping Assistant - Ask Anything in Hindi or English')

@section('content')
<div class="container py-3">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm p-4">
                <!-- Assistant Header -->
                <div class="d-flex align-items-center gap-3 pb-3 mb-3 border-bottom">
                    <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-robot fs-4"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-0">ShopKart AI Shopping Assistant</h4>
                        <small class="text-muted">Ask anything in English or Hinglish &bull; Grounded strictly in authentic database products</small>
                    </div>
                </div>

                <!-- Chat History Window -->
                <div id="chatWindow" class="sk-chat-box mb-3">
                    <!-- Bot Welcome Message -->
                    <div class="sk-chat-message bot">
                        <div class="sk-chat-bubble">
                            Namaste! 🙏 Main hoon ShopKart ka AI Shopping Assistant. Aap mujhe bataiye aapko kya chahiye, jaise:
                            <br><em class="text-muted">"Mujhe ₹2000 ke andar college ke liye black shoes chahiye"</em> ya 
                            <br><em class="text-muted">"Show me wireless ANC earbuds under ₹1500 with fast delivery"</em>.
                        </div>
                    </div>
                </div>

                <!-- Quick Prompt Chips -->
                <div class="mb-3">
                    <div class="small text-muted mb-2 fw-semibold">Try asking:</div>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="sendQuickQuery('Mujhe ₹2000 ke andar black shoes chahiye')">
                            👟 "Mujhe ₹2000 ke andar black shoes chahiye"
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="sendQuickQuery('Under ₹1500 wireless earbuds dikhao')">
                            🎧 "Under ₹1500 wireless earbuds dikhao"
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="sendQuickQuery('Pure cotton chikan kurta in Lucknow')">
                            🥻 "Pure cotton chikan kurta in Lucknow"
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="sendQuickQuery('Smartwatch under 3500 with AMOLED')">
                            ⌚ "Smartwatch under 3500 with AMOLED"
                        </button>
                    </div>
                </div>

                <!-- Chat Input Form -->
                <form id="aiChatForm" class="d-flex gap-2">
                    <input type="text" id="aiMessageInput" class="form-control" placeholder="Ask ShopKart AI (e.g. 'Black shoes under 2000')..." autocomplete="off" required>
                    <button type="submit" id="sendBtn" class="btn btn-danger px-4 fw-bold">
                        <i class="bi bi-send-fill me-1"></i> Ask
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const chatWindow = document.getElementById('chatWindow');
    const aiChatForm = document.getElementById('aiChatForm');
    const aiMessageInput = document.getElementById('aiMessageInput');
    const sendBtn = document.getElementById('sendBtn');
    let history = [];

    function sendQuickQuery(text) {
        aiMessageInput.value = text;
        submitQuery(text);
    }

    aiChatForm.addEventListener('submit', function (e) {
        e.preventDefault();
        const text = aiMessageInput.value.trim();
        if (text) {
            submitQuery(text);
        }
    });

    async function submitQuery(userMessage) {
        aiMessageInput.value = '';
        sendBtn.disabled = true;

        // Append user bubble
        appendMessage('user', userMessage);

        // Show typing indicator
        const typingId = 'typing_' + Date.now();
        const typingEl = document.createElement('div');
        typingEl.id = typingId;
        typingEl.className = 'sk-chat-message bot';
        typingEl.innerHTML = `<div class="sk-chat-bubble text-muted"><i class="bi bi-three-dots"></i> Searching ShopKart catalog...</div>`;
        chatWindow.appendChild(typingEl);
        chatWindow.scrollTop = chatWindow.scrollHeight;

        try {
            const response = await fetch("{{ route('ai.query') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    message: userMessage,
                    history: history
                })
            });

            const data = await response.json();
            document.getElementById(typingId)?.remove();

            if (data.success) {
                appendBotReply(data.reply, data.products);
                history.push({ user: userMessage, bot: data.reply });
            } else {
                appendMessage('bot', 'Sorry, I could not process that request. Please try again.');
            }
        } catch (err) {
            document.getElementById(typingId)?.remove();
            appendMessage('bot', 'Network error. Please try again.');
        } finally {
            sendBtn.disabled = false;
            aiMessageInput.focus();
        }
    }

    function appendMessage(sender, text) {
        const msgDiv = document.createElement('div');
        msgDiv.className = `sk-chat-message ${sender}`;
        msgDiv.innerHTML = `<div class="sk-chat-bubble">${escapeHtml(text)}</div>`;
        chatWindow.appendChild(msgDiv);
        chatWindow.scrollTop = chatWindow.scrollHeight;
    }

    function appendBotReply(replyText, products) {
        const msgDiv = document.createElement('div');
        msgDiv.className = 'sk-chat-message bot';

        let html = `<div class="sk-chat-bubble"><div>${escapeHtml(replyText)}</div>`;

        if (products && products.length > 0) {
            html += `<div class="row row-cols-1 row-cols-sm-2 g-2 mt-2">`;
            products.forEach(p => {
                html += `
                    <div class="col">
                        <div class="card p-2 border h-100 shadow-sm">
                            <div class="d-flex gap-2">
                                <img src="${p.thumbnail || 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=100&h=100&fit=crop'}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                                <div class="flex-grow-1 overflow-hidden">
                                    <strong class="d-block text-truncate small">${escapeHtml(p.name)}</strong>
                                    <div class="small fw-bold text-dark">₹${p.effective_price.toLocaleString('en-IN')}</div>
                                    <small class="text-success" style="font-size: 11px;">⚡ ${p.delivery_minutes}m delivery</small>
                                </div>
                            </div>
                            <a href="${p.url}" class="btn btn-outline-dark btn-sm mt-2 py-0" style="font-size: 11px;">View Product &rarr;</a>
                        </div>
                    </div>
                `;
            });
            html += `</div>`;
        }

        html += `</div>`;
        msgDiv.innerHTML = html;
        chatWindow.appendChild(msgDiv);
        chatWindow.scrollTop = chatWindow.scrollHeight;
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
</script>
@endpush
