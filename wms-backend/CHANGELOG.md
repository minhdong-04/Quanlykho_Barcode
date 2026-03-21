# WMS Backend - Release Notes

Changelog này thuộc dự án quản lý kho (WMS Barcode). Không sử dụng changelog của bộ khung `laravel/laravel`.

## [Unreleased]

## [v1.0.0] - 2026-01-04

### Tính năng chính
- Đăng nhập SPA (Laravel Sanctum) + đăng nhập mobile (token)
- Quên mật khẩu / đặt lại mật khẩu
- Quản lý sản phẩm (SKU, barcode, giá, ngưỡng tồn thấp)
- Nhập kho / xuất kho theo barcode
- Tồn kho (inventory) + cảnh báo tồn thấp
- Lịch sử xuất/nhập (stock movements) + export
- Quản lý nhà cung cấp (suppliers) + liên kết N-N với sản phẩm
- Quản lý người dùng + phân quyền theo role/ability

### Kỹ thuật
- Laravel Framework 11.x
- DB migrations cho: users, products, suppliers, inventories, stock_movements, barcode_devices, idempotency_keys
- Job/Queue (database queue) phục vụ tác vụ nền (ví dụ cảnh báo tồn thấp)
