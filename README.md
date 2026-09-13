# Quản Lý Thư Viện - Project Architecture Documentation

## Tổng quan (Project Overview)
Dự án **Quản Lý Thư Viện (Library Management System)** là hệ thống web dùng để quản lý hoạt động thư viện: tra cứu/mượn/trả sách, quản lý độc giả/thẻ thư viện, sách/bản sao, phiếu mượn, báo cáo sự cố, thanh lý sách, và thống kê.

**Công nghệ chính:**
- **Backend:** PHP 7/8 (procedural/OOP mix), MySQL (DB: `QuanLyThuVien`).
- **Frontend:** HTML5, CSS3 (custom per-page styles), JavaScript vanilla, FontAwesome icons, Blade-like PHP views.
- **Environment:** XAMPP (Apache + MySQL), chạy local tại `c:/xampp/htdocs/php/project`.
- **Auth:** Session-based roles (`QuanTriVien`, `ThuThu`, `DocGia`), login/register.
- **Key Features:** Mượn/trả trực tiếp/online, kiểm tra hạn mức/quá hạn/thẻ hết hạn, thanh lý/hỏng sách, lịch sử hoạt động.

## Cấu trúc thư mục (Directory Structure)
```
project/
├── config/
│   └── db.php                # DB connection (mysqli to QuanLyThuVien)
├── actions/                   # Business logic (controllers)
│   ├── auth-*.php            # Auth-protected actions (e.g., auth-muon-tra.php, auth-quan-ly-sach.php)
│   └── auth-Login.php        # Auth handlers
├── views/                     # UI templates (PHP views)
│   ├── Index-sidebar.php     # Sidebar nav (role-based)
│   ├── Index-topbar.php      # Topbar (notifications, user info)
│   ├── Login.php             # Public login/register
│   ├── muon-tra-sach.php     # Borrow/return UI
│   ├── quan-ly-*.php         # Admin pages (sach, doc-gia, phieu-muon, etc.)
│   └── thong-ke.php          # Stats
├── assets/
│   ├── css/                  # Page-specific CSS (e.g., style-muon-tra.css)
│   ├── js/                   # Effects (auth-effects.js, login.js)
│   └── images/               # Assets
├── includes/                  # Shared components (not detailed)
└── uploads/books/            # Book images (inferred)
```

**MVC Pattern:** actions/ = Controllers (POST/GET handlers), views/ = Views (render HTML/tables/modals), config/ = Config/Models (DB queries inline).

## Cơ sở dữ liệu (Database Schema)
**DB Name:** `QuanLyThuVien` (MySQL via XAMPP).

**Bảng chính & Mối quan hệ:**
- **DauSach** (head books): MaDauSach (PK), TenSach, NamXuatBan, GiaSach, MaTheLoai → TheLoai, MaNXB → NhaXuatBan. **1:N** BanSach, TacGia_Sach.
- **BanSach** (book copies): MaBanSach (PK), MaDauSach → DauSach, TinhTrang (0=borrowed,1=available,2=lost,3=liquidated), TinhTrangVatLy (Bình thường/Hỏng/Rách/Mất).
- **DocGia** (readers): MaDocGia (PK), HoTen, TenDangNhap → **1:1** TheThuVien.
- **TheThuVien** (library cards): SoThe (PK), MaDocGia → DocGia, SoSachMuonToiDa, TrangThai (0=locked,1=active), NgayHetHan.
- **PhieuMuon** (loan receipts): MaPhieuMuon (PK), SoThe → TheThuVien, NgayHenTra, NguoiTao, GhiChu. **1:N** ChiTietMuonTra.
- **ChiTietMuonTra** (loan details): MaPhieuMuon → PhieuMuon, MaBanSach → BanSach, NgayTraThucTe (NULL=borrowed).
- **yeucaumuon** (online requests): MaYeuCau (PK), MaDocGia → DocGia, MaBanSach → BanSach, TrangThai (ChoDuyet/DaDuyet/TuChoi).
- **TheLoai** (categories): MaTheLoai (PK), TenTheLoai → DauSach.
- **TacGia** (authors): MaTacGia (PK), TenTacGia → **N:N** DauSach via TacGia_Sach.
- **TacGia_Sach** (author-book junction): MaDauSach → DauSach, MaTacGia → TacGia.
- **NhaXuatBan** (publishers): MaNXB (PK), TenNXB → DauSach.
- **BaoCaoSuCo** (reports): LoaiSuCo (Vấn đề thẻ/Hết sách/Web lỗi), TrangThai (Chờ xử lý).
- **LichSuHoatDong** (audit log): NguoiThucHien, HanhDong, ChiTiet.
- **TaiKhoan** (users): TenDangNhap, role (QuanTriVien/ThuThu/DocGia).

