{{-- resources/views/components/hero.blade.php --}}
{{-- Dùng: <x-hero /> --}}
{{-- Ảnh nền: public/images/hero-ban-go.jpg (nên là bản KHÔNG có chữ) --}}

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500&family=Manrope:wght@400;500;600&display=swap" rel="stylesheet">

<style>
  .hero {
    --brown: #5a4536;
    --brown-dark: #3f2f24;
    --ink: #3a2e26;
    --ease: cubic-bezier(.22, .61, .36, 1);
    --hero-h: calc(100vh - 72px);       /* trừ chiều cao navbar */
    position: relative;
    min-height: var(--hero-h);
    margin-top: -1rem;                  /* bù padding-top của <main class="py-3"> */
    overflow: hidden;
    background: #efe3d3;
    font-family: 'Manrope', sans-serif;
    color: var(--ink);
    line-height: normal;
  }

  /* ---------- Nền ---------- */
  .hero__bg {
    position: absolute;
    inset: -30px;                       /* chừa chỗ cho parallax */
    background: url('{{ asset('images/hero-ban-go.jpg') }}') center / cover no-repeat;
    will-change: transform;
    animation: kenburns 2.4s var(--ease) both;
  }
  .hero__bg-wrap {
    position: absolute; inset: 0;
    will-change: transform;             /* JS dịch chuyển theo chuột + cuộn */
  }
  @keyframes kenburns {
    from { transform: scale(1.08); }
    to   { transform: scale(1); }
  }

  /* Vệt nắng: lớp sáng mờ dịch chậm trên vùng tường bên trái */
  .hero__sun {
    position: absolute;
    top: 8%; left: 30%;
    width: 38%; height: 55%;
    background: radial-gradient(ellipse at center, rgba(255, 244, 220, .55), rgba(255, 244, 220, 0) 70%);
    mix-blend-mode: soft-light;
    filter: blur(20px);
    pointer-events: none;
    will-change: transform, opacity;
    animation: sunshift 9s ease-in-out infinite alternate;
  }
  @keyframes sunshift {
    from { opacity: .35; transform: translate3d(-2%, 0, 0); }
    to   { opacity: .8;  transform: translate3d(3%, 2%, 0); }
  }

  /* Lớp sáng giúp chữ dễ đọc */
  .hero__glow {
    position: absolute; inset: 0;
    background: linear-gradient(100deg, rgba(255, 248, 236, .75) 0%, rgba(255, 248, 236, .35) 35%, rgba(255, 248, 236, 0) 55%);
    pointer-events: none;
  }

  /* ---------- Nội dung ---------- */
  .hero__inner {
    position: relative;
    z-index: 2;
    min-height: var(--hero-h);
    padding: 0 clamp(24px, 7vw, 134px);
    display: flex;
    flex-direction: column;
    justify-content: center;
  }
  .hero__brand {
    position: absolute;
    top: clamp(36px, 9vh, 90px);
    left: clamp(24px, 7vw, 134px);
    font-size: 14px;
    letter-spacing: .18em;
    line-height: 1.6;
  }
  .hero__brand small {
    display: block;
    font-size: 10px;
    letter-spacing: .2em;
    opacity: .6;
  }
  .hero__kicker {
    font-size: 12px;
    letter-spacing: .2em;
    opacity: .65;
    margin-bottom: 28px;
  }
  .hero__title {
    font-family: 'Cormorant Garamond', serif;
    font-weight: 400;
    font-size: clamp(56px, 8vw, 118px);
    line-height: 1.05;
    margin: 0 0 32px;
    color: var(--ink);
  }
  .hero__title span { display: block; }
  .hero__title span:last-child { color: #7a5a44; }
  .hero__desc {
    max-width: 440px;
    font-size: 17px;
    line-height: 1.85;
    margin: 0 0 40px;
  }

  .hero__btn {
    display: inline-flex;
    align-items: center;
    gap: 56px;
    padding: 20px 27px;
    background: var(--brown);
    color: #fff;
    font-size: 13px;
    font-weight: 600;
    letter-spacing: .1em;
    text-decoration: none;
    transition: background .4s var(--ease), transform .4s var(--ease), box-shadow .4s var(--ease);
    align-self: flex-start;
  }
  .hero__btn svg { transition: transform .4s var(--ease); }
  .hero__btn:hover,
  .hero__btn:focus-visible {
    background: var(--brown-dark);
    color: #fff;
    transform: translateY(-2px);
    box-shadow: 0 10px 24px rgba(63, 47, 36, .25);
  }
  .hero__btn:hover svg,
  .hero__btn:focus-visible svg { transform: translateX(6px); }
  .hero__btn:focus-visible { outline: 2px solid var(--brown); outline-offset: 4px; }

  /* ---------- Hiệu ứng vào trang: chữ lần lượt hiện lên ---------- */
  .hero .reveal {
    opacity: 0;
    transform: translate3d(0, 28px, 0);
    animation: rise 1.1s var(--ease) forwards;
    animation-delay: calc(var(--i) * .15s + .5s);
  }
  @keyframes rise {
    to { opacity: 1; transform: translate3d(0, 0, 0); }
  }

  /* ---------- Responsive ---------- */
  @media (max-width: 900px) {
    .hero__glow {
      background: linear-gradient(180deg, rgba(255, 248, 236, .85) 0%, rgba(255, 248, 236, .5) 55%, rgba(255, 248, 236, 0) 100%);
    }
    .hero__inner { justify-content: flex-start; padding-top: 24vh; }
    .hero__bg { background-position: 70% center; }
  }

  /* ---------- Giảm chuyển động ---------- */
  @media (prefers-reduced-motion: reduce) {
    .hero__bg, .hero__sun { animation: none; }
    .hero .reveal { animation: none; opacity: 1; transform: none; }
    .hero__btn, .hero__btn svg { transition: none; }
  }
</style>

<section class="hero" id="hero">
  <div class="hero__bg-wrap" data-bg>
    <div class="hero__bg"></div>
    <div class="hero__sun"></div>
  </div>
  <div class="hero__glow"></div>

  <div class="hero__inner">
    <div class="hero__brand reveal" style="--i:0">
      NỘI THẤT TINH HOA
      <small>GỖ ĐẸP CHO NHÀ</small>
    </div>

    <p class="hero__kicker reveal" style="--i:1">BÀN GỖ CHO MỖI NGÀY</p>

    <h1 class="hero__title">
      <span class="reveal" style="--i:2">Bàn đẹp.</span>
      <span class="reveal" style="--i:3">Nhà ấm.</span>
    </h1>

    <p class="hero__desc reveal" style="--i:4">
      Mặt bàn oval, sắc gỗ nâu ấm, đường nét mềm mại.
      Một góc quây quần cho bữa cơm gia đình,
      tách trà buổi chiều và những câu chuyện không vội.
    </p>

    <a href="#product-section" class="hero__btn reveal" style="--i:5">
      XEM MẪU BÀN
      <svg width="22" height="10" viewBox="0 0 22 10" fill="none" stroke="currentColor" stroke-width="1.2" aria-hidden="true">
        <path d="M0 5h20M16 1l4 4-4 4"/>
      </svg>
    </a>
  </div>
</section>

<script>
(function () {
  const hero = document.getElementById('hero');
  const bg = hero.querySelector('[data-bg]');
  const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const canHover = window.matchMedia('(hover: hover) and (min-width: 901px)').matches;
  if (reduce) return;

  // ===== Chỉnh độ mạnh hiệu ứng tại đây =====
  const MOUSE_STRENGTH = 14;   // px, nền lệch tối đa theo chuột
  const SCROLL_SPEED   = 0.25; // 0 = không parallax, 0.5 = nền trượt chậm một nửa
  const SMOOTH         = 0.06; // 0.02 (rất trễ, mượt) → 0.2 (bám sát)

  let tx = 0, ty = 0;        // mục tiêu theo chuột
  let cx = 0, cy = 0;        // giá trị hiện tại (đã làm mượt)
  let scrollY = 0;
  let ticking = false;

  if (canHover) {
    hero.addEventListener('mousemove', (e) => {
      const r = hero.getBoundingClientRect();
      // Nền di chuyển NGƯỢC hướng chuột
      tx = -((e.clientX - r.left) / r.width - 0.5) * 2 * MOUSE_STRENGTH;
      ty = -((e.clientY - r.top) / r.height - 0.5) * 2 * MOUSE_STRENGTH;
      start();
    });
    hero.addEventListener('mouseleave', () => { tx = 0; ty = 0; start(); });
  }

  window.addEventListener('scroll', () => {
    scrollY = Math.min(window.scrollY, hero.offsetHeight);
    start();
  }, { passive: true });

  function start() {
    if (!ticking) { ticking = true; requestAnimationFrame(frame); }
  }

  function frame() {
    cx += (tx - cx) * SMOOTH;
    cy += (ty - cy) * SMOOTH;
    const sy = scrollY * SCROLL_SPEED;
    bg.style.transform = `translate3d(${cx.toFixed(2)}px, ${(cy + sy).toFixed(2)}px, 0)`;

    const settled = Math.abs(tx - cx) < 0.05 && Math.abs(ty - cy) < 0.05;
    if (settled) { ticking = false; } else { requestAnimationFrame(frame); }
  }
})();
</script>
