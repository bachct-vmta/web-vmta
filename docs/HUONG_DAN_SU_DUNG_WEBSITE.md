# Hướng dẫn sử dụng website VMTA

Tài liệu này dành cho khách hàng và người quản trị website VMTA. Nội dung được ghi nhận khi truy cập trực tiếp website ngày 23/05/2026 tại:

- Website public: `http://vmta_laravel.test/`
- Khu vực quản trị: `http://vmta_laravel.test/admin`

Tài khoản admin test đăng nhập được:

- Email: `admin@nguyenkhoi.dev`
- Mật khẩu: `123456789`

## 1. Tổng quan website public

Website tự chuyển từ `http://vmta_laravel.test/` sang `http://vmta_laravel.test/vi`.

Menu chính public nhìn thấy:

- Giới thiệu: `/vi/gioi-thieu`
- Y tế - Trị liệu: `/vi/chuyen-khoa`
- Sản phẩm: `/vi/thanh-tuu-y-khoa`
- Mạng lưới Liên minh: `/vi/mang-luoi-lien-minh`
- Tin tức: `/vi/tin-tuc`
- Liên hệ: `/vi/lien-he`
- Chuyển ngôn ngữ: Tiếng Việt / English
- Nút Tham gia

Các thành phần dùng chung:

- Form đăng ký nhận tin ở cuối trang.
- Liên kết mạng xã hội: Facebook, YouTube, TikTok, Zalo.
- Thông tin hỗ trợ khách hàng qua email.
- Nút chatbot: "Xin chào! Tôi là trợ lý AI".

## 2. Các trang public đã kiểm tra

### Trang chủ

Mục đích: giới thiệu VMTA, giá trị cốt lõi, giải pháp, tầm nhìn - sứ mệnh, lợi ích, công nghệ, lý do chọn Việt Nam và tin tức nổi bật.

Người dùng có thể:

- Bấm "Tham gia hệ sinh thái".
- Bấm "Tìm hiểu thêm".
- Xem tin tức nổi bật.
- Đăng ký nhận tin.
- Mở chatbot.

Lưu ý: trang có video/khối media, cần kiểm tra sau khi thay ảnh hoặc nội dung trong admin.

### Giới thiệu

Mục đích: trình bày VMTA là ai, giá trị cốt lõi, cách VMTA hoạt động, điểm khác biệt và lời mời bắt đầu hành trình.

Người dùng có thể:

- Bấm "Khám phá hành trình".
- Bấm "Tham gia hệ sinh thái".
- Bấm "Nhận tư vấn".

### Y tế - Trị liệu

Mục đích: hiển thị danh sách chuyên khoa.

Nội dung nhìn thấy:

- Tiêu đề "Chuyên khoa".
- Ô tìm kiếm.
- Một chuyên khoa đang hiển thị: "Nha khoa".

Lưu ý: nếu thêm chuyên khoa trong admin nhưng không bật trạng thái hoạt động, chuyên khoa có thể không hiển thị ngoài public.

### Sản phẩm

Mục đích: hiển thị trang "Thành tựu y khoa tiêu biểu tại Việt Nam".

Nội dung nhìn thấy:

- Ghép đồng thời tim - phổi.
- Kỳ tích "ghép thận tự thân".
- Thay khớp háng toàn phần bằng công nghệ in 3D.
- Các nút "Xem chi tiết" và "Nhận tư vấn miễn phí".

### Mạng lưới Liên minh

Mục đích: giới thiệu mạng lưới đối tác VMTA, tiêu chuẩn liên minh, bản đồ mạng lưới và form tham gia liên minh.

Form tham gia gồm:

- Tên.
- Email.
- Số điện thoại.
- Ngành nghề.
- Tên doanh nghiệp.
- Ghi chú.

### Tin tức

Mục đích: hiển thị danh sách bài viết và tin mới.

Nội dung nhìn thấy:

- Khu tin mới.
- Khu bài viết.
- Các bài viết có nút "Xem thêm".

### Liên hệ

Mục đích: nhận yêu cầu tư vấn và yêu cầu tham gia hệ sinh thái.

Form dành cho khách hàng gồm:

- Họ tên.
- Email.
- Điện thoại.
- Bệnh lý.
- Ghi chú.

Form dành cho đối tác gồm:

- Họ tên.
- Email.
- Điện thoại.
- Ngành nghề.
- Tên doanh nghiệp.
- Ghi chú.

