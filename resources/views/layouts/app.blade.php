<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Store') — Cửa hàng trực tuyến</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --bg-cream: #F3E9DC;
            --bg-paper: #FAF6F0;
            --bg-card: #FFFFFF;
            --wood-brown: #5A4536;
            --wood-dark: #3F2F24;
            --text-ink: #3A2E26;
            --text-muted: #7E7065;
            --border-warm: #E6D8C8;
            --accent-gold: #C29D62;
            --font-serif: 'Cormorant Garamond', Georgia, serif;
            --font-sans: 'Manrope', -apple-system, BlinkMacSystemFont, sans-serif;
        }
        *, *::before, *::after { box-sizing: border-box; }
        html, body {
            overflow-x: hidden;
            max-width: 100vw;
        }
        body {
            font-family: var(--font-sans);
            background-color: var(--bg-paper);
            color: var(--text-ink);
            -webkit-font-smoothing: antialiased;
            margin: 0;
            padding: 0;
        }
        h1, h2, h3, .font-serif {
            font-family: var(--font-serif);
        }
        /* Ghi đè màu liên kết và nút cho đồng bộ hệ thống */
        a {
            color: var(--wood-brown);
            transition: color 0.2s ease;
        }
        a:hover {
            color: var(--wood-dark);
        }
    </style>
