// Keep acknowledged and pending messages when an older polling response arrives.
function mergeChatMessages(current, incoming) {
    const messages = new Map(current.map(message => [String(message.id), message]));
    incoming.forEach(message => messages.set(String(message.id), message));
    return [...messages.values()].sort((a, b) => {
        const aPending = String(a.id).startsWith('temp_'), bPending = String(b.id).startsWith('temp_');
        return aPending !== bPending ? Number(aPending) - Number(bPending) : aPending ? 0 : Number(a.id) - Number(b.id);
    });
}
async function sendStaffChat(url, payload, expectedUserId) {
    const sessionResponse = await fetch(@json(route('chat.session')), {cache:'no-store', headers:{Accept:'application/json'}});
    if (!sessionResponse.ok || sessionResponse.redirected) throw new Error('Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại rồi gửi.');
    const session = await sessionResponse.json();
    if (String(session.user_id) !== String(expectedUserId)) throw new Error('Tab này đã đổi tài khoản đăng nhập. Hãy tải lại trang; nếu thử admin và khách, dùng hai trình duyệt hoặc cửa sổ ẩn danh riêng.');
    const response = await fetch(url, {method:'POST', headers:{Accept:'application/json','Content-Type':'application/json','X-CSRF-TOKEN':session.csrf_token}, body:JSON.stringify({...payload, expected_user_id:expectedUserId})});
    if (response.status === 419) throw new Error('Phiên đăng nhập vừa thay đổi. Hãy tải lại trang rồi gửi lại.');
    if (response.status === 401 || response.status === 403 || response.redirected) throw new Error('Tài khoản hiện tại không có quyền gửi trong tab này. Vui lòng tải lại trang và kiểm tra đăng nhập.');
    const data = await response.json().catch(()=>null);
    if (!response.ok || !data?.id) throw new Error(data?.message || data?.error || `Không gửi được tin nhắn (HTTP ${response.status}). Nội dung chưa được gửi, vui lòng thử lại.`);
    return data;
}
