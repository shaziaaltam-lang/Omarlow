# دليل المطور

## البيئة المحلية

### المتطلبات
- PHP 8.2+
- Composer
- Node.js 18+
- MySQL 8.0+
- Git

### خطوات التثبيت

1. **استنساخ المستودع**
```bash
git clone https://github.com/shaziaaltam-lang/Omarlow.git
cd Omarlow
```

2. **تثبيت الحزم**
```bash
composer install
npm install
```

3. **إعداد البيئة**
```bash
cp .env.example .env
php artisan key:generate
```

4. **إنشاء قاعدة البيانات**
```bash
# تحديث بيانات الاتصال في .env
php artisan migrate
php artisan db:seed
```

5. **بدء الخادم**
```bash
# محطة 1: بدء الخادم
php artisan serve

# محطة 2: بناء الأصول
npm run dev
```

زيارة التطبيق على: http://localhost:8000

## معايير الكود

### PHP
- اتبع معايير PSR-12 و PSR-4
- استخدم Type Hints
- اكتب PHPDoc للدوال العامة
- استخدم Eloquent بدلاً من SQL الخام

### JavaScript/Vue
- استخدم Composition API
- اتبع معايير ESLint
- استخدم Prettier للتنسيق

### الاختبارات
```bash
# تشغيل جميع الاختبارات
php artisan test

# اختبارات محددة
php artisan test tests/Feature/AuthTest.php

# مع تقرير التغطية
php artisan test --coverage
```

## بنية المشروع

```
app/
├── Console/          # أوامر Artisan
├── Exceptions/       # معالجات الأخطاء
├── Http/
│   ├── Controllers/  # المتحكمات
│   ├── Middleware/   # البرمجيات الوسيطة
│   └── Requests/     # طلبات النموذج
├── Models/           # نماذج Eloquent
├── Services/         # الخدمات
├── Traits/           # السمات
└── Providers/        # مزودو الخدمات

config/              # ملفات التكوين
database/
├── migrations/      # هجرات قاعدة البيانات
├── seeders/         # بذور البيانات
└── factories/       # مصانع البيانات

resources/
├── views/           # عروض Blade
├── js/              # ملفات JavaScript
└── lang/            # الترجمات

routes/              # مسارات التطبيق
tests/               # الاختبارات
```

## التكاملات الخارجية

### البريد الإلكتروني
- اعداد MailTrap أو SMTP

### الرسائل النصية (SMS)
- تكامل Twilio

### WhatsApp
- تكامل WhatsApp Business API

### الدفع
- تكامل Stripe أو PayPal

## أوامر Artisan المهمة

```bash
# إنشاء نسخة احتياطية
php artisan backup:database

# تنظيف الملفات القديمة
php artisan cleanup:old-files

# إرسال التذكيرات
php artisan send:reminders

# تحديث حالات الحالات
php artisan update:case-status

# إنشاء التقارير
php artisan generate:reports
```

## استكشاف الأخطاء

### المشكلة: قاعدة البيانات غير موجودة
**الحل:**
```bash
php artisan migrate:fresh --seed
```

### المشكلة: مشاكل في الأذونات
**الحل:**
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

### المشكلة: الملفات المرفوعة لا تعمل
**الحل:**
```bash
php artisan storage:link
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
```

## الموارد الإضافية

- [وثائق Laravel](https://laravel.com/docs)
- [وثائق Vue.js](https://vuejs.org/guide/)
- [Spatie Permissions](https://spatie.be/docs/laravel-permission)
