(() => {
    const endpoint = @json(route('verification.status'));
    const expectedUserId = @json(auth()->id());
    const status = document.getElementById('verification-status');
    const check = document.getElementById('verification-check');
    let timer, busy = false, stopped = false;
    async function poll() {
        if (busy || stopped) return;
        clearTimeout(timer);
        if (document.hidden) { timer = setTimeout(poll, 3000); return; }
        busy = true;
        check.disabled = true;
        const controller = new AbortController();
        const timeout = setTimeout(() => controller.abort(), 10000);
        try {
            const response = await fetch(endpoint, {cache:'no-store', credentials:'same-origin', headers:{Accept:'application/json'}, signal:controller.signal});
            if (response.status === 401 || response.redirected) {
                stopped = true;
                status.textContent = 'Phiên đăng nhập đã hết hạn. Email vẫn được xác thực khi bạn bấm link; hãy đăng nhập lại để tiếp tục.';
                document.getElementById('verification-login').hidden = false;
                return;
            }
            if (!response.ok) throw new Error('status unavailable');
            const data = await response.json();
            if (String(data.user_id) !== String(expectedUserId)) {
                stopped = true;
                status.textContent = 'Tài khoản trong trình duyệt đã thay đổi. Hãy tải lại trang để tiếp tục.';
                return;
            }
            if (data.verified && data.redirect) {
                stopped = true;
                status.textContent = 'Đã xác thực! Đang vào website…';
                window.location.replace(data.redirect);
                return;
            }
            status.textContent = 'Đang chờ bạn xác thực email. Bạn có thể bấm link trên điện thoại.';
        } catch (error) {
            status.textContent = 'Chưa kết nối được để kiểm tra. Trang sẽ tự thử lại…';
        } finally {
            clearTimeout(timeout);
            busy = false;
            check.disabled = stopped;
            if (!stopped) timer = setTimeout(poll, 3000);
        }
    }
    check.addEventListener('click', poll);
    document.addEventListener('visibilitychange', () => { if (!document.hidden) poll(); });
    window.addEventListener('focus', poll);
    poll();
})();
