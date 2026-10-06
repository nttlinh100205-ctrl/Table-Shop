const fs = require('node:fs');
const vm = require('node:vm');
const assert = require('node:assert/strict');

// Exercise the actual preset click handler, send flow and polling renderer together.
const template = fs.readFileSync('resources/views/layouts/admin.blade.php', 'utf8');
const section = (start, end) => template.slice(template.indexOf(start), template.indexOf(end));
const helper = fs.readFileSync('resources/views/components/chat-message-state.blade.php', 'utf8');
const source = [
    helper,
    section('function renderMessages(messages)', 'function loadUsers('),
    section('function loadMessages()', '// ===== CÂU TRẢ LỜI MẪU'),
    section('const QUICK_REPLIES =', 'const quickWrap ='),
    section('function renderQuickReplies()', "document.getElementById('send-btn').onclick"),
].join('\n').replace(/@json\(route\('chat.session'\)\)/g, '"/chat/session"')
    .replace(/\{\{\s*\(int\) auth\(\)->id\(\)\s*\}\}/g, '7')
    .replace(/\{\{ route\('admin.chat.send'\) \}\}/g, '/admin/chat/send');
const flush = async () => { for (let i = 0; i < 12; i++) await Promise.resolve(); };

async function scenario(failSend) {
    let finishPost;
    const requests = [];
    const chips = [];
    const quickList = {
        set innerHTML(html) {
            chips.splice(0, chips.length, ...Array.from(html.matchAll(/data-key="([^"]+)"/g), match => ({
                dataset: {key: match[1]}, classList: {toggle() {}},
            })));
        },
        querySelectorAll: () => chips,
    };
    const customer = {id: 1, sender_id: 8, content: 'Xin chào, tôi cần tư vấn chọn bàn phù hợp.'};
    const context = vm.createContext({
        currentUserId: 8, chatUsers: [{id: 8}], messagesCache: {8: [customer]},
        pendingMessageCounter: 0, quickList, chatMessages: {}, chatInput: {value: 'Unsent draft'},
        document: {querySelector: () => null}, console,
        fetch: async (url, options) => {
            requests.push({url, options});
            if (url === '/chat/session') return {ok: true, json: async () => ({user_id: 7, csrf_token: 'fresh'})};
            if (url === '/admin/chat/send') return new Promise(resolve => { finishPost = resolve; });
            return {ok: true, json: async () => [customer]}; // Deliberately stale poll.
        },
    });
    vm.runInContext(source, context);
    let prevented = false;
    chips.find(chip => chip.dataset.key === 'advise').onclick({preventDefault() { prevented = true; }});
    await flush();
    assert.equal(prevented, true);
    const post = requests.find(request => request.url === '/admin/chat/send');
    assert.ok(post, 'Clicking the preset must issue a POST');
    const payload = JSON.parse(post.options.body);
    assert.equal(payload.user_id, 8);
    assert.match(payload.message, /ngân sách dự kiến/);
    assert.equal(context.chatInput.value, 'Unsent draft', 'Preset must not consume the typed draft');
    assert.match(context.chatMessages.innerHTML, /Đang gửi/);
    context.loadMessages();
    await flush();
    assert.equal(context.messagesCache[8].length, 2, 'Polling while sending cannot remove the preset');
    finishPost(failSend
        ? {ok: false, status: 500, json: async () => ({message: 'Test server failure'})}
        : {ok: true, status: 200, json: async () => ({id: 2, sender_id: 7, receiver_id: 8, content: payload.message})});
    await flush();
    context.loadMessages();
    await flush();
    assert.equal(context.messagesCache[8].length, 2, 'Neither failed nor saved presets may disappear');
    if (failSend) assert.match(context.chatMessages.innerHTML, /Chưa gửi: Test server failure/);
    else {
        assert.equal(context.messagesCache[8][1].id, 2);
        assert.doesNotMatch(context.chatMessages.innerHTML, /Đang gửi|Chưa gửi/);
    }
}

(async () => {
    await scenario(false);
    await scenario(true);
    console.log('PASS: actual admin preset click sends to selected customer; stale polling retains pending, saved and failed replies.');
})().catch(error => { console.error(error); process.exitCode = 1; });
