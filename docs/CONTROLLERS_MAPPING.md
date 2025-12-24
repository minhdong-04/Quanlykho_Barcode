Controller mapping: current -> proposed

- app/Http/Controllers/Api/V1/AuthController.php
  - Purpose: Login, Logout, Me
  - Kept in `Api/V1`.

- app/Http/Controllers/Api/V1/ProductController.php
  - Purpose: CRUD Products
  - Updated: store now validates via manual validator; uses `Request`.

- app/Http/Controllers/Api/V1/SupplierController.php
  - Purpose: CRUD Suppliers (kept)

- app/Http/Controllers/Api/V1/StockInController.php
  - Moved logic from `App\Http\Controllers\Api\Stock\StockInController` into this file.

- app/Http/Controllers/Api/V1/StockOutController.php
  - Moved logic from `App\Http\Controllers\Api\Stock\StockOutController` into this file.

- app/Http/Controllers/Api/V1/AlertController.php
  - Purpose: Low-stock alerts (kept)

- app/Http/Controllers/Api/V1/BarcodeController.php
  - Moved logic from `App\Http\Controllers\Api\Barcode\BarcodeController` into this file.

Notes:
- Some older controller files under `app/Http/Controllers/Api/*` remain for backward-compatibility but routes now point to `Api/V1` controllers.
- Tests run on sqlite in-memory and passed (6 tests, 32 assertions).
- Next: consider removing legacy files under `Api/*` after CI and runtime verification.