Lưu ý: các trường có dấu sao hoặc được trình duyệt báo bắt buộc cần nhập trước khi gửi.

## 3. Đăng nhập admin

1. Truy cập `http://vmta_laravel.test/admin`.
2. Website chuyển sang trang đăng nhập `http://vmta_laravel.test/login`.
3. Nhập email và mật khẩu.
4. Bấm "Đăng nhập".

Sau khi đăng nhập thành công, màn hình vào dashboard admin.

Menu quản trị nhìn thấy:

- Dashboard.
- Người dùng.
- Quản lý Media.
- Nội dung: Trang, Bài viết, Danh mục, Trang chủ, Giới thiệu, Mạng lưới Liên minh, Thành tựu Y khoa, Menu điều hướng.
- Mạng lưới: Đối tác, Chuyên khoa, Lead chuyên khoa.
- Chatbot: Cài đặt, Document groups, Documents, Conversations.
- Yêu cầu liên hệ.
- Newsletter.
- Vai trò.
- Cài đặt Media.
- Cài đặt hệ thống.

## 4. Hướng dẫn chức năng quản trị

### Dashboard

Mục đích: xem nhanh số liệu vận hành như pageview, lead, chatbot sessions, chatbot messages và top trang theo pageview.

Cách thêm mới: không áp dụng, không thấy nút thêm mới trên dashboard.

Cách chỉnh sửa: không áp dụng, dashboard chủ yếu để xem số liệu.

Cách xoá: không áp dụng, không thấy nút xoá.

Lưu ý khi dùng: bấm "Làm mới" nếu muốn cập nhật số liệu hiển thị.

### Người dùng

Mục đích: quản lý tài khoản đăng nhập admin và vai trò của từng người.

Cách thêm mới:

1. Vào Admin > Người dùng.
2. Bấm "Thêm mới".
3. Nhập họ tên, email, mật khẩu, xác nhận mật khẩu.
4. Chọn vai trò nếu cần.
5. Bật "Kích hoạt" nếu muốn tài khoản dùng được ngay.
6. Bấm "Tạo mới".

Cách chỉnh sửa:

1. Vào danh sách Người dùng.
2. Bấm biểu tượng sửa ở dòng người dùng.
3. Cập nhật thông tin cần thay đổi.
4. Lưu lại.

Cách xoá:

1. Chọn một hoặc nhiều người dùng trong danh sách.
2. Bấm "Xóa" trong thanh thao tác.
3. Xác nhận nếu hệ thống hỏi.

Lưu ý khi nhập dữ liệu:

- Email nên là email thật, không nhập trùng.
- Mật khẩu tối thiểu 8 ký tự theo gợi ý trên màn hình.
- Không xoá hoặc khoá tài khoản admin chính nếu chưa có tài khoản thay thế.
- Có nút "Đổi vai trò" và "Khóa" cho thao tác hàng loạt.

### Quản lý Media

Mục đích: quản lý ảnh, video, tài liệu và thư mục dùng trong nội dung website.

Cách thêm mới:

1. Vào Admin > Quản lý Media.
2. Bấm nút tải lên hoặc vùng tải lên nếu hiển thị.
3. Chọn file từ máy tính.
4. Bấm "Tải lên".
5. Có thể bấm nút tạo thư mục để tạo thư mục mới.

Cách chỉnh sửa:

1. Chọn file hoặc thư mục.
2. Dùng chức năng đổi tên nếu cần.
3. Với ảnh, màn hình có chức năng "Cắt ảnh" và "Cắt & Lưu".

Cách xoá:

1. Chọn file hoặc thư mục.
2. Bấm chuyển vào thùng rác.
3. Nếu cần xoá hẳn, dùng chức năng dọn sạch thùng rác.

Lưu ý khi nhập dữ liệu:

- Đặt tên file rõ nghĩa, không dùng tên quá dài.
- Ưu tiên ảnh đã nén nhẹ để website tải nhanh.
- Không tải file nhạy cảm lên Media.
- Nếu dùng URL ảnh ngoài, kiểm tra URL vẫn truy cập được.

### Trang nội dung

Mục đích: tạo các trang nội dung riêng ngoài các trang được thiết kế sẵn.

Cách thêm mới:

