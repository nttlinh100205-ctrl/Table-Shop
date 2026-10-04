{{-- resources/views/components/commitments.blade.php --}}
<section class="nth-commitments" aria-label="Cam kết thương hiệu">
    <div class="nth-container">
        <div class="nth-commitments__grid">
            {{-- Ý 1 --}}
            <div class="nth-commitment-item">
                <div class="nth-commitment-item__icon">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2">
                        <path d="M12 22V12M12 12C9.5 12 7.5 9.5 7.5 7C7.5 4 12 2 12 2C12 2 16.5 4 16.5 7C16.5 9.5 14.5 12 12 12Z"/>
                        <path d="M12 17C10 17 9 15.5 9 15.5"/>
                        <path d="M12 14.5C13.5 14.5 14.5 13.5 14.5 13.5"/>
                    </svg>
                </div>
                <div class="nth-commitment-item__body">
                    <h3 class="nth-commitment-item__title">Gỗ Tự Nhiên Tuyển Chọn</h3>
                    <p class="nth-commitment-item__desc">
                        Óc chó &amp; sồi Bắc Mỹ thớ vân tuyển lọc, tẩm sấy chuẩn quốc tế, giữ trọn sắc mộc nguyên bản.
                    </p>
                </div>
            </div>

            {{-- Ý 2 --}}
            <div class="nth-commitment-item">
                <div class="nth-commitment-item__icon">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2">
                        <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
                    </svg>
                </div>
                <div class="nth-commitment-item__body">
                    <h3 class="nth-commitment-item__title">Chế Tác Thủ Công Tinh Xảo</h3>
                    <p class="nth-commitment-item__desc">
                        Từng gờ cong, mối mộng và đường lượn được vuốt ráp tỉ mỉ bởi nghệ nhân mộc truyền thống.
                    </p>
                </div>
            </div>

            {{-- Ý 3 --}}
            <div class="nth-commitment-item">
                <div class="nth-commitment-item__icon">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2">
                        <path d="M21 3L3 21M21 3H14M21 3V10M3 21H10M3 21V14"/>
                        <path d="M16 8L8 16"/>
                    </svg>
                </div>
                <div class="nth-commitment-item__body">
                    <h3 class="nth-commitment-item__title">May Đo Theo Không Gian</h3>
                    <p class="nth-commitment-item__desc">
                        Tùy biến kích thước, kiểu dáng và vân gỗ chính xác theo bản vẽ phối cảnh của từng ngôi nhà.
                    </p>
                </div>
            </div>

            {{-- Ý 4 --}}
            <div class="nth-commitment-item">
                <div class="nth-commitment-item__icon">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        <path d="M9 12l2 2 4-4"/>
                    </svg>
                </div>
                <div class="nth-commitment-item__body">
                    <h3 class="nth-commitment-item__title">Đồng Hành Bền Vững</h3>
                    <p class="nth-commitment-item__desc">
                        Bảo hành kết cấu 5 năm, miễn phí dưỡng dầu làm mới mặt bàn định kỳ và giao lắp tận phòng.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* ========================================================
   COMMITMENTS STYLES — NỘI THẤT TINH HOA
   ======================================================== */
.nth-commitments {
    background: #FAF6F0;
    border-top: 1px solid #E6D8C8;
    border-bottom: 1px solid #E6D8C8;
    padding: 48px 0;
    font-family: 'Manrope', sans-serif;
}
.nth-commitments__grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 36px;
}
.nth-commitment-item {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 16px;
    position: relative;
}
.nth-commitment-item:not(:last-child)::after {
    content: '';
    position: absolute;
    top: 10%;
    right: -18px;
    height: 80%;
    width: 1px;
    background: #E6D8C8;
}
.nth-commitment-item__icon {
    width: 48px;
    height: 48px;
    border-radius: 2px;
    background: #F3E9DC;
    color: #5A4536;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #E6D8C8;
    transition: all 0.3s ease;
}
.nth-commitment-item:hover .nth-commitment-item__icon {
    background: #5A4536;
    color: #F3E9DC;
    border-color: #5A4536;
    transform: translateY(-2px);
}
.nth-commitment-item__title {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: 20px;
    font-weight: 500;
    color: #3A2E26;
    margin: 0 0 6px;
    line-height: 1.3;
}
.nth-commitment-item__desc {
    font-size: 13px;
    line-height: 1.65;
    color: #7E7065;
    margin: 0;
}

@media (max-width: 991px) {
    .nth-commitments__grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 28px;
    }
    .nth-commitment-item::after { display: none !important; }
}
@media (max-width: 575px) {
    .nth-commitments { padding: 36px 0; }
    .nth-commitments__grid {
        grid-template-columns: 1fr;
        gap: 24px;
    }
}
</style>