**Key Constraints:** TinhTrang enforces availability, JOINs for reports (GROUP_CONCAT authors).

## Luồng nghiệp vụ chính (Core Logic)
**1. Mượn sách (Borrow Flow - actions/auth-muon-tra.php):**
   - **Trực tiếp (3 steps):** Check card (valid/active/not expired/no overdue/max limit) → Check book (TinhTrang=1) → Create PhieuMuon + ChiTietMuonTra, UPDATE BanSach TinhTrang=0.
   - **Online (DocGia):** Select available books → INSERT yeucaumuon (ChoDuyet) → ThuThu approve → Auto-create PhieuMuon (14-day due).
   - Validations: Overdue check, card status, book availability.

**2. Trả sách (Return Flow - quan-ly-phieu-muon.php):**
   - Modal: Select condition (Bình thường/Mất/Hỏng/Rách) → Calc fine (e.g., lost=50% price) → UPDATE BanSach (TinhTrangVatLy, TinhTrang=1/2/3), SET NgayTraThucTe.

**3. Quản lý sách (auth-quan-ly-sach.php):**
   - Add DauSach + copies (auto MaBanSach=MVxxx-xxxx) → Link authors via TacGia_Sach → Delete cascades to BanSach/TacGia_Sach.

**4. Other:** Thanh lý (UPDATE TinhTrang=3 + log), reports (BaoCaoSuCo), stats (counts overdue/available).

**Error Handling:** Session role checks, SQL prepared stmts (partial), redirectWithMsg.

## Giao diện & Thành phần (UI Components)
- **Layout:** Sidebar (Index-sidebar.php: role-based nav e.g., DocGia=Tra cứu/Đăng ký; Admin=full modules) + Topbar (Index-topbar.php: search, notifs, profile) + Content (tables/modals/forms).
- **Theme:** Blue primary (#4e73df/#2e59d9), CSS vars (--primary-color), responsive tables (overflow-x:auto), badges (bg-ok/late/the/sach/web).
- **Components:**
  | Type | Description |
  |------|-------------|
  | Tables | Custom (thead/tbody, search/filter, JS sort?) |
  | Modals | Return book, confirm delete |
  | Forms | POST to actions/, JS dynamic (add/remove books) |
  | Alerts | Success/danger with icons |
  | Badges | Status (Quá hạn/Đang mượn/Chờ duyệt) |
- **JS:** Toggle password, form dynamics (e.g., borrowForm append inputs), modals.
- **Responsive:** Mobile-friendly cards/tables.

## Hướng dẫn nâng cấp (Scalability Note)
- **New Feature Consistency:**
  1. **File Structure:** New view: `views/new-feature.php` (include sidebar/topbar). Action: `actions/auth-new-feature.php` (session role check, DB logic).
  2. **DB:** Extend existing tables or add new (e.g., new table FK to DauSach/DocGia). Use prepared stmts.
  3. **Auth:** if(!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['QuanTriVien','ThuThu'])) → redirect.
  4. **UI:** Copy pattern from quan-ly-sach.php (table + form + JS edit/delete). Use existing CSS (style-main.css + page-specific).
  5. **Logic:** Reuse functions (getBookInfo, etc.). Validate (TinhTrang, dates, limits). Log to LichSuHoatDong.
  6. **Best Practices:** mysqli_real_escape_string/prepare everywhere. Add pagination for large tables. JS validation client-side.
  7. **Testing:** Check roles (DocGia/ThuThu/Admin), edge cases (overdue, expired card, 0 stock).
- **Avoid:** Raw SQL dumps, no framework → keep procedural. Backup DB before migrations.
- **Extensibility:** Add roles via TaiKhoan. Scale: Index DB fields (MaBanSach, SoThe, dates).

*Generated by BLACKBOXAI - Auto-updated structure for any AI reference.*