1. Vào Nội dung > Trang.
2. Bấm "Tạo trang".
3. Chọn trạng thái: bản nháp hoặc đã xuất bản.
4. Nhập template nếu có nhu cầu dùng mẫu riêng.
5. Nhập thời gian xuất bản nếu cần.
6. Nhập nội dung tiếng Việt và tiếng Anh: tiêu đề, slug, tóm tắt, nội dung.
7. Nhập thông tin SEO nếu cần.
8. Bấm "Tạo mới".

Cách chỉnh sửa:

1. Vào danh sách Trang.
2. Nếu có dữ liệu, bấm biểu tượng sửa ở dòng trang.
3. Cập nhật nội dung và lưu lại.

Cách xoá:

1. Chọn trang trong danh sách.
2. Bấm "Xoá".

Lưu ý khi nhập dữ liệu:

- Slug là phần đường dẫn, nên viết ngắn, không dấu, dùng dấu gạch ngang.
- Nếu chưa muốn public, để trạng thái "Bản nháp".
- Nên nhập cả tiếng Việt và tiếng Anh nếu website dùng hai ngôn ngữ.
- Khi kiểm tra, danh sách Trang đang không có dữ liệu.

### Bài viết

Mục đích: quản lý tin tức hiển thị tại trang Tin tức và khu tin nổi bật.

Cách thêm mới:

1. Vào Nội dung > Bài viết.
2. Bấm "Tạo bài viết".
3. Chọn trạng thái, chuyên mục và thời gian xuất bản.
4. Chọn ảnh đại diện nếu cần.
5. Bật "Nổi bật" nếu muốn ưu tiên hiển thị.
6. Nhập tiêu đề, slug, tóm tắt và nội dung cho tiếng Việt/tiếng Anh.
7. Bấm "Tạo mới".

Cách chỉnh sửa:

1. Vào danh sách Bài viết.
2. Bấm biểu tượng sửa ở dòng bài viết.
3. Cập nhật nội dung và lưu lại.

Cách xoá:

1. Chọn bài viết.
2. Bấm "Xoá".
3. Xác nhận nếu hệ thống hỏi.

Lưu ý khi nhập dữ liệu:

- Tiêu đề nên rõ, không quá dài.
- Tóm tắt nên ngắn vì có thể hiển thị ở danh sách tin.
- Chỉ đặt "Đã xuất bản" khi bài đã sẵn sàng.
- Kiểm tra ảnh đại diện sau khi lưu.

### Danh mục

Mục đích: nhóm bài viết theo chuyên mục.

Cách thêm mới:

1. Vào Nội dung > Danh mục.
2. Bấm "Tạo chuyên mục".
3. Chọn chuyên mục cha nếu đây là chuyên mục con.
4. Nhập thứ tự hiển thị.
5. Bật "Hoạt động" nếu muốn dùng.
6. Nhập tên, slug, mô tả cho tiếng Việt/tiếng Anh.
7. Bấm "Tạo mới".

Cách chỉnh sửa:

1. Vào danh sách Danh mục.
2. Bấm biểu tượng sửa.
3. Cập nhật thông tin và lưu.

Cách xoá:

1. Chọn danh mục.
2. Bấm "Xoá".

Lưu ý khi nhập dữ liệu:

- Không nên xoá danh mục đang có bài viết nếu chưa chuyển bài viết sang danh mục khác.
- Thứ tự nhỏ/lớn ảnh hưởng cách sắp xếp tuỳ cấu hình hiển thị.

### Trang chủ

Mục đích: chỉnh nội dung các khối trên trang chủ.

Các tab nhìn thấy:

- Hero.
- Giá trị cốt lõi.
- Về VMTA.
- Giải pháp.
- Tầm nhìn & Sứ mệnh.
- Lợi ích.
- Công nghệ.
- Tại sao Việt Nam.

Cách thêm mới:

- Không thấy nút tạo trang chủ mới.
- Trong một số tab có nút "+ Thêm hàng" hoặc "+ Thêm bullet" để thêm dòng/mục nội dung trong khối.

Cách chỉnh sửa:

1. Vào Nội dung > Trang chủ.
2. Chọn tab cần sửa.
3. Sửa tiêu đề, mô tả, nội dung, nhãn nút, URL nút hoặc các mục lặp lại.
4. Bấm "Lưu thay đổi" trong đúng khối đang sửa.

Cách xoá:

- Không thấy nút xoá toàn bộ trang chủ.
- Có nút "- Xoá hàng cuối" hoặc "- Xoá cuối" để xoá dòng/mục cuối trong một số khối.

