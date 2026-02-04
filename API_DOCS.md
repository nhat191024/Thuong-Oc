# API Documentation

Tài liệu dưới đây mô tả các API endpoints hiện có trong hệ thống.

**Base URL**: `http://<your-domain>/api`
**Authentication**: Hầu hết các API yêu cầu xác thực bằng Bearer Token.

## 1. Authentication

### Đăng nhập
*   **URL**: `/login`
*   **Method**: `POST`
*   **Body Parameters**:
    *   `username` (string, required): Tên đăng nhập.
    *   `password` (string, required): Mật khẩu.
*   **Response**: Trả về Access Token, thông tin user và menu (nếu là nhân viên).
    
    Status: `200 OK`
    ```json
    {
        "access_token": "13|...",
        "token_type": "bearer",
        "user": {
            "id": 1,
            "username": "0999999999",
            "name": "Nhân viên Bàn"
        },
        "role": [
            "staff"
        ],
        "menus": { // Chỉ trả về khi role là STAFF
            "data": [
                {
                    "id": 1,
                    "name": "Ốc",
                    "foods": [
                        {
                            "id": 10,
                            "name": "Ốc Hương",
                            "is_favorite": false,
                            "price": 50000,
                            "image": "http://...",
                            "dishes": [
                                {
                                    "id": 20,
                                    "name": "Hấp sả",
                                    "additional_price": 0
                                }
                            ]
                        }
                    ]
                }
            ]
        }
    }
    ```

### Đăng xuất
*   **URL**: `/logout`
*   **Method**: `POST`
*   **Headers**: `Authorization: Bearer <token>`
*   **Response**: 
    ```json
    {
        "message": "Logged out successfully"
    }
    ```

## 2. Chi nhánh (Branches)

### Lấy danh sách chi nhánh
*   **URL**: `/branches`
*   **Method**: `GET`
*   **Headers**: `Authorization: Bearer <token>`
*   **Response**: 
    ```json
    {
        "data": [
            {
                "id": 1,
                "name": "Chi nhánh 1"
            },
            {
                "id": 2,
                "name": "Chi nhánh 2"
            }
        ]
    }
    ```

### Chi tiết chi nhánh
*   **URL**: `/branches/{id}`
*   **Method**: `GET`
*   **Headers**: `Authorization: Bearer <token>`
*   **Response**:
    ```json
    {
        "data": {
            "id": 1,
            "name": "Chi nhánh 1"
        }
    }
    ```

## 3. Bàn (Tables)

### Lấy danh sách bàn
*   **URL**: `/tables`
*   **Method**: `GET`
*   **Headers**: `Authorization: Bearer <token>`
*   **Query Parameters**:
    *   `branch_id` (integer, required): ID của chi nhánh cần lấy danh sách bàn.
*   **Response**:
    ```json
    {
        "data": [
            {
                "id": 10,
                "table_number": "Bàn 01",
                "is_active": "active" // hoặc "inactive" / tùy thuộc enum
            }
        ]
    }
    ```

## 4. Hóa đơn & Thanh toán (Bills & Payment)

### Xem thông tin hóa đơn của bàn
*   **URL**: `/tables/{table_id}/bill`
*   **Method**: `GET`
*   **Headers**: `Authorization: Bearer <token>`
*   **Response**:
    *   **Success (200)**:
    ```json
    {
        "data": {
            "id": 50,
            "table_id": 10,
            "table_number": "Bàn 01",
            "time_in": "2023-10-27T10:00:00.000000Z",
            "customer": null, // Hoặc object { id, name, phone }
            "details": [
                {
                    "id": 100,
                    "name": "Ốc Hương",
                    "quantity": 2,
                    "price": 50000,
                    "total": 100000,
                    "cooking_method": "Hấp sả",
                    "note": "Ít cay"
                }
            ],
            "total_amount": 100000,
            "discount_percent": 0,
            "discount_amount": 0,
            "voucher_code": null,
            "final_amount": 100000,
            "pay_status": "unpaid"
        }
    }
    ```
    *   **Not Found (404)**: Nếu không có hóa đơn chưa thanh toán.
    ```json
    {
        "message": "No unpaid bill found for this table."
    }
    ```

### Gắn khách hàng vào hóa đơn
*   **URL**: `/tables/{table_id}/bill/customer`
*   **Method**: `POST`
*   **Headers**: `Authorization: Bearer <token>`
*   **Body Parameters**:
    *   `phone` (string, required): Số điện thoại khách hàng.
    *   `name` (string, optional): Tên khách hàng (nếu chưa có trong hệ thống thì sẽ tạo mới).
*   **Response**:
    ```json
    {
        "message": "Customer attached successfully.",
        "customer": {
            "id": 5,
            "name": "Nguyễn Văn A",
            "phone": "0912345678"
        }
    }
    ```

### Gỡ khách hàng khỏi hóa đơn
*   **URL**: `/tables/{table_id}/bill/customer`
*   **Method**: `DELETE`
*   **Headers**: `Authorization: Bearer <token>`
*   **Response**:
    ```json
    {
        "message": "Customer removed successfully."
    }
    ```

### Áp dụng mã giảm giá
*   **URL**: `/tables/{table_id}/bill/discount`
*   **Method**: `POST`
*   **Headers**: `Authorization: Bearer <token>`
*   **Body Parameters**:
    *   `code` (string, required): Mã voucher/giảm giá.
*   **Response**:
    ```json
    {
        "message": "Discount applied successfully.",
        "discount_amount": 20000
    }
    ```

### Gỡ mã giảm giá
*   **URL**: `/tables/{table_id}/bill/discount`
*   **Method**: `DELETE`
*   **Headers**: `Authorization: Bearer <token>`
*   **Response**:
    ```json
    {
        "message": "Discount removed successfully."
    }
    ```

### Thanh toán
*   **URL**: `/tables/{table_id}/bill/pay`
*   **Method**: `POST`
*   **Headers**: `Authorization: Bearer <token>`
*   **Body Parameters**:
    *   `payment_method` (string, required): Phương thức thanh toán (`cash` hoặc `qr_code`).
    *   `table_id` (integer, required): ID của bàn (cần gửi kèm trong body để validate).
*   **Response**: 
    *   **Thanh toán tiền mặt (`cash`)**:
    ```json
    {
        "message": "Payment successful.",
        "amount": 80000
    }
    ```

    *   **Thanh toán QR (`qr_code`)**:
    ```json
    {
        "message": "Payment link created.",
        "data": {
            "bin": "9704...",
            "accountNumber": "...",
            "amount": 80000,
            "description": "...",
            "qrCode": "data:image/png;base64,..." // Hoặc link QR
        }
    }
    ```

### Cập nhật trạng thái hóa đơn
*   **URL**: `/tables/{table_id}/bill/status`
*   **Method**: `PATCH`
*   **Headers**: `Authorization: Bearer <token>`
*   **Body Parameters**:
    *   `status` (string, required): Trạng thái (`paid`, `unpaid`, `cancelled`).
    *   `payment_method` (string, optional): Phương thức thanh toán (`cash`, `qr_code`). Mặc định là `cash`.
*   **Response**:
    ```json
    {
        "message": "Bill status updated successfully."
    }
    ```

