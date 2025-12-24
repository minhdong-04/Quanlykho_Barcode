# Hướng dẫn tích hợp API (Frontend)

Tài liệu ngắn bằng tiếng Việt để frontend (Vue + Axios / Vite) gọi API Laravel (Sanctum).

1) Cấu hình CORS & Sanctum

- Đảm bảo `config/sanctum.php` có `stateful` chứa domain frontend (ví dụ `localhost:5173`).
- Trong `.env`:

  - `SESSION_DRIVER=cookie`
  - `SANCTUM_STATEFUL_DOMAINS=localhost:5173`

2) Lấy CSRF cookie và đăng nhập (Axios)

```js
import axios from 'axios';

axios.defaults.withCredentials = true;

// 1) Lấy CSRF cookie
await axios.get('http://backend.test/sanctum/csrf-cookie');

// 2) Đăng nhập
await axios.post('http://backend.test/api/login', {
  email: 'user@example.com',
  password: 'secret',
});

// Bây giờ yêu cầu tiếp theo sẽ có cookie session
const resp = await axios.get('http://backend.test/api/user');
```

3) Ví dụ các endpoint chính

- `POST /api/login` — đăng nhập
- `POST /api/logout` — đăng xuất
- `GET /api/products` — danh sách sản phẩm
- `POST /api/products` — tạo sản phẩm
- `POST /api/stock/in` — nhập kho
- `POST /api/stock/out` — xuất kho

4) Token API (nếu dùng token thay vì cookie)

Sau khi đăng nhập qua API, có thể tạo `personal access token` và sử dụng header `Authorization: Bearer <token>`.

5) Lưu ý bảo mật

- CORS: chỉ cho phép origin tin cậy.
- Không lưu password trong frontend.

6) Liên hệ backend

Nếu cần thay đổi endpoints hoặc schema JSON, hãy liên hệ với team backend. File controller chính nằm tại `app/Http/Controllers/Api/V1`.