Lưu ý khi nhập dữ liệu:

- Kiểm tra cả nội dung VI và EN.
- URL nút nên nhập đúng đường dẫn, ví dụ `/vi/lien-he`.
- Sau khi lưu, mở lại trang chủ public để kiểm tra bố cục.

### Trang Giới thiệu

Mục đích: chỉnh nội dung trang Giới thiệu public.

Các tab nhìn thấy:

- Hero.
- VMTA Là ai.
- Giá trị cốt lõi.
- Cách hoạt động.
- Khác biệt.
- Tại sao chọn VMTA.
- Bắt đầu cùng VMTA.

Cách thêm mới:

- Không thấy nút tạo trang giới thiệu mới.
- Có thể thay ảnh, nhập nội dung, cập nhật các thẻ/mục có sẵn.

Cách chỉnh sửa:

1. Vào Nội dung > Giới thiệu.
2. Chọn tab cần sửa.
3. Cập nhật tiêu đề, nội dung, ảnh, nút hoặc mô tả.
4. Bấm "Lưu thay đổi".

Cách xoá:

- Không thấy nút xoá trang giới thiệu.
- Nếu cần bỏ nội dung, xoá phần chữ trong ô nhập hoặc tắt/ẩn theo tuỳ chọn nếu màn hình có.

Lưu ý khi nhập dữ liệu:

- Nội dung có thể chứa định dạng HTML từ trình soạn thảo, nên không dán nội dung lộn xộn từ Word.
- Sau khi đổi ảnh, kiểm tra trang public trên cả desktop và mobile.

### Trang Mạng lưới Liên minh

Mục đích: chỉnh nội dung trang Mạng lưới Liên minh public.

Các tab nhìn thấy:

- Hero.
- Tổng quan.
- Tiêu chuẩn.
- Bản đồ.
- Tham gia liên minh.

Cách thêm mới:

- Không thấy nút tạo trang mới.
- Có thể nhập nội dung trong các tab có sẵn.

Cách chỉnh sửa:

1. Vào Nội dung > Mạng lưới Liên minh.
2. Chọn tab cần sửa.
3. Cập nhật tiêu đề, mô tả phụ, nội dung hoặc nhãn nút.
4. Bấm "Lưu thay đổi".

Cách xoá:

- Không thấy nút xoá trang.
- Có thể xoá bớt nội dung trong ô nhập nếu không muốn hiển thị đoạn đó.

Lưu ý khi nhập dữ liệu:

- Phần tiêu chuẩn có ghi "đúng 5 mục", nên không tự ý thay đổi số lượng nếu không chắc.
- Form tham gia liên minh ngoài public nhận dữ liệu về hệ thống yêu cầu/lead, cần kiểm tra sau khi chỉnh nội dung kêu gọi.

### Trang Thành tựu Y khoa

Mục đích: chỉnh trang thành tựu y khoa và quản lý các case lâm sàng.

Các tab nhìn thấy:

- Hero.
- Năng lực.
- Cam kết.
- Cases lâm sàng.

Cách thêm mới:

1. Vào Nội dung > Thành tựu Y khoa.
2. Trong tab Cases lâm sàng, bấm "+ Thêm case mới".
3. Chọn ảnh chính nếu cần.
4. Nhập thứ tự, bật "Hiển thị công khai".
5. Nhập tiêu đề, slug, subtitle, đoạn intro cho VI/EN.
6. Nhập các mục cho cột "Giải pháp đột phá", "Hiệu quả vượt trội", "Ý nghĩa".
7. Bấm "Lưu".

Cách chỉnh sửa:

1. Vào tab Cases lâm sàng.
2. Bấm "Sửa" tại case cần cập nhật.
3. Có thể bấm "Chi tiết" để chỉnh phần chi tiết nếu cần.
4. Lưu lại.

Cách xoá:

1. Vào tab Cases lâm sàng.
2. Bấm "Xóa" tại case cần xoá.
3. Xác nhận nếu hệ thống hỏi.

Lưu ý khi nhập dữ liệu:

- Slug là đường dẫn chi tiết case, nên ngắn và không dấu.
- Nếu không bật "Hiển thị công khai", case có thể không xuất hiện ngoài website.
- Với các khối Hero/Năng lực/Cam kết, bấm đúng nút "Lưu thay đổi" của khối đang sửa.

