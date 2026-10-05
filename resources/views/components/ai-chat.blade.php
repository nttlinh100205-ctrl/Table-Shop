// Shared chatbox AI channel. This partial is included inside DOMContentLoaded.
let chatMode = 'ai', sending = false, aiLoaded = false, greetingPending = false;
const aiMessages = [];
const channelDrafts = { ai: '', staff: '' };
const aiModeButton = document.getElementById('chat-mode-ai');
const staffModeButton = document.getElementById('chat-mode-staff');
const channelNote = document.getElementById('chat-mode-note');
function renderAiMessages() {
    if (chatMode !== 'ai') return;
    chatBox.replaceChildren();
    aiMessages.forEach(message => {
        const row = document.createElement('div');
        const label = document.createElement('div');
        label.className = 'chat-sender-label' + (message.user ? ' me' : '');
        label.textContent = message.user ? 'Bạn' : 'Trợ lý AI';
        const bubble = document.createElement('div');
        bubble.className = 'chat-bubble ' + (message.user ? 'me' : 'admin');
        bubble.style.whiteSpace = 'pre-wrap';
        bubble.style.overflowWrap = 'anywhere';
        // Allow only read-only shop pages; generated HTML is always treated as text.
        const pattern = /\[([^\]]+)\]\((https?:\/\/[^\s)]+)\)/g;
        let match, last = 0;
        while ((match = pattern.exec(message.text)) !== null) {
            bubble.append(document.createTextNode(message.text.slice(last, match.index)));
            let url; try { url = new URL(match[2]); } catch (e) {}
            if (url && url.origin === location.origin && (/^\/(?:user\/)?products\/\d+$/.test(url.pathname) || /^\/(?:user\/orders(?:\/\d+)?|user\/points|login|email\/verify)$/.test(url.pathname))) {
                const a = document.createElement('a'); a.href = url.href; a.textContent = match[1]; bubble.append(a);
            } else bubble.append(document.createTextNode(match[1]));
            last = pattern.lastIndex;
        }
        bubble.append(document.createTextNode(message.text.slice(last)));
        row.append(label, bubble); chatBox.append(row);
    });
    chatBox.scrollTop = chatBox.scrollHeight;
}
async function loadAiMessages() {
    renderAiMessages();
    if (aiLoaded || greetingPending) return;
    greetingPending = true;
    try {
        const response = await fetch(@json(route('ai.greeting')), {headers: {Accept: 'application/json'}});
        if (!response.ok) throw new Error();
        const data = await response.json();
        aiMessages.unshift({text: data.reply, user: false}); aiLoaded = true;
    } catch (e) {
        aiMessages.unshift({text: 'Chào bạn! Mình chỉ tư vấn sản phẩm và mua hàng tại Table Shop. Bạn cần tìm mẫu nào?', user: false});
        aiLoaded = true;
    } finally { greetingPending = false; renderAiMessages(); }
}
async function sendAiMessage(preset) {
    const fromPreset = typeof preset === 'string';
    const message = (fromPreset ? preset : input.value).trim();
    if (!message) return;
    setSending(true);
    aiMessages.push({text: message, user: true}); renderAiMessages();
    channelNote.textContent = 'AI đang kiểm tra câu hỏi và tư vấn…';
    const controller = new AbortController();
    const timeout = setTimeout(() => controller.abort(), 55000);
    try {
        const response = await fetch(@json(route('ai.send')), {
            method: 'POST', headers: {'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf},
            body: JSON.stringify({message}),
            signal: controller.signal,
        });
        if (response.status === 419) throw new Error('Phiên chat đã hết hạn. Vui lòng tải lại trang rồi gửi lại.');
        if (response.status === 429) throw new Error('Bạn gửi hơi nhanh. Vui lòng chờ một phút rồi thử lại.');
        const data = await response.json().catch(() => { throw new Error('Máy chủ chưa phản hồi được. Vui lòng thử lại hoặc chọn Nhân viên.'); });
        if (!response.ok) throw new Error(data.message || 'AI tạm thời không khả dụng. Bạn có thể chuyển sang Nhân viên.');
        aiMessages.push({text: data.reply, user: false});
        if (!fromPreset) input.value = '';
    } catch (error) {
        aiMessages.push({text: error.name === 'AbortError' ? 'AI phản hồi quá chậm. Vui lòng thử lại hoặc chọn Nhân viên.' : (error.message || 'Mất kết nối. Vui lòng thử lại hoặc chuyển sang Nhân viên.'), user: false});
    } finally {
        clearTimeout(timeout);
        setSending(false); renderAiMessages(); input.focus();
        channelNote.textContent = 'Hỗ trợ sản phẩm, đơn hàng, điểm và hạng thành viên. Câu hỏi tư vấn AI được gửi tới dịch vụ AI.';
    }
}
function switchChatMode(mode) {
    if (sending || mode === chatMode) return;
    channelDrafts[chatMode] = input.value;
    chatMode = mode; input.value = channelDrafts[mode];
    aiModeButton.setAttribute('aria-pressed', String(mode === 'ai'));
    staffModeButton.setAttribute('aria-pressed', String(mode === 'staff'));
    aiModeButton.className = 'btn btn-sm ' + (mode === 'ai' ? 'btn-dark' : 'btn-outline-dark');
    staffModeButton.className = 'btn btn-sm ' + (mode === 'staff' ? 'btn-dark' : 'btn-outline-dark');
    channelNote.textContent = mode === 'ai'
        ? 'Hỗ trợ sản phẩm, đơn hàng, điểm và hạng thành viên. Câu hỏi tư vấn AI được gửi tới dịch vụ AI.'
        : 'Bạn đang chat với nhân viên shop. Cần đăng nhập để gửi tin nhắn.';
    input.placeholder = mode === 'ai' ? 'Hỏi về sản phẩm, giá, giao hàng…' : 'Nhập lời nhắn cho nhân viên…';
    chatBox.replaceChildren(); loadMessages();
}
aiModeButton.onclick = () => switchChatMode('ai');
staffModeButton.onclick = () => switchChatMode('staff');
