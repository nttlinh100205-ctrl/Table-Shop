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
            #chat-box { position: fixed; bottom: 24px; right: 24px; z-index: 2000; }
            #chat-toggle {
                width: 54px; height: 54px;
                background: linear-gradient(135deg, #5A4536, #3F2F24);
                border: 1px solid rgba(255, 255, 255, 0.2);
                border-radius: 50%;
                color: #FAF6F0;
                font-size: 1.25rem;
                box-shadow: 0 8px 24px rgba(63, 47, 36, 0.35);
                transition: transform 0.2s cubic-bezier(.22, .61, .36, 1), box-shadow 0.2s cubic-bezier(.22, .61, .36, 1);
                display: flex; align-items: center; justify-content: center;
                cursor: pointer;
            }
            #chat-toggle:hover {
                transform: scale(1.08) translateY(-2px);
                box-shadow: 0 12px 28px rgba(63, 47, 36, 0.45);
                background: #3F2F24;
                color: #fff;
            }
            #chat-popup {
                display: none;
                position: absolute;
                bottom: 68px;
                right: 0;
                width: 350px;
                max-height: 480px;
                border-radius: 8px;
                overflow: hidden;
                flex-direction: column;
                box-shadow: 0 16px 48px rgba(63, 47, 36, 0.22), 0 4px 12px rgba(63, 47, 36, 0.12);
                border: 1px solid #E6D8C8;
                background: #FAF6F0;
            }
            #chat-popup.open { display: flex !important; }
            #chat-header {
                background: linear-gradient(135deg, #3F2F24, #5A4536);
                padding: 0.85rem 1.1rem;
                display: flex;
                align-items: center;
                justify-content: space-between;
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            }
            #chat-header .chat-title {
                display: flex;
                align-items: center;
                gap: 0.55rem;
                color: #FAF6F0;
                font-family: var(--font-serif);
                font-weight: 600;
                font-size: 1.05rem;
                letter-spacing: 0.04em;
            }
            #chat-header .online-dot {
                width: 8px; height: 8px;
                background: #22c55e;
                border-radius: 50%;
                display: inline-block;
                box-shadow: 0 0 6px rgba(34, 197, 94, 0.6);
            }
            #chat-close-btn {
                background: rgba(255,255,255,0.15);
                border: none;
                color: #FAF6F0;
                width: 28px; height: 28px;
                border-radius: 4px;
                display: flex; align-items: center; justify-content: center;
                cursor: pointer;
                font-size: 0.85rem;
                transition: background 0.2s;
            }
            #chat-close-btn:hover { background: rgba(255,255,255,0.3); }
            #chat-messages {
                flex: 1;
                overflow-y: auto;
                padding: 1rem;
                background: #FAF6F0;
                display: flex;
                flex-direction: column;
                gap: 0.6rem;
                min-height: 220px;
                max-height: 300px;
            }
            .chat-bubble {
                max-width: 82%;
                padding: 0.55rem 0.85rem;
                border-radius: 6px;
                font-size: 0.875rem;
                line-height: 1.5;
                word-break: break-word;
            }
            .chat-bubble.me {
                align-self: flex-end;
                background: #5A4536;
                color: #FAF6F0;
                border-bottom-right-radius: 2px;
            }
            .chat-bubble.admin {
                align-self: flex-start;
                background: #FFFFFF;
                color: #3A2E26;
                border: 1px solid #E6D8C8;
                border-bottom-left-radius: 2px;
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
                border-radius: 4px;
                padding: 0.5rem 0.8rem;
                font-size: 0.875rem;
                outline: none;
                font-family: inherit;
                background: #FAF6F0;
                color: #3A2E26;
                transition: border-color 0.2s;
            }
            #chat-input:focus { border-color: #5A4536; background: #fff; }
            #chat-send {
                background: #5A4536;
                border: none;
                color: #fff;
                border-radius: 4px;
                width: 40px;
                display: flex; align-items: center; justify-content: center;
                cursor: pointer;
                font-size: 0.95rem;
                transition: background 0.2s, transform 0.15s;
                flex-shrink: 0;
            }
            #chat-send:hover { background: #3F2F24; transform: translateY(-1px); }
        </style>
        <div id="chat-box">
            <button id="chat-toggle" title="Chat hỗ trợ">
                <i class="bi bi-chat-dots"></i>
            </button>
            <div id="chat-popup">
                <div id="chat-header">
                    <div class="chat-title">
                        <span class="online-dot"></span>
                        Hỗ trợ khách hàng
                    </div>
                    <button id="chat-close-btn" aria-label="Đóng chat">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <div id="chat-messages">
                    <div style="text-align:center;margin:auto;color:#94a3b8;font-size:0.82rem;">
                        <i class="bi bi-chat-square-text d-block mb-1" style="font-size:1.5rem;opacity:0.4;"></i>
                        Bắt đầu trò chuyện với Admin
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
            const toggleBtn = document.getElementById('chat-toggle');
            const chatPopup = document.getElementById('chat-popup');
            const closeBtn  = document.getElementById('chat-close-btn');
            const sendBtn   = document.getElementById('chat-send');
            const input     = document.getElementById('chat-input');
            const chatBox   = document.getElementById('chat-messages');
            if (!toggleBtn) return;

            const myId = {{ (int) auth()->id() }};
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

            toggleBtn.onclick = () => {
                chatPopup.classList.add('open');
                toggleBtn.style.display = 'none';
                loadMessages();
            };
            closeBtn.onclick = () => {
                chatPopup.classList.remove('open');
                toggleBtn.style.display = 'flex';
            };

            function escapeHtml(s) {
                return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
            }

            function loadMessages() {
                fetch('{{ route('user.chat.messages') }}', { headers: { Accept: 'application/json' } })
                    .then(r => r.json())
                    .then(messages => {
                        if (!messages || messages.length === 0) {
                            chatBox.innerHTML = `<div style="text-align:center;margin:auto;color:#94a3b8;font-size:0.82rem;"><i class="bi bi-chat-square-text d-block mb-1" style="font-size:1.5rem;opacity:0.4;"></i>Bắt đầu trò chuyện với Admin</div>`;
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

            function sendMessage() {
                const message = input.value.trim();
                if (!message) return;
                input.disabled = true;
                sendBtn.disabled = true;
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
                        input.value = '';
                        input.disabled = false;
                        sendBtn.disabled = false;
                        input.focus();
                        loadMessages();
                    })
                    .catch(err => {
                        console.error(err);
                        input.disabled = false;
                        sendBtn.disabled = false;
                    });
            }

            sendBtn.onclick = sendMessage;
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