### Menu điều hướng

Mục đích: quản lý menu public như Main Navigation và Footer Navigation.

Cách thêm mới:

1. Vào Nội dung > Menu điều hướng.
2. Bấm "+ Tạo menu".
3. Nhập tên menu.
4. Chọn vị trí: Main Navigation, Page Sidebar, Footer 1 Navigation hoặc Footer 2 Navigation.
5. Bật "Hoạt động".
6. Bấm "Lưu".

Cách chỉnh sửa:

1. Vào danh sách Menu.
2. Bấm "Sửa" ở menu cần chỉnh.
3. Cập nhật tên, vị trí, trạng thái.
4. Ở phần "Các mục", có thể sửa nhãn VI/EN, URL VI/EN, icon CSS class, cách mở liên kết.
5. Có thể thêm mục từ Trang, Bài viết hoặc URL tuỳ chỉnh.
6. Bấm "Lưu".

Cách xoá:

1. Vào danh sách Menu.
2. Bấm "Xoá" tại menu hoặc mục menu cần xoá.
3. Xác nhận nếu hệ thống hỏi.

Lưu ý khi nhập dữ liệu:

- Không xoá Main Navigation nếu chưa có menu thay thế.
- URL nên bắt đầu bằng `/vi/...` hoặc `/en/...` nếu là đường dẫn nội bộ.
- Sau khi đổi menu, kiểm tra lại header/footer public.

### Đối tác

Mục đích: quản lý đối tác trong hệ sinh thái như bệnh viện/phòng khám, khách sạn, du lịch.

Cách thêm mới:

1. Vào Mạng lưới > Đối tác.
2. Bấm "Tạo đối tác".
3. Chọn loại đối tác.
4. Chọn logo nếu có.
5. Bật "Hoạt động".
6. Nhập website, điện thoại, email.
7. Nhập tên, mô tả, địa chỉ.
8. Bấm "Tạo mới".

Cách chỉnh sửa:

1. Vào danh sách Đối tác.
2. Bấm biểu tượng sửa ở dòng đối tác.
3. Cập nhật thông tin và lưu.

Cách xoá:

1. Chọn đối tác.
2. Bấm "Xoá" nếu màn hình cho phép.

Lưu ý khi nhập dữ liệu:

- Website nên nhập đầy đủ dạng `https://...`.
- Email và điện thoại nên là thông tin có thể liên hệ thật.
- Chỉ bật "Hoạt động" với đối tác đã kiểm tra thông tin.

### Chuyên khoa

Mục đích: quản lý chuyên khoa hiển thị tại trang Y tế - Trị liệu.

Cách thêm mới:

1. Vào Mạng lưới > Chuyên khoa.
2. Bấm "Tạo chuyên khoa".
3. Bật "Hoạt động" nếu muốn hiển thị public.
4. Bật "Hiện form tư vấn" nếu muốn khách gửi lead theo chuyên khoa.
5. Nhập tên, slug, breadcrumb, tiêu đề hero, mô tả cho VI/EN.
6. Chọn icon, ảnh giới thiệu nếu cần.
7. Nhập nội dung giới thiệu, thế mạnh, bệnh viện.
8. Bấm "Tạo mới".

Cách chỉnh sửa:

1. Vào danh sách Chuyên khoa.
2. Bấm biểu tượng sửa.
3. Cập nhật nội dung và lưu.

Cách xoá:

1. Chọn chuyên khoa.
2. Bấm "Xoá" nếu màn hình cho phép.

Lưu ý khi nhập dữ liệu:

- Slug ảnh hưởng URL public, đổi slug có thể làm link cũ bị sai.
- Nếu muốn nhận tư vấn, bật form tư vấn.
- Nên nhập đầy đủ VI/EN để tránh trang tiếng Anh thiếu nội dung.

### Lead chuyên khoa

Mục đích: xem yêu cầu tư vấn được gửi từ trang chuyên khoa.

Cách thêm mới: không thấy nút thêm lead trong admin; lead được tạo từ form ngoài website.

Cách chỉnh sửa:

1. Vào Mạng lưới > Lead chuyên khoa.
2. Bấm biểu tượng xem ở dòng lead.
3. Đổi trạng thái: Mới, Đã liên hệ hoặc Đã đóng.
4. Bấm "Lưu".

Cách xoá: không thấy nút xoá trên danh sách lead chuyên khoa khi kiểm tra.

