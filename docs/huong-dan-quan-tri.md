# HƯỚNG DẪN QUẢN TRỊ NỘI DUNG WEBSITE VMTA

Chào mừng bạn đến với tài liệu hướng dẫn vận hành hệ thống quản trị (Admin Panel/CMS) của website **Vietnam Medical Tourism Alliance (VMTA)**. Tài liệu này được thiết kế đặc biệt với ngôn ngữ đơn giản, trực quan, hướng dẫn từng bước (Step-by-step) kèm theo hình ảnh thực tế giúp các Điều phối viên (Coordinator) và Quản trị viên (Admin) vận hành hệ thống dễ dàng mà không cần kiến thức về lập trình hay IT.

---

## MỤC LỤC
1. [Bắt đầu nhanh (Quick Start)](#phần-1-bắt-đầu-nhanh-quick-start)
2. [Đọc hiểu Bảng điều khiển (Dashboard)](#phần-2-đọc-hiểu-bảng-điều-khiển-dashboard)
3. [Quản lý Nội dung Trang Web (CMS)](#phần-3-quản-lý-nội-dung-trang-web-cms)
4. [Vận hành Mạng lưới Đối tác & Chuyên khoa (Catalog)](#phần-4-vận-hành-mạng-lưới-đối-tác--chuyên-khoa-catalog)
5. [Quản trị Trợ lý AI Chatbot & Tri thức (RAG)](#phần-5-quản-trị-trợ-lý-ai-chatbot--tri-thức-rag)
6. [Tiếp nhận & Phân công Yêu cầu (Inquiry Pipeline)](#phần-6-tiếp-nhận--phân-công-yêu-cầu-inquiry-pipeline)
7. [Thư viện Media & Tải lên tệp lớn](#phần-7-thư-viện-media--tải-lên-tệp-lớn)
8. [Quản trị Hệ thống (Dành riêng cho Super Admin)](#phần-8-quản-trị-hệ-thống-dành-riêng-cho-super-admin)
9. [Bảng tra cứu sự cố & Mẹo giải quyết nhanh](#phần-9-bảng-tra-cứu-sự-cố--mẹo-giải-quyết-nhanh)

---

## PHẦN 1: BẮT ĐẦU NHANH (QUICK START)

Để bắt đầu làm việc với hệ thống quản trị, bạn thực hiện theo các bước sau:

### 1. Truy cập liên kết quản trị
Mở trình duyệt web (Google Chrome, Safari, Microsoft Edge...) trên máy tính hoặc điện thoại và nhập đường dẫn sau:
👉 **`https://vmta.nadetoday.vn/admin`**

Hệ thống sẽ tự động chuyển hướng bạn đến trang Đăng nhập bảo mật.

![Giao diện đăng nhập hệ thống quản trị VMTA](/Users/nguyenkhoi/Data/Web_Project/VMTA_Laravel/docs/screenshots/admin-01-login.png)

### 2. Đăng nhập tài khoản
1. Nhập **Email** tài khoản của bạn (Ví dụ: `admin@vmta.vn` hoặc `coordinator@nguyenkhoi.dev`).
2. Nhập **Mật khẩu** chính xác. Bạn có thể bấm nút **"Hiện mật khẩu"** (hình con mắt) để kiểm tra ký tự đã nhập.
3. *Tùy chọn:* Tích chọn ô **"Ghi nhớ"** để duy trì trạng thái đăng nhập cho những lần sau (không khuyến nghị sử dụng trên máy tính công cộng).
4. Bấm nút **"Đăng nhập"**.

> [!CAUTION]
> **Quy tắc an toàn bảo mật:**
> - Luôn bấm nút **"Đăng xuất"** (logout) ở góc dưới cùng bên trái thanh trình đơn (Sidebar) sau khi làm việc xong để tránh rò rỉ dữ liệu.
> - Tuyệt đối không chia sẻ tài khoản cá nhân cho người khác. Mọi thao tác tạo mới, sửa đổi hay xóa dữ liệu của bạn đều được hệ thống tự động ghi lại vào Nhật ký hoạt động (Activity Log) để giám sát.

---

## PHẦN 2: ĐỌC HIỂU BẢNG ĐIỀU KHIỂN (DASHBOARD)

Ngay sau khi đăng nhập thành công, bạn sẽ được đưa đến trang **Tổng quan (Dashboard)**. Đây là "bộ não" hiển thị toàn bộ chỉ số đo lường hiệu quả hoạt động theo thời gian thực của website.

![Giao diện Dashboard tổng quan số liệu vận hành](/Users/nguyenkhoi/Data/Web_Project/VMTA_Laravel/docs/screenshots/admin-02-dashboard.png)

### 1. Ý nghĩa các con số thống kê
*   **Pageview hôm nay:** Tổng số lượt khách hàng click xem các trang trên website của bạn trong ngày hôm nay.
*   **Lead 7 ngày:** Số lượng khách hàng đã điền form đăng ký tư vấn/liên hệ trên website trong vòng 1 tuần qua.
*   **Chatbot sessions 7 ngày:** Số lượng cuộc hội thoại độc lập mà khách hàng đã thực hiện với trợ lý AI Chatbot của bạn trong 7 ngày qua.
*   **Chatbot messages 7 ngày:** Tổng số lượng tin nhắn qua lại mà khách hàng đã gửi cho Chatbot.

### 2. Biểu đồ trực quan và Top trang xem nhiều nhất
*   **Biểu đồ đường xu hướng:** Cho biết xu hướng tăng hay giảm của lượt truy cập, lead và tin nhắn chatbot theo từng ngày trong tháng.
*   **Biểu đồ phân bổ lead theo nguồn:** Thống kê khách hàng biết đến VMTA chủ yếu từ nguồn nào (*Form liên hệ, Hotline, Chatbot...*).

![Các biểu đồ thống kê trực quan trên Dashboard](/Users/nguyenkhoi/Data/Web_Project/VMTA_Laravel/docs/screenshots/admin-03-dashboard-charts.png)

*   **Bảng Top 10 trang xem nhiều nhất:** Liệt kê danh sách các trang/đường dẫn thu hút lượt đọc nhiều nhất để bạn biết khách hàng đang quan tâm đến nội dung nào.

> [!TIP]
> Số liệu trên Dashboard được hệ thống cập nhật tự động mỗi phút. Nếu bạn muốn nhìn thấy dữ liệu mới nhất ngay lập tức mà không cần tải lại toàn bộ trang web, hãy click vào nút **"Làm mới"** (biểu tượng mũi tên xoay tròn `refresh`) ở góc trên bên phải.

---

## PHẦN 3: QUẢN LÝ NỘI DUNG TRANG WEB (CMS)

Thanh menu bên trái (Sidebar) cung cấp phân hệ **Nội dung** để chỉnh sửa giao diện hiển thị của website public. Hệ thống VMTA hỗ trợ hiển thị song ngữ **Tiếng Việt (VI)** và **Tiếng Anh (EN)** cực kỳ mạnh mẽ.

### 3.1 Quy tắc chung khi biên tập song ngữ
*   Mọi thông tin chỉnh sửa (tiêu đề, mô tả, nội dung...) đều chia làm 2 ô nhập liệu rõ ràng: một ô có nhãn **VI** (Tiếng Việt) và một ô có nhãn **EN** (Tiếng Anh).
*   **Bắt buộc:** Bạn cần dịch thuật và điền đầy đủ cả 2 ô để tránh tình trạng khách hàng nước ngoài chuyển sang giao diện tiếng Anh bị thiếu nội dung hoặc hiển thị tiếng Việt bị lỗi.

---

### 3.2 Chỉnh sửa Nội dung Trang Chủ
Truy cập qua **Nội dung** -> **Trang chủ**. Giao diện chỉnh sửa được chia thành nhiều **Tab** tương ứng với các khối nội dung bên ngoài website:

![Giao diện chỉnh sửa Trang chủ dạng Tab song ngữ](/Users/nguyenkhoi/Data/Web_Project/VMTA_Laravel/docs/screenshots/admin-04-content-homepage.png)

#### Tab 1: Hero (Khối đầu trang chào mừng)
*   **Tiêu đề & Phụ đề (VI/EN):** Dòng chữ lớn đập vào mắt người dùng đầu tiên.
*   **CTA Button (Nút kêu gọi hành động):**
    *   *Nhãn nút (VI/EN):* Ví dụ: "Tham gia hệ sinh thái" / "Join the ecosystem".
    *   *Đường dẫn (URL):* Ví dụ: `/vi/contact` (phiên bản Việt) và `/en/contact` (phiên bản Anh).
*   **Bảng số liệu thống kê du khách động:** Hiển thị các nước và số liệu thống kê du lịch y tế (Ví dụ: USA - 10,000).
    *   Để thêm nước mới: Bấm nút **"+ Thêm hàng"**.
    *   Để bỏ dòng cuối cùng: Bấm nút **"- Xoá hàng cuối"**.
    *   Bấm **"Lưu thay đổi"** riêng cho khối Hero để cập nhật.

#### Tab 2: Về VMTA (Giới thiệu ngắn)
*   Cho phép biên tập văn bản giới thiệu về Liên minh, chèn liên kết chi tiết và tạo danh sách 3 gạch đầu dòng cốt lõi thể hiện giá trị hành trình du khách.
*   Sau khi sửa xong, luôn bấm nút **"Lưu thay đổi"** nằm ở cuối khối nội dung đó.

#### Các Tab khác (Giá trị cốt lõi, Giải pháp, Tầm nhìn, Lợi ích, Công nghệ, Tại sao chọn VN)
*   Các khối này hoạt động tương tự: cho phép chỉnh sửa tiêu đề lớn, tiêu đề nhỏ, mô tả đoạn văn, tải lên hình ảnh minh họa từ Thư viện và thêm/sửa các thẻ con hiển thị dạng lưới (Bento Grid).

---

### 3.3 Chỉnh sửa Trang Giới thiệu & Mạng lưới Liên minh
Cách hoạt động tương tự như Trang chủ:
1. Vào **Nội dung** -> **Giới thiệu** hoặc **Mạng lưới Liên minh**.
2. Chọn **Tab** nội dung tương ứng cần sửa (Ví dụ: Tab *Tiêu chuẩn* của trang Mạng lưới).

![Giao diện chỉnh sửa Trang Giới thiệu song ngữ](/Users/nguyenkhoi/Data/Web_Project/VMTA_Laravel/docs/screenshots/admin-05-content-about.png)

3. Cập nhật thông tin chữ hoặc chọn lại hình ảnh đại diện mới.
4. Bấm **"Lưu thay đổi"**.

---

### 3.4 Sắp xếp Menu điều hướng (Thanh Menu đầu trang & cuối trang)
Hệ thống cho phép bạn kéo thả cực kỳ trực quan để quản lý các nút điều hướng ngoài website public.
1. Vào **Nội dung** -> **Menu điều hướng**.
2. Tìm menu cần sửa (Ví dụ: *Main Navigation* - Menu chính) và bấm nút **"Sửa"** (biểu tượng bút chì).

![Giao diện quản lý Menu điều hướng kéo thả trực quan](/Users/nguyenkhoi/Data/Web_Project/VMTA_Laravel/docs/screenshots/admin-06-menus.png)

3. **Thay đổi vị trí các nút:** Click giữ chuột vào biểu tượng kéo thả và di chuyển mục menu lên hoặc xuống để đổi thứ tự hiển thị ngoài website.
4. **Thêm nút mới:** Bạn có thể nhập trực tiếp tên nút hiển thị (VI/EN) và đường dẫn liên kết (URL VI/EN), hoặc chọn nhanh từ trang hay bài viết có sẵn.
5. Bấm nút **"Lưu"** ở cuối trang để hoàn tất.

---

## PHẦN 4: VẬN HÀNH MẠNG LƯỚI ĐỐI TÁC & CHUYÊN KHOA (CATALOG)

Phân hệ **Mạng lưới** giúp quản lý hệ sinh thái y tế và dịch vụ cung cấp của VMTA.

### 4.1 Quản lý Chuyên khoa (Ví dụ: Nha khoa, Tim mạch, Tế bào gốc...)
Nơi giới thiệu năng lực điều trị y tế của liên minh. Khách hàng ngoài public có thể tìm kiếm chuyên khoa và gửi yêu cầu tư vấn trực tiếp.

![Giao diện danh sách các Chuyên khoa](/Users/nguyenkhoi/Data/Web_Project/VMTA_Laravel/docs/screenshots/admin-08-catalog-specialties.png)

#### Các bước tạo mới một Chuyên khoa:
1. Vào **Mạng lưới** -> **Chuyên khoa**. Bấm nút **"Tạo chuyên khoa"**.
2. Giao diện chia thành 5 tab nhập liệu song ngữ:

![Biểu mẫu tạo mới/chỉnh sửa Chuyên khoa đa ngôn ngữ](/Users/nguyenkhoi/Data/Web_Project/VMTA_Laravel/docs/screenshots/admin-28-specialty-form.png)

   *   **Tổng quan:** Chọn Icon, Ảnh giới thiệu, Tích chọn **"Hoạt động"** và tích chọn **"Hiện form tư vấn"** để mở khung đăng ký riêng của trang chuyên khoa ngoài public.
   *   **Cơ bản:** Nhập Tên chuyên khoa (VI/EN). Đường dẫn **Slug** tự động sinh ra (Ví dụ: *Nha khoa* -> *nha-khoa*).
   *   **Giới thiệu:** Nhập tiêu đề H2, câu giới thiệu ngắn và nội dung chi tiết bằng trình soạn thảo.
   *   **Thế mạnh & Bệnh viện:** Bấm nút **"+ Thêm"** tương ứng để tạo danh sách thế mạnh và liên kết các bệnh viện thành viên.
3. Bấm **"Tạo mới"** để lưu lại.

---

### 4.2 Quản lý Đối tác (Bệnh viện đối tác, Khách sạn, Du lịch)
Quản lý danh bạ thông tin các thành viên tham gia liên minh.

![Giao diện danh sách các Đối tác thành viên](/Users/nguyenkhoi/Data/Web_Project/VMTA_Laravel/docs/screenshots/admin-07-catalog-partners.png)

#### Hướng dẫn Thêm Đối tác mới:
1. Vào **Mạng lưới** -> **Đối tác**. Bấm nút **"Tạo đối tác"**.
2. Điền các trường thông tin trên biểu mẫu:

![Biểu mẫu tạo mới Đối tác](/Users/nguyenkhoi/Data/Web_Project/VMTA_Laravel/docs/screenshots/admin-12-partner-form.png)

   *   **Loại đối tác:** Chọn một trong 3 loại: *Bệnh viện / Phòng khám*, *Khách sạn*, hoặc *Du lịch*.
   *   **Logo:** Click chọn logo chính thức của đối tác từ Thư viện Media.
   *   **Liên hệ:** Nhập Website (dạng đầy đủ bắt đầu bằng `https://`), Số điện thoại và Email liên hệ của đối tác.
   *   **Nội dung:** Nhập tên đối tác, Địa chỉ và viết mô tả chi tiết năng lực của đối tác bằng trình soạn thảo văn bản.
3. Bấm **"Tạo mới"**.

> [!NOTE]
> Để tinh giản và tăng tốc độ nhập liệu cho quản trị viên, phân hệ **Đối tác** được thiết kế nhập liệu đơn ngôn ngữ (Tiếng Việt). Bạn chỉ cần điền thông tin vào các trường thông thường mà không cần dịch thuật song song.

---

### 4.3 Quản lý Hộp thư "Lead Chuyên khoa"
Khi khách hàng xem trang chuyên khoa ngoài public (Ví dụ: Trang Nha khoa) và điền vào form đăng ký tư vấn riêng của trang đó, dữ liệu sẽ đổ về đây.
1. Vào **Mạng lưới** -> **Lead chuyên khoa**.

![Giao diện danh sách Lead đăng ký tại trang Chuyên khoa](/Users/nguyenkhoi/Data/Web_Project/VMTA_Laravel/docs/screenshots/admin-24-specialty-leads.png)

2. Bạn sẽ thấy danh sách các yêu cầu gửi về bao gồm: Họ tên khách hàng, số điện thoại, email, chuyên khoa quan tâm và thời gian gửi.
3. Click chọn nút **"Xem"** (biểu tượng con mắt `visibility`) trên dòng yêu cầu:
   *   Đọc yêu cầu cụ thể của khách tại dòng **DEMAND** (Ví dụ: *Denta* - làm răng sứ) và lời nhắn tại dòng **MESSAGE**.
   *   Sau khi liên hệ, tư vấn cho khách xong, bạn click chọn **TRẠNG THÁI** phù hợp: *Mới* (chưa xử lý), *Đã liên hệ* (đang tư vấn), hoặc *Đã đóng* (đã xử lý xong/không liên lạc được).
   *   Bấm nút **"Lưu"** (biểu tượng `save`) để cập nhật trạng thái.

---

## PHẦN 5: QUẢN TRỊ TRỢ LÝ AI CHATBOT & TRI THỨC (RAG)

Website VMTA được trang bị Trợ lý AI thế hệ mới cực kỳ thông minh. AI có khả năng đọc hiểu các tài liệu văn bản (.pdf, .txt, .docx) do bạn cung cấp để tự động trả lời khách hàng một cách chính xác tuyệt đối mà không sợ bị sai lệch thông tin (gọi là cơ chế RAG - Retrieval-Augmented Generation).

### 5.1 Cấu hình hoạt động của Chatbot
Vào **Chatbot** -> **Cài đặt**:

![Giao diện cấu hình giới hạn và câu hỏi gợi ý cho Chatbot](/Users/nguyenkhoi/Data/Web_Project/VMTA_Laravel/docs/screenshots/admin-13-chatbot-settings.png)

*   **Số câu hỏi tối đa / phiên:** Đặt số lượt khách chat được hỏi AI trong 1 phiên (Khuyến nghị: **5**). Giới hạn này giúp kiểm soát chi phí sử dụng API AI của hệ thống.
*   **Thời hạn phiên (giây):** Thời gian lưu trạng thái hội thoại của 1 khách hàng trước khi reset (Khuyến nghị: **86400** giây, tương đương 24 giờ).
*   **Câu hỏi gợi ý:** Nhập 4 câu hỏi mẫu hiển thị sẵn trên khung chat để định hướng người dùng click nhanh (Ví dụ: *Gợi ý lịch trình du lịch hà nội 2 ngày 3 đêm*, *Khám sức khoẻ tổng quát cần chuẩn bị gì?*).
*   Bấm **"Lưu thay đổi"**.

---

### 5.2 Quy trình "Huấn luyện tri thức" cho AI (Cực kỳ quan trọng)
Để AI biết trả lời về các dịch vụ y tế hay thông tin du lịch mới nhất, bạn cần "cho AI ăn tài liệu". Quy trình gồm 3 bước đơn giản sau:

#### Bước 1: Tạo Nhóm tài liệu để phân loại
1. Vào **Chatbot** -> **Document groups**.
2. Nhập **Mã** nhóm (viết hoa không dấu, Ví dụ: `YTE` hoặc `DULICH`).
3. Nhập **Tên hiển thị** dễ nhớ (Ví dụ: *Thông tin Y khoa*, *Hướng dẫn du lịch*).
4. Bấm **"Tạo nhóm"**.

![Giao diện tạo Nhóm tài liệu Chatbot](/Users/nguyenkhoi/Data/Web_Project/VMTA_Laravel/docs/screenshots/admin-14-chatbot-docgroups.png)

#### Bước 2: Tải tệp tài liệu tri thức lên hệ thống
1. Vào **Chatbot** -> **Documents**.
2. Chọn Nhóm tài liệu vừa tạo ở Bước 1.
3. Bấm chọn tệp văn bản từ máy tính của bạn (hỗ trợ các định dạng .txt, .pdf, .docx chứa thông tin chi tiết về gói khám, cẩm nang du lịch...).
4. Bấm nút **"Upload"** để tải lên. Lúc này tài liệu sẽ ở trạng thái **Chờ xử lý (Pending)**.

![Giao diện tải lên và quản lý Tài liệu RAG](/Users/nguyenkhoi/Data/Web_Project/VMTA_Laravel/docs/screenshots/admin-15-chatbot-docs.png)

#### Bước 3: Kích hoạt cho AI học tài liệu
1. Tại màn hình **Documents**, bạn bấm vào nút lớn màu xanh **"Process tất cả pending"** (biểu tượng vô cực `all_inclusive`).
2. Hệ thống sẽ tự động chạy ngầm để chia nhỏ văn bản và nạp tri thức vào bộ nhớ của AI. Khi tiến trình hoàn tất, trạng thái tài liệu chuyển sang màu xanh **Processed**.

---

### 5.3 Giám sát cuộc trò chuyện (Conversations)
Để kiểm tra xem khách hàng đang nói chuyện gì với AI và có cần sự hỗ trợ của điều phối viên con người hay không:
1. Vào **Chatbot** -> **Conversations**.

![Giao diện danh sách hội thoại của khách hàng với Chatbot AI](/Users/nguyenkhoi/Data/Web_Project/VMTA_Laravel/docs/screenshots/admin-16-chatbot-conversations.png)

2. Click chọn nút **"Xem"** trên dòng hội thoại bạn quan tâm để đọc toàn bộ lịch sử tin nhắn qua lại.
3. **Mẹo đặc biệt:** Cuối mỗi câu trả lời của AI, bạn có thể bấm mở rộng dòng **"Nguồn tham khảo"** để biết chính xác AI đã trích dẫn thông tin từ câu chữ nào, tài liệu nào trong thư viện tri thức bạn đã nạp ở mục 5.2.

> [!WARNING]
> **Cảnh báo lỗi kỹ thuật "Admin access required":**
> Nếu bạn truy cập trang Documents hoặc Conversations và nhìn thấy dòng thông báo đỏ *"Không thể tải từ Tourism API: Admin access required"*, điều đó có nghĩa là kết nối API bảo mật giữa máy chủ website của bạn và máy chủ AI trung tâm đang gặp sự cố xác thực. 
> 👉 **Cách xử lý:** Đừng hoang mang, hãy liên hệ ngay với Bộ phận Kỹ thuật/IT để họ cập nhật lại mã khóa bảo mật (API Key) trong tệp cấu hình hệ thống. Dữ liệu tài liệu của bạn vẫn an toàn và không bị mất đi.

---

## PHẦN 6: TIẾP NHẬN & PHÂN CÔNG YÊU CẦU (INQUIRY PIPELINE)

Đây là phân hệ làm việc hàng ngày quan trọng nhất của các **Điều phối viên (Coordinator)** để tiếp nhận, chăm sóc và theo sát tiến trình các yêu cầu tư vấn dịch vụ du lịch y tế từ khách hàng gửi về.

### 1. Phân loại và Tìm kiếm Yêu cầu
Vào **Yêu cầu liên hệ** (Inquiry Pipeline):

![Giao diện hộp thư yêu cầu liên hệ Inquiry Pipeline](/Users/nguyenkhoi/Data/Web_Project/VMTA_Laravel/docs/screenshots/admin-17-inquiries.png)

*   **Bộ lọc Trạng thái:** Giúp bạn lọc nhanh danh sách yêu cầu theo tiến trình xử lý: *Mới* (chưa xử lý), *Đã liên hệ* (đang tư vấn), *Đủ điều kiện* (xác thực hồ sơ y khoa), *Hoàn tất* (chốt tour thành công), hoặc *Đã huỷ* (yêu cầu rác).
*   **Phân chia công việc:** Click chuyển đổi tab giữa **"Tất cả"** (xem toàn bộ yêu cầu của hệ thống) và **"Chỉ của tôi"** (chỉ hiển thị những yêu cầu được gán cho chính tài khoản của bạn xử lý).

---

### 2. Quy trình xử lý chi tiết Yêu cầu (Lead Pipeline Workflow)
Khi có một yêu cầu mới, bạn click nút **"Xem"** (biểu tượng con mắt `visibility`) trên dòng yêu cầu đó để mở trang chi tiết và thực hiện quy trình chăm sóc khách hàng:

![Giao diện xem và xử lý chi tiết một yêu cầu khách hàng](/Users/nguyenkhoi/Data/Web_Project/VMTA_Laravel/docs/screenshots/admin-17b-inquiry-detail.png)

*   **Đọc thông tin:** Xem Họ tên, số điện thoại, email, mức độ ưu tiên của yêu cầu (Ví dụ: **Khẩn** màu đỏ biểu thị khách hàng đang cần tư vấn gấp) và nội dung khách gửi tại phần **Nội dung**.
*   **Phân công người phụ trách (Assignment):** Trưởng nhóm/Admin chọn tên Điều phối viên phù hợp trong danh sách Dropdown và bấm nút **"Gửi yêu cầu"** (bên cạnh ô gán cho).
*   **Ghi chú nhật ký cuộc gọi (Add Note):** Kéo xuống mục **"Thêm ghi chú"**, nhập tóm tắt cuộc gọi (Ví dụ: *Đã gọi điện tư vấn, gửi báo giá qua Zalo...*) và bấm **"Gửi yêu cầu"**. Ghi chú này sẽ lưu vết vĩnh viễn và không thể chỉnh sửa để bảo mật thông tin.
*   **Chuyển đổi trạng thái xử lý:** Click chọn trạng thái mới ở phần **Trạng thái** (Ví dụ: Chuyển từ *Mới* sang *Đã liên hệ*) và bấm nút **"Lưu"**.

---

## PHẦN 7: THƯ VIỆN MEDIA & TẢI LÊN TỆP LỚN

Phân hệ **Quản lý Media** là nơi lưu trữ tập trung tất cả hình ảnh, tài liệu và video được sử dụng trên toàn bộ trang web VMTA.

### 1. Hướng dẫn tải lên tệp tin
1. Vào **Quản lý Media**.

![Giao diện Thư viện quản lý Media tập trung](/Users/nguyenkhoi/Data/Web_Project/VMTA_Laravel/docs/screenshots/admin-19-media.png)

2. Click trực tiếp vào vùng nét đứt có biểu tượng đám mây **"Tải lên / Drag & drop"** hoặc kéo thả trực tiếp tệp tin từ máy tính vào vùng này.

> [!TIP]
> **Tính năng Tải lên tệp lớn chia nhỏ (Chunked Upload):**
> Bạn hoàn toàn có thể tải lên các tệp video chất lượng cao nặng hàng trăm MB mà không sợ bị lỗi ngắt quãng. Hệ thống tự động chia nhỏ tệp tin khi truyền tải (Chunking), đảm bảo tải lên an toàn tuyệt đối ngay cả khi đường truyền internet của bạn không ổn định.

---

### 2. Tổ chức lưu trữ khoa học
*   **Tạo thư mục mới:** Bấm vào biểu tượng thư mục có dấu cộng **"Tạo thư mục"** (`create_new_folder`) ở thanh công cụ bên phải, nhập tên thư mục viết không dấu (Ví dụ: *anh-chuyen-khoa*) để lưu trữ tệp ngăn nắp.
*   **Thùng rác thông minh:** Khi bạn xóa một tệp tin, tệp đó sẽ nằm trong **Thùng rác** (`delete Thùng rác`). Bạn có thể vào đây để chọn **"Khôi phục"** tệp xóa nhầm hoặc chọn **"Xóa vĩnh viễn"** để giải phóng bộ nhớ.

---

## PHẦN 8: QUẢN TRỊ HỆ THỐNG (DÀNH RIÊNG CHO SUPER ADMIN)

Các cài đặt cấu hình lõi này ảnh hưởng trực tiếp đến sự vận hành của toàn bộ hệ thống, **chỉ dành riêng cho tài khoản có vai trò Super Admin**.

### 8.1 Quản lý Nhân sự (Người dùng)
Vào **Người dùng**:

![Giao diện danh sách quản lý tài khoản người dùng](/Users/nguyenkhoi/Data/Web_Project/VMTA_Laravel/docs/screenshots/admin-20-users.png)

*   **Thêm nhân sự mới:** Bấm **"Thêm mới"**, nhập họ tên, email, mật khẩu (tối thiểu 8 ký tự gồm cả chữ và số). Tại ô **Vai trò**, chọn quyền hạn phù hợp (*Super Admin* hoặc *Coordinator*). Tích chọn **"Kích hoạt"** và bấm **"Tạo mới"**.
*   **Khóa tài khoản:** Khi nhân sự nghỉ việc, bấm vào biểu tượng ba chấm (`more_vert`) trên dòng tài khoản đó và chọn **"Khóa"** để thu hồi quyền truy cập ngay lập tức.

---

### 8.2 Cấu hình máy chủ gửi Email tự động (SMTP)
Để hệ thống có thể tự động gửi email thông báo cho bạn khi có khách hàng điền form liên hệ:
1. Vào **Cài đặt hệ thống** -> Chọn tab **Email**.

![Giao diện cài đặt email máy chủ SMTP](/Users/nguyenkhoi/Data/Web_Project/VMTA_Laravel/docs/screenshots/admin-22-settings.png)

2. Nhập các thông số kỹ thuật hòm thư gửi đi của bạn: *Email gửi mặc định, Tên người gửi, Danh sách email nhận thông báo (ngăn cách bởi dấu phẩy) và cấu hình máy chủ SMTP*.
3. Bấm **"Lưu thay đổi"**.
4. **Kiểm tra kết nối:** Nhập email của bạn vào ô trống dưới phần **"Gửi email thử"** và bấm nút **"Gửi email thử"** (`outgoing_mail`) để tự kiểm tra kết nối xem SMTP hoạt động chưa.

---

### 8.3 Cài đặt thông tin Website chung
Vào **Cài đặt hệ thống** -> Chọn tab **Trang web**:

![Giao diện cấu hình Website chung, Hotline và Logo](/Users/nguyenkhoi/Data/Web_Project/VMTA_Laravel/docs/screenshots/admin-22b-settings-website.png)

*   **Favicon:** Chọn ảnh vuông PNG đại diện từ Thư viện Media để làm biểu tượng nhỏ hiển thị trên tab trình duyệt web.
*   **Logo trang:** Chọn ảnh Logo chính thức của VMTA hiển thị tại đầu trang (Header) và cuối trang (Footer).
*   **Số hotline:** Nhập số hotline chính của liên minh (Ví dụ: `1900-1234`).
*   **URL form chuyển tiếp:** Đường link dẫn tới form Google Form hoặc Tally để AI Chatbot tự động gửi hồ sơ khách hàng sang khi cuộc chat đạt yêu cầu chuyển tiếp.
*   Bấm **"Lưu thay đổi"**.

---

## PHẦN 9: BẢNG TRA CỨU SỰ CỐ & MẸO GIẢI QUYẾT NHANH

Dưới đây là các sự cố thường gặp nhất của người dùng không chuyên và cách xử lý đơn giản trong vòng 30 giây:

| Sự cố phát sinh | Nguyên nhân chủ yếu | Giải pháp tự xử lý nhanh |
| :--- | :--- | :--- |
| **Không thể đăng nhập vào Admin Panel** | 1. Nhập sai Email hoặc mật khẩu.<br>2. Tài khoản đang bị quản trị viên khóa.<br>3. Session (phiên làm việc) bị hết hạn do mở trang quá lâu. | 1. Bấm nút "Hiện mật khẩu" để kiểm tra xem có gõ sai phím Caps Lock hay bộ gõ tiếng Việt EVKey/VietKey không.<br>2. Tải lại trang web (`F5`) rồi đăng nhập lại.<br>3. Nhờ tài khoản Super Admin khác kiểm tra xem tài khoản có đang ở trạng thái "Kích hoạt" không. |
| **Đã viết bài/tạo chuyên khoa mới nhưng ngoài website public không thấy hiển thị** | 1. Trạng thái của bài viết/chuyên khoa đang để là "Bản nháp".<br>2. Bạn chưa tích chọn ô "Hoạt động".<br>3. Đường dẫn (Slug) bị để trống hoặc trùng lặp. | 1. Vào lại trang chỉnh sửa Bài viết/Chuyên khoa đó.<br>2. Kiểm tra phần Trạng thái, chuyển từ **Bản nháp** sang **Đã xuất bản** hoặc tích chọn ô **Hoạt động**.<br>3. Bấm **Lưu** và mở trang public để kiểm tra lại. |
| **Chatbot AI trả lời sai thông tin hoặc bảo "Tôi không biết thông tin này"** | 1. Bạn chưa tải tài liệu tri thức lên hoặc tải sai Nhóm tài liệu.<br>2. Bạn mới tải tài liệu lên nhưng chưa kích hoạt cho AI học. | 1. Vào **Chatbot** -> **Documents**.<br>2. Kiểm tra xem tài liệu đã có trong danh sách chưa và Nhóm tài liệu đã chọn đúng chưa.<br>3. Bấm nút màu xanh **"Process tất cả pending"** và chờ trạng thái tài liệu chuyển sang màu xanh **Processed** là AI sẽ trả lời được ngay. |
| **Không nhận được email thông báo khi có khách hàng điền Form** | 1. Cấu hình hòm thư gửi đi SMTP đang bị sai thông số hoặc mật khẩu SMTP bị đổi.<br>2. Ô nhập danh sách email nhận thông báo bị thiếu hoặc viết sai email. | 1. Vào **Cài đặt hệ thống** -> Tab **Email**.<br>2. Kiểm tra lại danh sách email nhận thông báo xem có gõ sai dấu phẩy hoặc khoảng trắng không.<br>3. Thử chạy tính năng **"Gửi email thử"** để xem hệ thống báo lỗi gì và gửi lỗi đó cho IT/kỹ thuật xử lý. |
| **Tải ảnh lên Thư viện bị báo lỗi hoặc ảnh hiển thị méo mó ngoài Web** | 1. Dung lượng ảnh quá nặng vượt giới hạn máy chủ.<br>2. Định dạng tệp không được hỗ trợ.<br>3. Kích thước ảnh (tỷ lệ dọc/ngang) bị lệch chuẩn. | 1. Ưu tiên tải lên ảnh có định dạng phổ biến như PNG, JPG, WEBP.<br>2. Sử dụng các trang web nén ảnh miễn phí (Ví dụ: *TinyPNG*) để giảm dung lượng ảnh xuống dưới 1MB trước khi tải lên.<br>3. Dùng công cụ **"Cắt ảnh"** ngay trên giao diện Media để căn chỉnh tỷ lệ chuẩn trước khi chèn vào bài viết. |

---
*Chúc bạn có những trải nghiệm làm việc tuyệt vời và hiệu quả cùng hệ thống quản trị VMTA!*
