<div dir="rtl">

# موقع تقييم الأفلام (Movie Rating Website)

تطبيق ويب مبني بـ **Laravel 13** لإدارة الأفلام والمسلسلات. يحتوي حالياً على لوحة تحكم للمشرف (Admin) تتيح إضافة الأفلام والمسلسلات وتعديلها وحذفها، مع المخرجين والتصنيفات وصور البوستر.

## المميزات

- تسجيل دخول خاص بالمشرف باستخدام guard منفصل (`admin`) مع خيار "تذكرني".
- لوحة تحكم (Dashboard) تعرض إحصائيات المحتوى.
- إدارة **الأفلام**: العنوان، المخرج، سنة الإصدار، الوصف، التصنيفات، صورة البوستر.
- إدارة **المسلسلات**: العنوان، المخرج، سنة البداية والنهاية (فارغة = ما زال يُعرض)، عدد المواسم والحلقات، الوصف، التصنيفات، البوستر.
- اختيار مخرج موجود أو كتابة اسم مخرج جديد يُضاف تلقائياً.
- اختيار تصنيفات موجودة أو إضافة تصنيفات جديدة من نفس النموذج.
- رفع صور البوستر (jpg, jpeg, png, webp حتى 10MB) وحذف الصورة القديمة تلقائياً عند التعديل أو الحذف.
- بيانات تجريبية (Seeders) للمخرجين والتصنيفات والأفلام والمسلسلات.

## التقنيات المستخدمة

| الجزء | التقنية |
|---|---|
| Backend | PHP 8.3+، Laravel 13 |
| قاعدة البيانات | MySQL |
| Frontend | Blade Components، Tailwind CSS 4، Vite |
| الاختبارات | Pest |

## المتطلبات

- PHP 8.3 أو أحدث
- Composer
- Node.js و npm
- MySQL

## التثبيت والتشغيل

</div>

```bash
# 1. استنساخ المشروع
git clone https://github.com/mohamedelsherife/Movie-rating-website.git
cd Movie-rating-website

# 2. تثبيت الحزم
composer install
npm install

# 3. إعداد ملف البيئة
cp .env.example .env
php artisan key:generate
```

<div dir="rtl">

4. أنشئ قاعدة بيانات باسم `movie_rating_website` في MySQL، ثم عدّل بيانات الاتصال في ملف `.env` إذا لزم الأمر:

</div>

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=movie_rating_website
DB_USERNAME=root
DB_PASSWORD=
```

```bash
# 5. إنشاء الجداول وإضافة البيانات التجريبية
php artisan migrate --seed

# 6. ربط مجلد التخزين لعرض صور البوستر
php artisan storage:link

# 7. تشغيل المشروع (السيرفر + Vite + الـ queue معاً)
composer run dev
```

<div dir="rtl">

بعد التشغيل افتح المتصفح على: `http://localhost:8000`

## حساب المشرف

يتم إنشاء حساب المشرف الافتراضي تلقائياً عند تشغيل الـ seeders:

| الحقل | القيمة |
|---|---|
| البريد / اسم المستخدم | `admin` |
| كلمة المرور | `admin` |

> ⚠️ يُنصح بتغيير هذه البيانات. أضف القيم التالية إلى ملف `.env` ثم شغّل الأمر بعدها:

</div>

```env
ADMIN_NAME=Administrator
ADMIN_EMAIL=admin@example.com
ADMIN_PASSWORD=your-strong-password
```

```bash
php artisan db:seed --class=AdminSeeder
```

<div dir="rtl">

## المسارات (Routes)

| الطريقة | المسار | الوصف |
|---|---|---|
| GET | `/` | تحويل إلى `/admin` |
| GET / POST | `/admin/login` | تسجيل دخول المشرف |
| POST | `/admin/logout` | تسجيل الخروج |
| GET | `/admin` | لوحة التحكم |
| GET, POST, PUT, DELETE | `/admin/movies` | إدارة الأفلام |
| GET, POST, PUT, DELETE | `/admin/series` | إدارة المسلسلات |

جميع مسارات `/admin` (عدا صفحة الدخول) تتطلب تسجيل دخول المشرف.

## هيكل قاعدة البيانات

| الجدول | الوصف |
|---|---|
| `admins` | حسابات المشرفين |
| `directors` | المخرجون (اسم فريد) |
| `genres` | التصنيفات (اسم فريد) |
| `movies` | الأفلام (مرتبطة بمخرج) |
| `series` | المسلسلات (مرتبطة بمخرج) |
| `movie_genres` | ربط الأفلام بالتصنيفات (many-to-many) |
| `series_genres` | ربط المسلسلات بالتصنيفات (many-to-many) |

## هيكل المشروع

</div>

```
app/
├── Http/Controllers/
│   ├── AdminController.php    # الدخول والخروج ولوحة التحكم
│   ├── MovieController.php    # CRUD للأفلام
│   └── SeriesController.php   # CRUD للمسلسلات
└── Models/                    # Admin, Director, Genre, Movie, Series, User
database/
├── migrations/
└── seeders/                   # بيانات تجريبية + حساب المشرف
resources/views/
├── layouts/app.blade.php      # القالب الرئيسي (navbar + footer)
├── admin/                     # صفحات لوحة التحكم
└── components/                # مكونات Blade قابلة لإعادة الاستخدام
routes/web.php
```

<div dir="rtl">

## أوامر مفيدة

</div>

```bash
composer run dev          # تشغيل بيئة التطوير
composer run test         # تشغيل الاختبارات
npm run build             # بناء ملفات الواجهة للإنتاج
php artisan migrate:fresh --seed   # إعادة بناء قاعدة البيانات من الصفر
./vendor/bin/pint         # تنسيق الكود
```

<div dir="rtl">

## الترخيص

هذا المشروع مرخّص تحت رخصة [MIT](https://opensource.org/licenses/MIT).

</div>