Lưu ý khi nhập dữ liệu:

- Đây là dữ liệu khách gửi, không nên tự sửa nội dung liên hệ nếu không cần.
- Nên cập nhật trạng thái sau khi đã gọi hoặc gửi email cho khách.

### Cài đặt Chatbot

Mục đích: cấu hình chatbot public.

Cách thêm mới:

- Không thấy nút tạo chatbot mới.
- Có thể thêm/cập nhật câu hỏi gợi ý trong các ô "Câu hỏi gợi ý".

Cách chỉnh sửa:

1. Vào Chatbot > Cài đặt.
2. Nhập nhóm tài liệu nếu muốn chatbot ưu tiên nhóm cụ thể.
3. Nhập AI Provider nếu cần.
4. Cập nhật số câu hỏi tối đa mỗi phiên và thời hạn phiên.
5. Nhập tối đa 4 câu hỏi gợi ý.
6. Bấm "Lưu thay đổi".

Cách xoá:

- Xoá nội dung câu hỏi gợi ý trong ô nhập nếu không muốn hiển thị.
- Không thấy nút xoá chatbot.

Lưu ý khi nhập dữ liệu:

- Thời hạn phiên tính bằng giây, ví dụ 3600 là 1 giờ.
- Không đặt số câu hỏi tối đa quá thấp nếu muốn khách trao đổi đủ thông tin.

### Chatbot Document groups

Mục đích: quản lý nhóm tài liệu cho chatbot.

Cách thêm mới:

1. Vào Chatbot > Document groups.
2. Nhập mã nhóm.
3. Nhập tên hiển thị.
4. Bấm "Tạo nhóm".

Cách chỉnh sửa: không thấy nút sửa nhóm tài liệu khi kiểm tra.

Cách xoá:

1. Tại danh sách nhóm tài liệu, bấm "Xoá".
2. Xác nhận nếu hệ thống hỏi.

Lưu ý khi nhập dữ liệu:

- Mã nhóm nên viết hoa, dùng chữ, số, gạch dưới hoặc gạch ngang theo gợi ý trên màn hình.
- Không xoá nhóm đang được chatbot sử dụng nếu chưa chuyển tài liệu sang nhóm khác.

### Chatbot Documents

Mục đích: quản lý tài liệu RAG để chatbot tra cứu.

Trạng thái khi kiểm tra: màn hình hiển thị thông báo "Không thể tải từ Tourism API: Admin access required".

Cách thêm mới:

- Màn hình có khu "Upload tài liệu mới", nhưng khi kiểm tra báo chưa có nhóm tài liệu và cần tạo nhóm trước khi upload.

Cách chỉnh sửa:

- Không thấy nút sửa tài liệu trong trạng thái hiện tại.

Cách xoá:

- Không thấy nút xoá tài liệu trong trạng thái hiện tại.

Lưu ý khi nhập dữ liệu:

- Cần tạo Document group trước.
- Sau khi upload, màn hình ghi cần bấm "Process" để index trước khi chatbot tra được.
- Nếu còn lỗi "Admin access required", cần kiểm tra quyền truy cập Tourism API/tài khoản tích hợp.

### Chatbot Conversations

Mục đích: xem lịch sử hội thoại chatbot để audit/monitor.

Cách thêm mới: không thấy nút tạo hội thoại trong admin; hội thoại được tạo từ chatbot ngoài public.

Cách chỉnh sửa: không thấy nút sửa hội thoại; có nút "Xem" để xem chi tiết.

Cách xoá: không thấy nút xoá hội thoại khi kiểm tra.

Lưu ý khi nhập dữ liệu:

- Có ô tìm theo title hoặc nội dung message.
- Dữ liệu hội thoại có thể chứa thông tin khách hàng, không chia sẻ ra ngoài nếu không cần.

### Yêu cầu liên hệ

Mục đích: quản lý các yêu cầu được gửi từ form liên hệ, form đối tác hoặc chatbot chuyển tiếp.

Cách thêm mới: không thấy nút thêm yêu cầu trong admin; yêu cầu được tạo từ form public.

Cách chỉnh sửa:

1. Vào Yêu cầu liên hệ.
2. Bấm biểu tượng xem ở dòng yêu cầu.
3. Có thể gán người phụ trách.
4. Có thể thêm ghi chú.
5. Có thể cập nhật trạng thái qua các hành động trên màn hình.
6. Bấm gửi/lưu theo nút hiển thị.