</head>
<body>
    {{-- HEADER NỘI THẤT TINH HOA --}}
    <x-header />

    <main id="main-content" style="min-height: 60vh;">
        @yield('content')
    </main>

    {{-- FOOTER NỘI THẤT TINH HOA --}}
    <x-footer />

    {{-- Toast giỏ hàng dùng chung (góc phải dưới, tự tắt) --}}
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1100">
        <div id="cart-toast" class="toast align-items-center text-bg-success border-0 shadow" role="alert" aria-live="polite" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body" id="cart-toast-body">
                    <div class="fw-semibold mb-0">Thông báo</div>
                    <div id="cart-toast-msg">Thêm giỏ hàng thành công.</div>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Đóng"></button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toast thêm giỏ — không cần bấm OK
        window.showCartToast = function (message, isError) {
            var el = document.getElementById('cart-toast');
            var msg = document.getElementById('cart-toast-msg');
            if (!el || !window.bootstrap) return false;
            el.classList.remove('text-bg-success', 'text-bg-danger');
            el.classList.add(isError ? 'text-bg-danger' : 'text-bg-success');
            if (msg) msg.textContent = message || (isError ? 'Có lỗi xảy ra.' : 'Thêm giỏ hàng thành công.');
            var t = bootstrap.Toast.getOrCreateInstance(el, { delay: 2800, autohide: true });
            t.show();
            return true;
        };
        window.updateCartBadge = function (count) {
            var badge = document.getElementById('cart-count-badge');
            if (!badge) return;
            badge.textContent = count;
            badge.style.display = (count && count > 0) ? 'inline-block' : 'none';
        };

        // Mobile: toggle submenu bằng click
        document.querySelectorAll('.nav-cat-item > .nav-link').forEach(function (link) {
            link.addEventListener('click', function (e) {
                if (window.innerWidth < 992 && this.parentElement.querySelector('.cat-menu')) {
                    // Cho phép mở menu; lần click thứ 2 mới đi link nếu đã open
                    if (!this.parentElement.classList.contains('open')) {
                        e.preventDefault();
                        document.querySelectorAll('.nav-cat-item.open').forEach(el => {
                            if (el !== this.parentElement) el.classList.remove('open');
                        });
                        this.parentElement.classList.add('open');
                    }
                }
            });
        });
        document.querySelectorAll('.cat-menu > li.has-children > a').forEach(function (link) {
            link.addEventListener('click', function (e) {
                if (window.innerWidth < 992) {
                    e.preventDefault();
                    this.parentElement.classList.toggle('open');
                }
            });
        });
    </script>
    @stack('scripts')

    {{-- ===== USER LIVECHAT ===== --}}
    @auth
        @if(!auth()->user()->isAdmin())
        <style>
            /* ===== DRAGGABLE USER LIVECHAT ===== */
            #chat-toggle {
                position: fixed;
                bottom: 24px;
                right: 24px;
                z-index: 2000;
                width: 56px;
                height: 56px;
                background: linear-gradient(135deg, #5A4536, #3F2F24);
                border: 1px solid rgba(255, 255, 255, 0.25);
                border-radius: 50%;
                color: #FAF6F0;
                font-size: 1.35rem;
                box-shadow: 0 8px 24px rgba(63, 47, 36, 0.35);
                transition: transform 0.2s cubic-bezier(.22, .61, .36, 1), box-shadow 0.2s cubic-bezier(.22, .61, .36, 1);
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: grab;
                user-select: none;
                touch-action: none;
            }
            #chat-toggle:hover {
                transform: scale(1.08) translateY(-2px);
                box-shadow: 0 12px 28px rgba(63, 47, 36, 0.45);
                background: #3F2F24;
                color: #fff;
            }
            #chat-toggle.is-dragging {
                cursor: grabbing !important;
                transform: scale(1.12) !important;
                box-shadow: 0 18px 40px rgba(63, 47, 36, 0.55), 0 0 0 3px rgba(194, 157, 98, 0.5) !important;
                transition: none !important;
            }
            #chat-toggle::after {
                content: '';
                position: absolute;
                inset: -3px;
                border-radius: 50%;
                border: 2px solid rgba(194, 157, 98, 0.5);
                animation: chat-pulse 3s infinite;
                pointer-events: none;
            }
            @keyframes chat-pulse {
                0% { transform: scale(1); opacity: 0.8; }
                50% { transform: scale(1.22); opacity: 0; }
                100% { transform: scale(1.22); opacity: 0; }
            }

            #chat-popup {
                display: none;
                position: fixed;
                bottom: 24px;
                right: 24px;
                width: 360px;
                max-width: calc(100vw - 20px);
                height: 500px;
                max-height: calc(100vh - 36px);
                border-radius: 12px;
                overflow: hidden;
                flex-direction: column;
                box-shadow: 0 18px 48px rgba(63, 47, 36, 0.26), 0 4px 14px rgba(63, 47, 36, 0.12);
                border: 1px solid #E6D8C8;
                background: #FAF6F0;
                z-index: 2001;
                transition: box-shadow 0.2s ease;
            }
            #chat-popup.open { display: flex !important; }
            #chat-popup.is-dragging {
                cursor: grabbing !important;
                box-shadow: 0 24px 60px rgba(63, 47, 36, 0.42), 0 0 0 2px rgba(194, 157, 98, 0.5) !important;
                opacity: 0.97;
                transition: none !important;
            }
            #chat-popup.is-dragging #chat-messages {
                pointer-events: none;
            }

            #chat-header {
                background: linear-gradient(135deg, #3F2F24, #5A4536);
                padding: 0.75rem 1rem;
                display: flex;
                align-items: center;
                justify-content: space-between;
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
                cursor: grab;
                user-select: none;
                touch-action: none;
            }
            #chat-header:active, #chat-popup.is-dragging #chat-header {
                cursor: grabbing !important;
            }
            #chat-header .chat-title {
                display: flex;
                align-items: center;
                gap: 0.45rem;
                color: #FAF6F0;
                font-family: var(--font-serif);
                font-weight: 600;
                font-size: 1.05rem;
                letter-spacing: 0.03em;
            }
            .chat-drag-handle-hint {
                display: inline-flex;
                align-items: center;
                color: rgba(250, 246, 240, 0.55);
                font-size: 1.15rem;
                margin-right: 2px;
                cursor: grab;
            }
            #chat-header .online-dot {
                width: 8px; height: 8px;
                background: #22c55e;
                border-radius: 50%;
                display: inline-block;
                box-shadow: 0 0 6px rgba(34, 197, 94, 0.7);
            }
            .chat-header-actions {
                display: flex;
                align-items: center;
                gap: 4px;
            }
            .chat-header-btn {
                background: rgba(255,255,255,0.12);
                border: none;
                color: #FAF6F0;
                width: 26px; height: 26px;
                border-radius: 5px;
                display: inline-flex; align-items: center; justify-content: center;
                cursor: pointer;
                font-size: 0.8rem;
                transition: background 0.15s, transform 0.15s, color 0.15s;
            }
            .chat-header-btn:hover {
                background: rgba(255,255,255,0.28);
                color: #fff;
                transform: translateY(-1px);
            }
            .chat-drag-badge {
                font-size: 0.65rem;
                background: rgba(255,255,255,0.15);
                color: rgba(255,255,255,0.85);
                padding: 1px 6px;
                border-radius: 10px;
                letter-spacing: normal;
                font-family: var(--font-sans);
                font-weight: 500;
            }

            #chat-messages {
                flex: 1;
                overflow-y: auto;
                padding: 1rem;
                background: #FAF6F0;
                display: flex;
                flex-direction: column;
                gap: 0.65rem;
                min-height: 200px;
            }
            .chat-bubble {
                max-width: 84%;
                padding: 0.6rem 0.9rem;
                border-radius: 8px;
                font-size: 0.875rem;
                line-height: 1.5;
                word-break: break-word;
            }
            .chat-bubble.me {
                align-self: flex-end;
                background: #5A4536;
                color: #FAF6F0;
                border-bottom-right-radius: 2px;
                box-shadow: 0 2px 6px rgba(90, 69, 54, 0.2);
            }
            .chat-bubble.admin {
                align-self: flex-start;
                background: #FFFFFF;
                color: #3A2E26;
                border: 1px solid #E6D8C8;
                border-bottom-left-radius: 2px;
                box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
            }
            .chat-sender-label {
                font-size: 0.72rem;
                font-weight: 600;
                margin-bottom: 2px;
                color: #7E7065;
            }
            .chat-sender-label.me { text-align: right; color: #5A4536; }

            #chat-footer {
                padding: 0.75rem 0.85rem;
                border-top: 1px solid #E6D8C8;
                background: #FFFFFF;
                display: flex;
                gap: 0.5rem;
            }
            #chat-input {
                flex: 1;
                border: 1px solid #E6D8C8;
                border-radius: 6px;
                padding: 0.55rem 0.85rem;
                font-size: 0.875rem;
                outline: none;
                font-family: inherit;
                background: #FAF6F0;
                color: #3A2E26;
                transition: border-color 0.2s, background-color 0.2s;
            }
            #chat-input:focus { border-color: #5A4536; background: #fff; }
            #chat-send {
                background: #5A4536;
                border: none;
                color: #fff;
                border-radius: 6px;
                width: 42px;
                display: flex; align-items: center; justify-content: center;
                cursor: pointer;
                font-size: 1rem;
                transition: background 0.2s, transform 0.15s;
                flex-shrink: 0;
            }
            #chat-send:hover { background: #3F2F24; transform: translateY(-1px); }

            /* ===== QUICK REPLIES (CÂU MẪU) ===== */
            #chat-quick-wrap {
                background: #FFFFFF;
                border-top: 1px solid #E6D8C8;
                padding: 0.5rem 0.75rem 0.4rem;
            }
            .chat-quick-head {
                display: flex; align-items: center; justify-content: space-between;
                font-size: 0.7rem; font-weight: 600; letter-spacing: 0.04em;
                text-transform: uppercase; color: #7E7065; margin-bottom: 0.35rem;
            }
            #chat-quick-toggle {
                background: none; border: none; color: #7E7065; cursor: pointer;
                font-size: 0.75rem; padding: 0 2px; transition: transform 0.2s, color 0.2s;
            }
            #chat-quick-toggle:hover { color: #5A4536; }
            #chat-quick-wrap.collapsed #chat-quick-toggle { transform: rotate(180deg); }
            #chat-quick-wrap.collapsed #chat-quick-list { display: none; }
            #chat-quick-wrap.collapsed .chat-quick-head { margin-bottom: 0; }
            #chat-quick-list {
                display: flex; flex-wrap: wrap; gap: 0.4rem;
                max-height: 92px; overflow-y: auto;
            }
            .chat-quick-chip {
                display: inline-flex; align-items: center; gap: 0.3rem;
                background: #FAF6F0; color: #5A4536;
                border: 1px solid #E6D8C8; border-radius: 999px;
                padding: 0.3rem 0.7rem; font-size: 0.78rem; font-weight: 500;
                font-family: inherit; cursor: pointer; white-space: nowrap;
                transition: background 0.18s, color 0.18s, border-color 0.18s, transform 0.15s;
            }
            .chat-quick-chip i { font-size: 0.8rem; color: #C29D62; transition: color 0.18s; }
            .chat-quick-chip:hover {
                background: #5A4536; color: #FAF6F0; border-color: #5A4536;
                transform: translateY(-1px);
            }
            .chat-quick-chip:hover i { color: #FAF6F0; }
            .chat-quick-chip:disabled { opacity: 0.55; cursor: not-allowed; transform: none; }
            @media (max-width: 575.98px) {
                #chat-quick-list { flex-wrap: nowrap; overflow-x: auto; overflow-y: hidden; max-height: none; padding-bottom: 2px; }
            }
        </style>

        <div id="chat-box">
            <!-- Nút bật chat (có thể kéo di chuyển) -->
            <button id="chat-toggle" title="Chat hỗ trợ" aria-label="Mở chat hỗ trợ">
                <i class="bi bi-chat-dots"></i>
            </button>

            <!-- Hộp thoại chat popup (có thể kéo di chuyển thanh tiêu đề) -->
            <div id="chat-popup" role="dialog" aria-labelledby="chat-title-text">
                <div id="chat-header">
                    <div class="chat-title">
                        <i class="bi bi-grip-vertical chat-drag-handle-hint"></i>
                        <span class="online-dot"></span>
                        <span id="chat-title-text" style="white-space:nowrap;">Hỗ trợ khách hàng</span>
                    </div>
                    <div class="chat-header-actions">
                        <button id="chat-minimize-btn" class="chat-header-btn" title="Thu nhỏ" aria-label="Thu nhỏ">
                            <i class="bi bi-dash-lg"></i>
                        </button>
                        <button id="chat-close-btn" class="chat-header-btn" title="Đóng chat" aria-label="Đóng chat">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                </div>
                <div id="chat-messages">
                    <div style="text-align:center;margin:auto;color:#94a3b8;font-size:0.82rem;">
                        <i class="bi bi-chat-square-text d-block mb-1" style="font-size:1.5rem;opacity:0.4;"></i>
                        Bắt đầu trò chuyện
                    </div>
                </div>
                <div id="chat-quick-wrap">
                    <div class="chat-quick-head">
                        <span><i class="bi bi-lightning-charge-fill" style="color:#C29D62;"></i> Câu hỏi nhanh</span>
                        <button type="button" id="chat-quick-toggle" title="Ẩn/hiện câu hỏi nhanh" aria-label="Ẩn/hiện câu hỏi nhanh">
                            <i class="bi bi-chevron-down"></i>
                        </button>
                    </div>
                    <div id="chat-quick-list">
                        <button type="button" class="chat-quick-chip" data-msg="Xin chào, tôi cần tư vấn chọn bàn phù hợp."><i class="bi bi-hand-thumbs-up"></i>Tư vấn chọn bàn</button>
                        <button type="button" class="chat-quick-chip" data-msg="Sản phẩm này hiện còn hàng không ạ?"><i class="bi bi-box-seam"></i>Còn hàng không?</button>
                        <button type="button" class="chat-quick-chip" data-msg="Phí vận chuyển và thời gian giao hàng là bao lâu?"><i class="bi bi-truck"></i>Phí &amp; thời gian giao</button>
                        <button type="button" class="chat-quick-chip" data-msg="Hiện shop có chương trình khuyến mãi hoặc mã giảm giá nào không?"><i class="bi bi-tag"></i>Khuyến mãi</button>
                        <button type="button" class="chat-quick-chip" data-msg="Tôi muốn kiểm tra tình trạng đơn hàng của mình."><i class="bi bi-receipt"></i>Kiểm tra đơn hàng</button>
                        <button type="button" class="chat-quick-chip" data-msg="Chính sách bảo hành và đổi trả của shop như thế nào?"><i class="bi bi-shield-check"></i>Bảo hành &amp; đổi trả</button>
                        <button type="button" class="chat-quick-chip" data-msg="Shop có hỗ trợ lắp đặt tại nhà không?"><i class="bi bi-tools"></i>Lắp đặt tại nhà</button>
                        <button type="button" class="chat-quick-chip" data-msg="Shop hỗ trợ những phương thức thanh toán nào?"><i class="bi bi-credit-card"></i>Thanh toán</button>
                    </div>
                </div>
                <div id="chat-footer">
                    <input type="text" id="chat-input" placeholder="Nhập tin nhắn..." autocomplete="off" maxlength="2000">
                    <button id="chat-send" title="Gửi">
                        <i class="bi bi-send"></i>
                    </button>
                </div>
            </div>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggleBtn   = document.getElementById('chat-toggle');
            const chatPopup   = document.getElementById('chat-popup');
            const chatHeader  = document.getElementById('chat-header');
            const closeBtn    = document.getElementById('chat-close-btn');
            const minimizeBtn = document.getElementById('chat-minimize-btn');
            const resetBtn    = document.getElementById('chat-reset-btn');
            const sendBtn     = document.getElementById('chat-send');
            const input       = document.getElementById('chat-input');
            const chatBox     = document.getElementById('chat-messages');
            if (!toggleBtn || !chatPopup) return;

            const myId = {{ (int) auth()->id() }};
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

            // ===== DRAG & DROP ENGINE =====
            const STORAGE_POPUP_KEY  = 'table_shop_chat_popup_pos';
            const STORAGE_TOGGLE_KEY = 'table_shop_chat_toggle_pos';

            function clampPosition(el, targetLeft, targetTop) {
                const margin = 10;
                const rect = el.getBoundingClientRect();
                const w = rect.width || el.offsetWidth || 360;
                const h = rect.height || el.offsetHeight || 500;
                const maxLeft = Math.max(margin, window.innerWidth - w - margin);
                const maxTop  = Math.max(margin, window.innerHeight - h - margin);

                const clampedX = Math.round(Math.max(margin, Math.min(targetLeft, maxLeft)));
                const clampedY = Math.round(Math.max(margin, Math.min(targetTop, maxTop)));

                el.style.left = clampedX + 'px';
                el.style.top = clampedY + 'px';
                el.style.right = 'auto';
                el.style.bottom = 'auto';

                return { x: clampedX, y: clampedY };
            }

            function setupDraggable(targetEl, handleEl, storageKey, onJustClicked) {
                let startX = 0, startY = 0;
                let origLeft = 0, origTop = 0;
                let isDragging = false;
                let hasMoved = false;
                const threshold = 6;

                // Khôi phục vị trí đã lưu
                try {
                    const saved = JSON.parse(localStorage.getItem(storageKey));
                    if (saved && typeof saved.x === 'number' && typeof saved.y === 'number') {
                        clampPosition(targetEl, saved.x, saved.y);
                    }
                } catch(e) {}

                function onPointerDown(e) {
                    // Bỏ qua nếu nhấn chuột phải hoặc nhấn vào nút chức năng trong header
                    if (e.type === 'mousedown' && e.button !== 0) return;
                    if (e.target.closest('.chat-header-btn')) return;

                    const clientX = e.touches ? e.touches[0].clientX : e.clientX;
                    const clientY = e.touches ? e.touches[0].clientY : e.clientY;

                    const rect = targetEl.getBoundingClientRect();
                    startX = clientX;
                    startY = clientY;
                    origLeft = rect.left;
                    origTop = rect.top;
                    hasMoved = false;
                    isDragging = false;

                    document.addEventListener('mousemove', onPointerMove, { passive: false });
                    document.addEventListener('mouseup', onPointerUp);
                    document.addEventListener('touchmove', onPointerMove, { passive: false });
                    document.addEventListener('touchend', onPointerUp);
                }

                function onPointerMove(e) {
                    const clientX = e.touches ? e.touches[0].clientX : e.clientX;
                    const clientY = e.touches ? e.touches[0].clientY : e.clientY;
                    const dx = clientX - startX;
                    const dy = clientY - startY;

                    if (!hasMoved && Math.hypot(dx, dy) > threshold) {
                        hasMoved = true;
                        isDragging = true;
                        targetEl.classList.add('is-dragging');
                        document.body.style.userSelect = 'none';
                    }

                    if (isDragging) {
                        if (e.cancelable) e.preventDefault();
                        clampPosition(targetEl, origLeft + dx, origTop + dy);
                    }
                }

                function onPointerUp() {
                    document.removeEventListener('mousemove', onPointerMove);
                    document.removeEventListener('mouseup', onPointerUp);
                    document.removeEventListener('touchmove', onPointerMove);
                    document.removeEventListener('touchend', onPointerUp);
                    document.body.style.userSelect = '';

                    if (isDragging) {
                        targetEl.classList.remove('is-dragging');
                        isDragging = false;
                        const rect = targetEl.getBoundingClientRect();
                        const pos = clampPosition(targetEl, rect.left, rect.top);
                        try {
                            localStorage.setItem(storageKey, JSON.stringify(pos));
                        } catch(e) {}
                    } else if (!hasMoved) {
                        if (typeof onJustClicked === 'function') {
                            onJustClicked();
                        }
                    }
                }

                handleEl.addEventListener('mousedown', onPointerDown);
                handleEl.addEventListener('touchstart', onPointerDown, { passive: true });
            }

            // Kéo nút toggle (phân biệt kéo và click)
            setupDraggable(toggleBtn, toggleBtn, STORAGE_TOGGLE_KEY, function() {
                openChat();
            });

            // Kéo hộp chat qua thanh header
            setupDraggable(chatPopup, chatHeader, STORAGE_POPUP_KEY, null);

            // Tự động căn chỉnh khi resize màn hình
            window.addEventListener('resize', function () {
                [toggleBtn, chatPopup].forEach(el => {
                    if (el.style.left && el.style.left !== 'auto') {
                        const rect = el.getBoundingClientRect();
                        clampPosition(el, rect.left, rect.top);
                    }
                });
            });

            // Mở và đóng chat
            function openChat() {
                chatPopup.classList.add('open');
                toggleBtn.style.display = 'none';

                // Nếu popup chưa có vị trí lưu, đặt mặc định hoặc gần nút toggle
                const saved = localStorage.getItem(STORAGE_POPUP_KEY);
                if (!saved) {
                    const toggleRect = toggleBtn.getBoundingClientRect();
                    const popupW = 360;
                    const popupH = 500;
                    // Đặt cạnh nút toggle nhưng đảm bảo lọt trong màn hình
                    let initialX = window.innerWidth - popupW - 24;
                    let initialY = window.innerHeight - popupH - 24;
                    if (toggleRect.left < window.innerWidth / 2) {
                        initialX = Math.max(10, toggleRect.left);
                    }
                    clampPosition(chatPopup, initialX, initialY);
                } else {
                    const rect = chatPopup.getBoundingClientRect();
                    clampPosition(chatPopup, rect.left, rect.top);
                }

                loadMessages();
            }

            function closeChat() {
                chatPopup.classList.remove('open');
                toggleBtn.style.display = 'flex';
                // Đảm bảo nút toggle nằm trong màn hình
                const rect = toggleBtn.getBoundingClientRect();
                clampPosition(toggleBtn, rect.left, rect.top);
            }

            closeBtn.onclick = closeChat;
            minimizeBtn.onclick = closeChat;

            // Đặt lại vị trí mặc định (góc dưới phải)
            function resetChatPosition() {
                try {
                    localStorage.removeItem(STORAGE_POPUP_KEY);
                } catch(e) {}
                chatPopup.style.left = 'auto';
                chatPopup.style.top = 'auto';
                chatPopup.style.right = '24px';
                chatPopup.style.bottom = '24px';
            }
            if (resetBtn) resetBtn.onclick = resetChatPosition;
            // Nhấp đúp vào header để reset vị trí
            chatHeader.addEventListener('dblclick', function(e) {
                if (!e.target.closest('.chat-header-btn')) {
                    resetChatPosition();
                }
            });

            // ===== LOGIC TIN NHẮN =====
            function escapeHtml(s) {
                return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
            }

            function loadMessages() {
                fetch('{{ route('user.chat.messages') }}', { headers: { Accept: 'application/json' } })
                    .then(r => r.json())
                    .then(messages => {
                        if (!messages || messages.length === 0) {
                            chatBox.innerHTML = `<div style="text-align:center;margin:auto;color:#94a3b8;font-size:0.82rem;"><i class="bi bi-chat-square-text d-block mb-1" style="font-size:1.5rem;opacity:0.4;"></i>Bắt đầu trò chuyện</div>`;
                            return;
                        }
                        let html = '';
                        messages.forEach(msg => {
                            const isMe = msg.sender_id == myId;
                            const label = isMe ? 'Bạn' : 'Admin';
                            html += `<div>
                                <div class="chat-sender-label ${isMe ? 'me' : ''}">` + label + `</div>
                                <div class="chat-bubble ${isMe ? 'me' : 'admin'}">${escapeHtml(msg.content)}</div>
                            </div>`;
                        });
                        chatBox.innerHTML = html;
                        chatBox.scrollTop = chatBox.scrollHeight;
                    })
                    .catch(err => console.error('Lỗi tải tin nhắn:', err));
            }

            const quickWrap  = document.getElementById('chat-quick-wrap');
            const quickChips = document.querySelectorAll('.chat-quick-chip');
            const QUICK_COLLAPSE_KEY = 'table_shop_chat_quick_collapsed';

            try {
                if (localStorage.getItem(QUICK_COLLAPSE_KEY) === '1') quickWrap.classList.add('collapsed');
            } catch (e) {}

            document.getElementById('chat-quick-toggle').onclick = () => {
                quickWrap.classList.toggle('collapsed');
                try { localStorage.setItem(QUICK_COLLAPSE_KEY, quickWrap.classList.contains('collapsed') ? '1' : '0'); } catch (e) {}
            };

            // Bấm câu mẫu -> gửi ngay
            quickChips.forEach(chip => {
                chip.addEventListener('click', () => sendMessage(chip.dataset.msg));
            });

            function setSending(state) {
                input.disabled = state;
                sendBtn.disabled = state;
                quickChips.forEach(c => c.disabled = state);
            }

            function sendMessage(preset) {
                const fromPreset = typeof preset === 'string';
                const message = (fromPreset ? preset : input.value).trim();
                if (!message) return;
                setSending(true);
                fetch('{{ route('user.chat.send') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ message }),
                })
                    .then(r => r.json())
                    .then(() => {
                        if (!fromPreset) input.value = '';
                        setSending(false);
                        input.focus();
                        loadMessages();
                    })
                    .catch(err => {
                        console.error(err);
                        setSending(false);
                    });
            }

            sendBtn.onclick = () => sendMessage();
            input.addEventListener('keypress', e => { if (e.key === 'Enter') sendMessage(); });
            setInterval(() => {
                if (chatPopup.classList.contains('open')) loadMessages();
            }, 3000);
        });
        </script>
        @endif
    @endauth
</body>
</html>
