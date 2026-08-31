# Hướng dẫn sử dụng Hệ thống Quản trị VMTA (Admin Panel User Guide)

Tài liệu này hướng dẫn chi tiết cách sử dụng các phân hệ quản lý của trang quản trị Vietnam Medical Tourism Alliance (VMTA). Hệ thống dựa trên Laravel 12, được thiết kế theo mô hình Monolith mô-đun tối ưu hóa cho điều phối viên và quản trị viên.

---

## Mục lục
1. [Tổng quan Bảng điều khiển (Dashboard)](#1-tổng-quan-bảng-điều-khiển-dashboard)
2. [Quản trị Nội dung Trang Web (Content CMS)](#2-quản-trị-nội-dung-trang-web-content-cms)
3. [Mạng lưới Liên minh & Đối tác (Alliance & Partner Catalog)](#3-mạng-lưới-liên-minh--đối-tác-alliance--partner-catalog)
4. [Hệ thống Chatbot AI & RAG (Knowledge Management)](#4-hệ-thống-chatbot-ai--rag-knowledge-management)
5. [Hộp thư Yêu cầu & Quy trình Điều phối (Inquiry Pipeline)](#5-hộp-thư-yêu-cầu--quy-trình-điều-phối-inquiry-pipeline)
6. [Quản lý Phương tiện (Media Manager)](#6-quản-lý-phương-tiện-media-manager)
7. [Quản lý Tài khoản & Phân quyền (RBAC)](#7-quản-lý-tài-khoản--phân-quyền-rbac)
8. [Cấu hình Hệ thống (System Settings)](#8-cấu-hình-hệ-thống-system-settings)

---

## 1. Tổng quan Bảng điều khiển (Dashboard)

Dashboard là nơi cung cấp các chỉ số đo lường hiệu quả hoạt động theo thời gian thực (real-time metrics) của hệ sinh thái du lịch y tế VMTA.

### Các chỉ số hiển thị chính:
- **Pageview — 30 ngày gần nhất**: Biểu đồ hiển thị lượt truy cập của trang public.
- **Lead — 30 ngày gần nhất**: Số lượng yêu cầu liên hệ / emergency lead mới tiếp nhận.
- **Chatbot messages — 30 ngày gần nhất**: Tần suất hội thoại của khách truy cập với trợ lý AI.
- **Phân bổ lead theo nguồn — 30 ngày**: Thống kê nguồn gốc lead (Form liên hệ, Khẩn cấp, Catalog, Chatbot chuyển tiếp...).
- **Top 10 trang theo pageview (30 ngày)**: Bảng liệt kê chi tiết các trang được đọc nhiều nhất kèm mã định danh `Metric key`.

> [!TIP]
> Sử dụng nút **"Làm mới"** (refresh icon) ở góc trên bên phải để cập nhật dữ liệu báo cáo mới nhất mà không cần tải lại toàn bộ trang.

---

## 2. Quản trị Nội dung Trang Web (Content CMS)

Hệ thống hỗ trợ biên tập nội dung đa ngôn ngữ (Tiếng Việt và Tiếng Anh) trực quan. Giao diện quản trị hiển thị các trường nhập liệu song ngữ song song hoặc theo thẻ.

### 2.1 Quản lý Trang chủ (Trang chủ)
Truy cập qua menu **Nội dung** -> **Trang chủ**. Giao diện chia thành các Tab chức năng tương ứng với các Section trên giao diện người dùng:

*   **Hero Section**:
    *   **Tiêu đề & Phụ đề (VI/EN)**: Tiêu đề lớn hiển thị đầu trang (ví dụ: *Liên minh Du lịch Y tế Việt Nam* / *Vietnam Medical Tourism Alliance*).
    *   **Mô tả (VI/EN)**: Đoạn giới thiệu ngắn về sứ mệnh hệ sinh thái.
    *   **CTA Button (VI/EN)**: Nút kêu gọi hành động (mặc định: *Tham gia hệ sinh thái* / *Join the ecosystem* và đường dẫn `/vi/contact`, `/en/contact`).
    *   **Bảng số liệu thống kê du khách**: Danh sách các quốc gia và số lượng thống kê (ví dụ: *USA - 10,000*, *Germany - 10,000*). Cho phép sử dụng nút `+ Thêm hàng` và `- Xoá hàng cuối` để cập nhật động số liệu.
*   **Về VMTA Section**:
    *   Biên tập nội dung mô tả chi tiết về Liên minh, hỗ trợ soạn thảo HTML cơ bản.
    *   Nút CTA liên kết đến trang giới thiệu chi tiết (`/vi/gioi-thieu` / `/en/about`).
    *   Danh sách 3 gạch đầu dòng cốt lõi (ví dụ: *Kết nối nguồn lực y tế*, *Chuẩn hóa toàn bộ hành trình*, *Tối ưu hiệu quả*).
*   **Các Tab khác**: *Giá trị cốt lõi, Giải pháp, Tầm nhìn & Sứ mệnh, Lợi ích, Công nghệ, Tại sao Việt Nam*.

### 2.2 Quản lý trang Giới thiệu (Giới thiệu)
Biên tập các thông tin lịch sử hình thành, đội ngũ sáng lập, các giá trị cam kết và cơ cấu tổ chức của VMTA.

### 2.3 Menu điều hướng (Menu điều hướng)
Giao diện quản lý cây menu Header và Footer dạng kéo thả (Drag-and-Drop) giúp quản trị viên dễ dàng thay đổi thứ tự và cấp độ của các danh mục điều hướng trên trang chủ.

---

## 3. Mạng lưới Liên minh & Đối tác (Alliance & Partner Catalog)

Catalog quản lý mạng lưới các Bệnh viện đặc biệt, khu nghỉ dưỡng cao cấp, bác sĩ chuyên khoa và danh mục dịch vụ y tế.

### 3.1 Quản lý trang Mạng lưới Liên minh
Quản lý trang giới thiệu chung về mạng lưới liên minh VMTA qua 5 Tab:
*   **Hero**: Tiêu đề và mô tả của trang mạng lưới.
*   **Tổng quan**: Giới thiệu chung và các cột mốc phát triển.
*   **Tiêu chuẩn**: Tiêu chuẩn khắt khe để gia nhập liên minh (áp dụng cho Bệnh viện và Resort).
*   **Bản đồ**: Tích hợp hiển thị vị trí các đối tác chiến lược.
*   **Tham gia liên minh**: Nội dung biểu mẫu kêu gọi đối tác mới nộp hồ sơ.

### 3.2 Quản lý Đối tác (Đối tác)
Danh sách các đối tác trong liên minh.
*   **Phân loại**: Bệnh viện đối tác (Hospitals) hoặc Khu nghỉ dưỡng phục hồi (Resorts).
*   **Nội dung**: Tên đối tác, Logo, Hình ảnh thư viện (Gallery), Mô tả chi tiết, Danh sách dịch vụ cung cấp.

### 3.3 Chuyên khoa (Chuyên khoa)
Danh mục các chuyên khoa điều trị (ví dụ: Tim mạch, Ung bướu, Tế bào gốc, IVF...). Mỗi chuyên khoa liên kết trực tiếp với các Bác sĩ điều trị và Bệnh viện thành viên để người dùng tìm kiếm chéo dễ dàng.

---

## 4. Hệ thống Chatbot AI & RAG (Knowledge Management)

Hệ thống tích hợp trợ lý AI thông minh sử dụng kỹ thuật RAG (Retrieval-Augmented Generation) để trả lời thông tin chính xác dựa trên cơ sở dữ liệu được admin cung cấp, bảo mật API key qua cơ chế Server-side Proxy.

### 4.1 Cấu hình Chatbot (Cài đặt Chatbot)
Truy cập qua **Chatbot** -> **Cài đặt**:
*   **API Configuration**:
    *   `Nhóm tài liệu (Document Group)`: Chọn bộ tri thức mặc định để chatbot tham chiếu.
    *   `AI Provider`: Nhà cung cấp dịch vụ AI (mặc định cấu hình qua server-side).
*   **Giới hạn phiên**:
    *   `Số câu hỏi tối đa / phiên`: Giới hạn số lượt chat của 1 khách truy cập mỗi phiên (mặc định: 5 câu hỏi để tối ưu chi phí API).
    *   `Thời hạn phiên (giây)`: Thời gian sống của 1 phiên chat (mặc định: 86400 giây tương đương 24 giờ).
*   **Câu hỏi gợi ý**:
    *   Thiết lập 4 câu hỏi mẫu hiển thị sẵn trên khung chat để người dùng click nhanh (ví dụ: *Gợi ý lịch trình du lịch hà nội 2 ngày 3 đêm*, *Đặc sản nên thử...*).

### 4.2 Quản lý Nhóm tài liệu (Document groups)
Phân loại các bộ tri thức theo chủ đề (ví dụ: nhóm `DULICH` - Hướng dẫn du lịch, nhóm `YTE` - Thông tin y khoa).
*   Cho phép tạo nhóm mới bằng cách nhập **Mã nhóm** (chữ in hoa không dấu) và **Tên hiển thị**.

### 4.3 Quản lý Tài liệu RAG (Documents)
Nơi tải lên các tài liệu tri thức (.pdf, .txt, .docx...) phục vụ huấn luyện Chatbot.
*   **Quy trình xử lý tri thức**:
    1.  Tải file tài liệu lên nhóm tương ứng.
    2.  Hệ thống sẽ lưu file ở trạng thái *Pending*.
    3.  Click nút **"Process tất cả pending"** (all_inclusive icon) để kích hoạt tiến trình chia nhỏ tài liệu (chunking) và chuyển đổi thành dạng Vector lưu trữ vào cơ sở dữ liệu tri thức.
*   **Tìm kiếm tri thức**: Tìm kiếm nhanh các chunk dữ liệu bằng ô nhập liệu tìm kiếm theo tên file hoặc nội dung chunk.

### 4.4 Lịch sử hội thoại (Conversations)
Nơi giám sát, lưu trữ toàn bộ nội dung khách hàng trò chuyện với AI.
*   Hiển thị thông tin: Tiêu đề hội thoại (câu hỏi đầu tiên), ID User (Session UUID), AI Provider sử dụng, Nhóm tài liệu tham chiếu, thời gian cập nhật.
*   Admin/Coordinator có thể bấm nút **"Xem"** để đọc toàn bộ lịch sử đoạn chat, từ đó hiểu rõ nhu cầu của khách hàng trước khi chủ động liên hệ hỗ trợ.

---

## 5. Hộp thư Yêu cầu & Quy trình Điều phối (Inquiry Pipeline)

Đây là phân hệ cốt lõi dành cho Điều phối viên (Coordinator) để tiếp nhận và xử lý các yêu cầu tư vấn du lịch y tế từ khách hàng.

### 5.1 Giao diện Hộp thư yêu cầu (Hộp thư yêu cầu)
*   **Lọc dữ liệu thông minh**:
    *   Phân loại theo **Trạng thái**: *Mới* (chưa xử lý), *Đã liên hệ*, *Đủ điều kiện* (được xác thực y khoa), *Hoàn tất* (đã chốt tour/khám), *Đã huỷ*.
    *   Phân loại theo **Nguồn lead**: *Form liên hệ* (contact form chung), *Form đối tác*, *Khẩn cấp* (Emergency pipeline), *Quick form Catalog* (yêu cầu từ trang chuyên khoa/bệnh viện), *Chatbot chuyển tiếp* (handoff từ AI).
*   **Chế độ xem**: Hỗ trợ chuyển đổi tab giữa **"Tất cả"** và **"Chỉ của tôi"** (các yêu cầu được gán cho chính tài khoản đang đăng nhập).

### 5.2 Giao trình xử lý chi tiết Yêu cầu (Lead Detail)
Click nút xem chi tiết (`visibility` icon) tại từng yêu cầu:
1.  **Xem nội dung**: Đọc chi tiết thông tin họ tên, email, số điện thoại khách hàng, cùng nội dung yêu cầu cụ thể.
2.  **Phân công xử lý (Assignment)**:
    *   Tại mục **"Gán cho"**, chọn tên Điều phối viên chịu trách nhiệm xử lý từ danh sách dropdown.
    *   Click **"Gửi yêu cầu"** để hoàn tất phân công. Người được gán sẽ nhận thông báo qua email và lead sẽ xuất hiện trong tab *Chỉ của tôi* của họ.
3.  **Ghi chép tiến trình (Add Note)**:
    *   Tại khung **"Thêm ghi chú"**, điều phối viên nhập nhật ký liên hệ (ví dụ: *Đã gọi điện tư vấn lúc 10h, khách đang cân nhắc gói khám tổng quát tại BV Vinmec*).
    *   Nội dung ghi chú sẽ được lưu trữ vĩnh viễn trong phần **Lịch sử** xử lý yêu cầu kèm thông tin người tạo và thời gian, phục vụ công tác bàn giao và PDPA compliance.

---

## 6. Quản lý Phương tiện (Media Manager)

Trình quản lý tệp tin trung tâm tích hợp sẵn trong nhân hệ thống, cho phép upload tệp và chia sẻ tài nguyên đồng bộ giữa các phân hệ CMS khác nhau.

### 6.1 Các chức năng cốt lõi:
*   **Bộ lọc định dạng nhanh**: Xem nhanh theo danh mục *Tất cả*, *Hình ảnh*, *Video*, *Tài liệu*.
*   **Quản lý thư mục**: Tạo thư mục mới (`create_new_folder` icon) để tổ chức dữ liệu khoa học.
*   **Tìm kiếm & Sắp xếp**: Tìm kiếm file theo tên trong thư mục hiện tại. Hỗ trợ sắp xếp theo tên file tăng/giảm dần, ngày tải lên, kích thước.
*   **Tải lên tệp lớn**: Hệ thống hỗ trợ **Chunked Upload** (chia nhỏ tệp khi truyền tải), giúp tải lên các video hoặc tài liệu hướng dẫn nặng hàng trăm MB mà không bị ngắt quãng hoặc lỗi giới hạn timeout của server.
*   **Quản lý Thùng rác**: Các file xóa sẽ đưa vào *Thùng rác*, cho phép khôi phục lại khi cần hoặc xóa vĩnh viễn để giải phóng bộ nhớ.

---

## 7. Quản lý Tài khoản & Phân quyền (RBAC)

Hệ thống bảo vệ nghiêm ngặt dựa trên cơ chế kiểm soát truy cập theo Vai trò (Role-Based Access Control).

### 7.1 Vai trò trong hệ thống (Default Roles):
1.  **Super Admin (Quản trị hệ thống)**:
    *   Toàn quyền truy cập và thao tác trên mọi phân hệ.
    *   Quản lý danh sách người dùng, phân vai trò, thay đổi cấu hình lõi của hệ thống.
2.  **Coordinator (Điều phối viên)**:
    *   Được phân quyền thao tác trên các phân hệ nghiệp vụ: Quản lý Nội dung (CMS), Quản lý Đối tác & Chuyên khoa (Catalog), Xử lý Hộp thư yêu cầu (Inquiry Pipeline), Quản lý Media.
    *   **Không có quyền** truy cập trang Quản lý Người dùng, Phân vai trò, Cài đặt Hệ thống và cấu hình lõi.

### 7.2 Quản lý vai trò (Roles)
Trang quản lý các quyền hạn chi tiết gắn liền với từng vai trò. Đảm bảo phân tách trách nhiệm rõ ràng nhằm tuân thủ quy trình kiểm soát dữ liệu.

---

## 8. Cấu hình Hệ thống (System Settings)

Nơi thiết lập các thông số vận hành cốt lõi của website.

### 8.1 Cấu hình Email (Tab Email)
*   Thiết lập máy chủ gửi mail phục vụ thông báo tự động (Inquiry notification, Double opt-in newsletter):
    *   `Email gửi mặc định (mail.from_address)`: Email đại diện gửi đi (ví dụ: `noreply@vmta.vn`).
    *   `Tên người gửi (mail.from_name)`: Tên thương hiệu hiển thị (ví dụ: `VMTA`).
    *   `Danh sách email nhận thông báo`: Các email nhận cảnh báo khi có lead mới (ngăn cách bởi dấu phẩy).
    *   `Cấu hình SMTP`: Nhập host, port, username, password SMTP bảo mật (dữ liệu password được mã hóa tự động bằng `APP_ENC_KEY` trong database).
*   **Gửi email thử (Test Connection)**: Click nút **"Gửi email thử"** để kiểm tra cấu hình SMTP có hoạt động chính xác hay không.

### 8.2 Cấu hình Website (Tab Trang web)
*   `Số hotline`: Số điện thoại khẩn cấp hiển thị trên website (mặc định: `1900-1234`).
*   `URL CTA mặc định`: Đường dẫn điều hướng chính trên trang chủ.
*   `URL form chuyển tiếp`: Đường dẫn nhận thông tin khi chatbot chuyển tiếp yêu cầu của khách hàng.