Cách xoá:

1. Tại danh sách, bấm biểu tượng xoá ở dòng yêu cầu hoặc chọn nhiều dòng rồi bấm "Xoá".
2. Trong trang chi tiết có nút "Xoá yêu cầu".

Lưu ý khi nhập dữ liệu:

- Nên ghi chú ngắn gọn sau mỗi lần liên hệ khách.
- Không xoá yêu cầu khi chưa xử lý xong hoặc chưa lưu thông tin cần thiết.
- Bộ lọc có trạng thái và nguồn, dùng để tìm nhanh yêu cầu.

### Newsletter

Mục đích: quản lý người đăng ký nhận tin.

Cách thêm mới: không thấy nút thêm email trong admin; email được tạo từ form đăng ký nhận tin ngoài public.

Cách chỉnh sửa: không thấy nút sửa người đăng ký khi kiểm tra.

Cách xoá:

1. Vào Newsletter.
2. Bấm biểu tượng xoá tại dòng email hoặc chọn nhiều dòng rồi bấm "Xóa".

Lưu ý khi nhập dữ liệu:

- Có thể xuất CSV bằng nút "Xuất CSV".
- Không gửi email marketing cho người đã huỷ đăng ký.
- Bộ lọc có trạng thái: chờ xác nhận, đã xác nhận, đã hủy.

### Vai trò

Mục đích: quản lý nhóm quyền cho tài khoản admin.

Cách thêm mới:

1. Vào Vai trò.
2. Bấm "Thêm mới".
3. Nhập tên vai trò.
4. Nhập slug hoặc để hệ thống tự tạo nếu trống.
5. Nhập mô tả nếu cần.
6. Chọn "Vai trò mặc định cho người dùng mới" nếu phù hợp.
7. Tích các quyền cần cấp.
8. Bấm "Tạo mới".

Cách chỉnh sửa:

1. Vào danh sách Vai trò.
2. Bấm biểu tượng sửa.
3. Cập nhật tên, mô tả hoặc quyền.
4. Lưu lại.

Cách xoá:

1. Chọn vai trò.
2. Bấm "Xóa".

Lưu ý khi nhập dữ liệu:

- Chỉ cấp quyền cần thiết cho từng người.
- Không xoá vai trò đang có người dùng nếu chưa chuyển người dùng sang vai trò khác.
- Cẩn thận với quyền xoá, cài đặt hệ thống và quản lý người dùng.

### Cài đặt Media

Mục đích: cấu hình tải lên file, chunk upload, xem trước tài liệu và Google Drive Storage.

Cách thêm mới:

- Không thấy nút thêm cấu hình mới.
- Có nút "Kết nối Google Drive" nếu muốn kết nối lưu trữ Google Drive.

Cách chỉnh sửa:

1. Vào Cài đặt Media.
2. Cập nhật dung lượng file tối đa, thư mục upload mặc định, loại file cho phép.
3. Bật/tắt tải lên theo phần nếu cần.
4. Cập nhật kích thước chunk.
5. Bật/tắt xem trước tài liệu và chọn provider.
6. Bấm "Lưu cài đặt".

Cách xoá:

- Không thấy nút xoá cấu hình media.

Lưu ý khi nhập dữ liệu:

- Không đặt dung lượng file tối đa quá lớn nếu hosting giới hạn.
- Chỉ cho phép các loại file thật sự cần dùng.
- Nếu dùng Google Drive, cần kiểm tra kết nối trước khi cho admin upload file.

### Cài đặt hệ thống

Mục đích: quản lý email gửi đi và một số cấu hình website.

Cách thêm mới:

- Không thấy nút thêm cấu hình mới.

Cách chỉnh sửa:

1. Vào Cài đặt hệ thống.
2. Chọn tab Email hoặc Trang web.
3. Cập nhật email gửi, tên người gửi, người nhận thông báo, SMTP host, port, username, password, encryption.
4. Cập nhật các URL/nội dung website như CTA mặc định, handoff form URL, hotline nếu cần.
5. Bấm "Lưu thay đổi".
6. Có thể dùng "Gửi email thử" để kiểm tra.

Cách xoá:

- Không thấy nút xoá cấu hình hệ thống.
- Với mật khẩu SMTP, màn hình ghi "Để trống nếu không đổi".

Lưu ý khi nhập dữ liệu:

