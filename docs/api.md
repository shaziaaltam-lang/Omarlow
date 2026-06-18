# API Documentation

## المصادقة

جميع طلبات API تتطلب توكن API. يمكنك الحصول عليه بعد تسجيل الدخول.

### الرؤوس المطلوبة
```
Authorization: Bearer {token}
Content-Type: application/json
```

## الموارد

### العملاء (Clients)

#### الحصول على جميع العملاء
```
GET /api/v1/clients
```

**الاستجابة:**
```json
{
    "success": true,
    "message": "تم جلب العملاء بنجاح",
    "data": [
        {
            "id": 1,
            "name": "أحمد محمد",
            "email": "ahmad@example.com",
            "phone": "1234567890",
            "type": "individual"
        }
    ]
}
```

#### إنشاء عميل جديد
```
POST /api/v1/clients
```

**البيانات المطلوبة:**
```json
{
    "name": "أحمد محمد",
    "email": "ahmad@example.com",
    "phone": "1234567890",
    "address": "الرياض",
    "city": "الرياض",
    "country": "السعودية"
}
```

### الحالات (Cases)

#### الحصول على جميع الحالات
```
GET /api/v1/cases
```

#### إنشاء حالة جديدة
```
POST /api/v1/cases
```

**البيانات المطلوبة:**
```json
{
    "case_number": "CASE-001",
    "client_id": 1,
    "case_type_id": 1,
    "title": "قضية تعويض",
    "description": "تفاصيل القضية"
}
```

### الفواتير (Invoices)

#### الحصول على جميع الفواتير
```
GET /api/v1/invoices
```

#### إنشاء فاتورة جديدة
```
POST /api/v1/invoices
```

**البيانات المطلوبة:**
```json
{
    "invoice_number": "INV-001",
    "client_id": 1,
    "case_id": 1,
    "amount": 1000.00,
    "due_date": "2024-12-31",
    "description": "رسوم قانونية"
}
```

#### تحديد الفاتورة كمدفوعة
```
POST /api/v1/invoices/{id}/mark-as-paid
```

## أكواد الأخطاء

| الكود | المعنى |
|------|--------|
| 200 | طلب ناجح |
| 201 | تم الإنشاء بنجاح |
| 400 | طلب غير صحيح |
| 401 | غير مصرح |
| 403 | ممنوع |
| 404 | غير موجود |
| 422 | بيانات غير قابلة للمعالجة |
| 500 | خطأ في الخادم |