- Cấu hình email sai có thể làm form liên hệ không gửi thông báo.
- Không chia sẻ mật khẩu SMTP.
- Sau khi sửa email, nên gửi email thử.

## 5. Lỗi thường gặp

### Không đăng nhập được admin

Nguyên nhân có thể:

- Sai email hoặc mật khẩu.
- Tài khoản bị khoá.
- Website/session hết hạn.

Cách xử lý:

- Nhập lại email và mật khẩu.
- Nhờ tài khoản Super Admin kiểm tra trạng thái người dùng.
- Tải lại trang đăng nhập rồi thử lại.

### Bài viết/trang không hiển thị ngoài public

Nguyên nhân có thể:

- Trạng thái đang là "Bản nháp".
- Chưa đến thời gian xuất bản.
- Slug hoặc menu chưa trỏ đúng.

Cách xử lý:

- Chuyển trạng thái sang "Đã xuất bản".
- Kiểm tra thời gian xuất bản.
- Kiểm tra menu điều hướng và URL public.

### Chuyên khoa không hiển thị

Nguyên nhân có thể:

- Chuyên khoa chưa bật "Hoạt động".
- Slug bị sai.
- Nội dung chưa lưu.

Cách xử lý:

- Vào Mạng lưới > Chuyên khoa và bật "Hoạt động".
- Kiểm tra slug.
- Lưu lại và mở trang `/vi/chuyen-khoa`.

### Form liên hệ gửi không thấy phản hồi

Nguyên nhân có thể:

- Người dùng chưa nhập trường bắt buộc.
- Cấu hình email thông báo sai.
- Yêu cầu đã vào admin nhưng chưa được xử lý.

Cách xử lý:

- Kiểm tra Yêu cầu liên hệ trong admin.
- Kiểm tra Cài đặt hệ thống > Email.
- Gửi email thử.

### Ảnh không hiển thị đúng

Nguyên nhân có thể:

- File bị xoá hoặc nằm trong thùng rác.
- URL ảnh ngoài không còn truy cập được.
- Ảnh quá nặng hoặc sai định dạng.

Cách xử lý:

- Kiểm tra Quản lý Media.
- Tải lại ảnh mới.
- Dùng ảnh có dung lượng vừa phải.

### Chatbot Documents báo lỗi Admin access required

Trạng thái đã thấy khi kiểm tra: trang Chatbot Documents hiển thị "Không thể tải từ Tourism API: Admin access required".

Cách xử lý:

- Kiểm tra quyền tài khoản tích hợp Tourism API.
- Tạo Document group trước khi upload.
- Sau khi upload tài liệu, bấm "Process" để chatbot có thể tra cứu.

## 6. Lưu ý khi vận hành website

- Trước khi sửa nội dung lớn, nên chụp lại màn hình hoặc lưu bản nháp nội dung cũ.
- Sau khi chỉnh nội dung public, luôn mở lại website để kiểm tra.
- Với nội dung song ngữ, cập nhật cả tiếng Việt và tiếng Anh.
- Không xoá dữ liệu quan trọng nếu chưa chắc có bản sao lưu.
- Phân quyền admin theo nhu cầu thật, không cấp quyền Super Admin cho mọi người.
- Kiểm tra định kỳ Yêu cầu liên hệ, Lead chuyên khoa và Newsletter.
- Khi thay đổi cài đặt email/media/chatbot, nên kiểm tra lại ngay bằng thao tác thực tế.
- Tránh tải file quá nặng lên Media vì có thể làm website chậm.
- Không đưa thông tin mật như mật khẩu SMTP, API key, dữ liệu khách hàng vào bài viết hoặc trang public.

## 7. Trang/chức năng không truy cập được hoặc có cảnh báo

- Website public, các trang chính và admin đều truy cập được trong lần kiểm tra.
- Trang Chatbot Documents trong admin truy cập được, nhưng phần tải dữ liệu từ Tourism API báo lỗi: "Admin access required".
- Trang Nội dung > Trang truy cập được nhưng danh sách đang không có dữ liệu.

## 8. Câu hỏi còn lại

- Chưa xác nhận được luồng gửi form public có gửi email thành công hay không vì tài liệu chỉ ghi nhận thao tác truy cập và màn hình.
- Chưa xác nhận được quyền Tourism API cần cấu hình ở đâu để xử lý lỗi Chatbot Documents.
