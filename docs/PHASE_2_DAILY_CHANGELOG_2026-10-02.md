# تقرير تعديلات مشروع وصال — 2026-10-02

## معلومات التقرير

- **المشروع:** Wasal — وصال
- **النطاق:** منصة العقارات وإدارة الأملاك للسوق اليمني، مع التركيز على عدن
- **الفرع:** `phase-2-real-estate`
- **تاريخ التقرير:** 2026-10-02
- **آخر commit موثق في هذا التقرير:** `965e1b0`
- **المستودع:** `Alqasimx/wasal`

---

## 1. ملخص تنفيذي

خلال هذا اليوم تم استكمال مجموعة مهمة من أعمال المرحلة الثانية، مع التركيز على:

1. إصلاح توافق نماذج Filament مع الإصدار 5.
2. تحسين نموذج إضافة وتعديل العقار والقوائم الجغرافية.
3. معالجة رفع صور العقارات وتشخيص سبب فشل الرفع في Windows وHerd.
4. عرض صور العقارات في الجداول المرتبطة بها.
5. إضافة اقتراحات للرمز الداخلي مع الحفاظ على عدم التكرار.
6. تطوير إحصائيات لوحة التحكم.
7. تحسين حجم الجداول والصور ومساحات العرض.
8. إعادة ألوان Filament الأصلية بعد طلب المستخدم، مع الإبقاء على تحسينات الحجم والمسافات.
9. نشر جميع التعديلات على GitHub في فرع `phase-2-real-estate`.

> لم يتم تغيير ملفات Windows المحلية مباشرة من بيئة Manus. جميع تعديلات الكود تم تنفيذها داخل نسخة المستودع في بيئة التطوير ثم رفعها إلى GitHub، بينما كانت أوامر Windows المقدمة للمستخدم مخصصة لإعداد بيئته المحلية وتشغيل المشروع.

---

## 2. التسلسل الزمني للـ commits

| Commit | الوصف |
|---|---|
| `0b65643` | إصلاح فهرس MySQL الطويل في سجل حالات طلبات العقارات |
| `7a98f35` | إصلاح أنواع `Get` و`Set` في نماذج Filament 5 |
| `092ad95` | تحسين عرض صور العقارات ومعلومات الموقع في الجداول |
| `a2e2b4a` | إضافة اقتراحات الرموز الداخلية في نماذج العقارات المرتبطة |
| `776157c` | إضافة إعداد صريح لرفع Livewire المؤقت |
| `c699600` | إزالة الرفض الصارم لنوع MIME أثناء الرفع المؤقت |
| `5181c19` | تحديث لوحة التحكم وتصميم الجداول وتكبير الصور |
| `965e1b0` | إعادة ألوان Filament الأصلية مع الحفاظ على تحسينات الجداول |

---

## 3. إصلاح توافق Filament 5

### المشكلة

ظهر الخطأ التالي عند فتح صفحة إضافة العقار:

```text
Argument #1 ($get) must be of type Filament\\Forms\\Get,
Filament\\Schemas\\Components\\Utilities\\Get given
```

كان السبب أن نموذج العقار يستخدم مسارات الأدوات القديمة الخاصة بـ `Get` و`Set`.

### الإصلاح

تم تغيير الاستيراد في:

```text
app/Filament/Resources/Properties/Schemas/PropertyForm.php
```

من:

```php
use Filament\Forms\Get;
use Filament\Forms\Set;
```

إلى:

```php
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
```

### النتيجة

أصبحت القوائم المتسلسلة متوافقة مع Filament 5:

```text
المدينة ← المديرية ← الحي ← الشارع
```

---

## 4. إصلاح فهرس MySQL الطويل

### المشكلة

فشل migration الخاص بتاريخ حالات طلب العقار بسبب تجاوز MySQL الحد الأقصى لطول اسم الفهرس:

```text
property_request_status_history_property_request_id_created_at_index
```

### الإصلاح

تم استخدام اسم فهرس مختصر داخل migration، مع الحفاظ على الجدول الموجود وعدم حذف البيانات.

### تعليمات مهمة

تم التأكيد على عدم تنفيذ:

```powershell
php artisan migrate:fresh
```

لأن هذا الأمر يحذف جميع بيانات قاعدة البيانات.

---

## 5. الجغرافيا الخاصة بعدن

تم اعتماد `GeographySeeder` لإضافة بيانات اليمن وعدن، وتشمل:

### الدولة والمحافظة والمدينة

- اليمن
- محافظة عدن
- مدينة عدن

### المديريات المضافة

- البريقة
- دار سعد
- الشيخ عثمان
- المنصورة
- خور مكسر
- المعلا
- كريتر
- التواهي

### الشوارع المقترحة

من أمثلة الشوارع المضافة:

- الطريق الرئيسي للبريقة
- شارع صلاح الدين
- الطريق الرئيسي لدار سعد
- شارع دار سعد
- شارع الشيخ عثمان الرئيسي
- شارع عبدالقوي
- شارع التسعين
- شارع الخمسين
- شارع المنصورة الرئيسي
- شارع المطار
- شارع الجامعة
- شارع ساحل أبين
- شارع المعلا الرئيسي
- شارع مدرم
- شارع الميناء
- شارع الملكة أروى
- شارع أروى
- شارع الميدان
- شارع التواهي الرئيسي
- شارع الهلال

### تشغيل البيانات على الجهاز المحلي

```powershell
cd C:\Projects\wasal
php artisan db:seed --class=GeographySeeder
php artisan optimize:clear
```

يستخدم الـSeeder أسلوب `updateOrCreate`، لذلك يمكن تشغيله مرة أخرى دون إنشاء نسخ مكررة من البيانات.

---

## 6. نموذج العقار والقوائم الجغرافية

تم الحفاظ على القوائم المرتبطة التالية:

- نوع العقار.
- المدينة.
- المديرية التابعة للمدينة.
- الحي التابع للمديرية.
- الشارع التابع للمديرية.

عند تغيير المدينة يتم تصفير:

- المديرية.
- الحي.
- الشارع.

وعند تغيير المديرية يتم تصفير:

- الحي.
- الشارع.

هذا يمنع حفظ علاقة جغرافية غير صحيحة.

---

## 7. رفع صور العقارات

### إعداد حقل الصور

في الملف:

```text
app/Filament/Resources/Properties/Schemas/PropertyForm.php
```

تم إعداد حقل `gallery` ليتيح:

- رفع عدة صور.
- ترتيب الصور بالسحب.
- فتح الصورة.
- تنزيل الصورة.
- حد أقصى 20 صورة.
- حفظ الصور في مجلد `properties`.
- الصيغ المدعومة: JPG وPNG وWEBP.
- حد Filament للصورة الواحدة: 10 ميجابايت تقريبًا.

الإعداد الأساسي:

```php
FileUpload::make('gallery')
    ->disk('public')
    ->directory('properties')
    ->image()
    ->multiple()
    ->reorderable()
    ->appendFiles()
    ->openable()
    ->downloadable()
    ->maxFiles(20)
    ->maxSize(10240);
```

### التخزين

تم استخدام القرص العام:

```text
storage/app/public
```

ومجلد الصور:

```text
storage/app/public/properties
```

والرابط العام:

```text
public/storage
```

الأمر المطلوب مرة واحدة على الجهاز المحلي:

```powershell
php artisan storage:link
```

إذا ظهرت الرسالة:

```text
The [public\storage] link already exists.
```

فهذا يعني أن الرابط موجود مسبقًا وليس خطأ.

---

## 8. تشخيص مشكلة رفع الصور في Windows وHerd

### رسالة الخطأ الأصلية

ظهر في المتصفح:

```text
The data.gallery.<uuid> failed to upload.
```

وبعد فحص طلب الشبكة ظهر السبب الحقيقي:

```text
PHP Request Startup: File upload error - unable to create a temporary file
```

وكانت استجابة endpoint:

```text
Status: 422 Unprocessable Content
URL: /livewire-*/upload-file
```

### السبب

كان PHP يعمل بدون مجلد مؤقت صريح:

```text
sys_temp_dir => no value
upload_tmp_dir => no value
```

لذلك لم يتمكن PHP من إنشاء الملف المؤقت قبل وصوله إلى Livewire.

### الإصلاح المحلي

تم إنشاء مجلد خاص:

```text
C:\Users\ابو سلال\AppData\Local\Temp\php-upload
```

ثم تمت إضافة الإعدادات إلى ملف PHP المستخدم من Herd:

```text
C:\Users\ابو سلال\.config\herd\bin\php84\php.ini
```

الإعدادات:

```ini
upload_tmp_dir = "C:\Users\ابو سلال\AppData\Local\Temp\php-upload"
sys_temp_dir = "C:\Users\ابو سلال\AppData\Local\Temp\php-upload"
```

التحقق:

```powershell
php -i | findstr /I "upload_tmp_dir sys_temp_dir"
```

يجب ألا تظهر عبارة:

```text
no value
```

### النتيجة

بعد ضبط المجلد المؤقت، نجح رفع الصور فعليًا.

---

## 9. إعداد Livewire المؤقت

تم إنشاء الملف:

```text
config/livewire.php
```

ليحتوي على إعداد صريح للرفع المؤقت:

```php
return [
    'temporary_file_upload' => [
        'disk' => env('LIVEWIRE_TEMPORARY_FILE_UPLOAD_DISK', 'local'),
        'directory' => 'livewire-tmp',
        'rules' => 'file|max:10240',
        'middleware' => 'throttle:60,1',
    ],
];
```

### سبب استخدام `file|max`

تم ترك التحقق النهائي من كون الملف صورة لحقل Filament نفسه، بينما يكتفي Livewire في المرحلة المؤقتة بالتحقق من:

- أن الملف ملف حقيقي.
- ألا يتجاوز الحد المسموح.

هذا يمنع رفض بعض صور الهاتف بسبب اختلاف MIME الذي يرسله المتصفح، مع بقاء حقل Filament مقيدًا بالصور المطلوبة.

### مجلد Livewire المؤقت

```text
storage/app/private/livewire-tmp
```

يمكن إنشاؤه محليًا عبر:

```powershell
New-Item -ItemType Directory -Force storage\app\private\livewire-tmp
```

---

## 10. تحذير Herd المكرر

ظهر التحذير:

```text
Module "herd" is already loaded
```

وبالفحص ظهر تكرار سطر الإضافة داخل:

```text
php.ini
```

كان هناك سطران متطابقان:

```ini
extension = 'C:\Program Files\Herd\resources\app.asar.unpacked\resources\bin\phpherd\php_herd-8.4.dll'
extension = 'C:\Program Files\Herd\resources\app.asar.unpacked\resources\bin\phpherd\php_herd-8.4.dll'
```

تم الإبقاء على سطر واحد فعال وتعطيل الثاني بإضافة `;` في بدايته.

الصيغة الصحيحة:

```ini
[Herd]
; Enable MongoDB
extension="C:\Program Files\Herd\resources\app.asar.unpacked\resources\bin\phpmongodb\php_mongodb-8.4.dll"

extension = 'C:\Program Files\Herd\resources\app.asar.unpacked\resources\bin\phpherd\php_herd-8.4.dll'

upload_tmp_dir = "C:\Users\ابو سلال\AppData\Local\Temp\php-upload"
sys_temp_dir = "C:\Users\ابو سلال\AppData\Local\Temp\php-upload"
```

التحقق:

```powershell
php -v
```

ويُفترض ألا يظهر التحذير بعد إزالة التكرار.

---

## 11. اقتراحات الرمز الداخلي

### نموذج العقار

تم تحسين حقل `internal_code` ليعرض قائمة اقتراحات، مع السماح بكتابة رمز مخصص.

أمثلة الاقتراحات:

```text
ADEN-001
ADEN-002
PROP-001
VILLA-001
APT-001
SHOP-001
OFFICE-001
LAND-001
```

### قواعد الرمز

- الاقتراحات لا تعرض الرموز المستخدمة مسبقًا.
- يمكن للمستخدم اختيار رمز من القائمة.
- يمكن إدخال رمز يدوي.
- ما زال شرط عدم التكرار فعالًا.
- يتم تحديث الاقتراحات من قاعدة البيانات.

### الصفحات المرتبطة

تم تحسين اختيار العقار في:

- إضافة الإعلان.
- إضافة مالك العقار.
- إضافة وحدة العقار.

ويظهر الخيار بالشكل:

```text
ADEN-001 — اسم العقار
```

كما أصبح البحث ممكنًا بواسطة:

- الرمز الداخلي.
- اسم العقار.

---

## 12. عرض صور العقارات في الجداول

تمت إضافة صور العقار إلى الجداول التالية:

### جدول العقارات

المسار:

```text
/admin/properties
```

ويعرض:

- صورة العقار.
- الرمز الداخلي.
- اسم العقار.
- النوع.
- المدينة.
- المديرية.
- الحي.
- الشارع.
- المساحة.
- عدد الوحدات.
- الحالة.

### جدول الإعلانات

المسار:

```text
/admin/property-listings
```

ويعرض صورة العقار المرتبط بالإعلان، إضافة إلى:

- رقم الإعلان.
- عنوان العرض.
- الرمز الداخلي للعقار.
- المدينة.
- المديرية.
- الغرض.
- السعر.
- دورية السعر.
- حالة الإعلان.
- حالة المشاركة.
- عدد المشاركات.
- رابط المشاركة.

### جدول ملاك العقارات

المسار:

```text
/admin/property-owners
```

تمت إضافة صورة العقار المرتبط بالمالك.

### جدول وحدات العقارات

المسار:

```text
/admin/property-units
```

تمت إضافة صورة العقار المرتبط بالوحدة.

### أبعاد الصور

تم رفع حجم الصورة في الجداول إلى ما يقارب:

```text
148 × 104 بكسل
```

مع حجم متجاوب أصغر على الشاشات الضيقة.

---

## 13. تحسين الجداول

تم تطبيق تحسينات عامة على الجداول:

- زيادة ارتفاع الصفوف.
- تحسين المسافات الداخلية.
- عرض كامل للمحتوى المتاح.
- تفعيل نمط الصفوف المتناوبة في الجداول العقارية الرئيسية.
- جعل الجداول أسهل للقراءة عند وجود معلومات كثيرة.
- الحفاظ على التمرير الأفقي عند الشاشات الصغيرة بدل ضغط البيانات.
- عرض 10 سجلات افتراضيًا في الجداول الرئيسية التي تم تعديلها.

> بناءً على طلب المستخدم، تم التراجع عن تغيير الألوان. لذلك بقيت ألوان Filament الأصلية، وتم الاحتفاظ فقط بتحسينات الحجم والمسافات والصور.

---

## 14. تحسين لوحة التحكم

تمت إضافة إحصائيات تشغيلية مرتبطة بصلاحيات المستخدم:

- عدد العقارات.
- عدد الإعلانات المنشورة.
- عدد طلبات العقارات غير المكتملة.
- عدد المهام المفتوحة.
- المستخدمون.
- الأدوار.
- الحسابات البنكية.
- العملات.
- سجل التدقيق.

كل بطاقة ترتبط بالصفحة المناسبة عند الضغط عليها، ولا تظهر إذا لم يملك المستخدم الصلاحية اللازمة.

الألوان الأصلية للوحة Filament بقيت كما هي بعد آخر إصلاح.

---

## 15. الملفات التي تم تعديلها أو إنشاؤها

### ملفات النماذج

```text
app/Filament/Resources/Properties/Schemas/PropertyForm.php
app/Filament/Resources/PropertyListings/Schemas/PropertyListingForm.php
app/Filament/Resources/PropertyOwners/Schemas/PropertyOwnerForm.php
app/Filament/Resources/PropertyUnits/Schemas/PropertyUnitForm.php
```

### ملفات الجداول

```text
app/Filament/Resources/Properties/Tables/PropertiesTable.php
app/Filament/Resources/PropertyListings/Tables/PropertyListingsTable.php
app/Filament/Resources/PropertyOwners/Tables/PropertyOwnersTable.php
app/Filament/Resources/PropertyUnits/Tables/PropertyUnitsTable.php
```

### لوحة التحكم

```text
app/Filament/Widgets/WasalStatsOverview.php
app/Providers/Filament/AdminPanelProvider.php
```

### إعدادات الرفع

```text
config/livewire.php
config/filesystems.php
```

### التصميم

```text
resources/views/filament/admin/styles.blade.php
```

### الجغرافيا

```text
database/seeders/GeographySeeder.php
```

---

## 16. أوامر التحديث على جهاز Windows

بعد أي تحديث جديد على الفرع:

```powershell
cd C:\Projects\wasal
git pull --ff-only origin phase-2-real-estate
php artisan optimize:clear
php artisan view:clear
```

تشغيل الخادم:

```powershell
php -S 127.0.0.1:8000 -t public
```

أو:

```powershell
php artisan serve --host=127.0.0.1 --port=8000
```

> يجب إبقاء نافذة الخادم مفتوحة أثناء استخدام لوحة الإدارة.

فتح لوحة الإدارة:

```text
http://127.0.0.1:8000/admin
```

تحديث إجباري للمتصفح:

```text
Ctrl + F5
```

---

## 17. التحقق والاختبارات المنفذة

تم تنفيذ فحص PHP syntax للملفات المعدلة، وكانت النتيجة:

```text
No syntax errors detected
```

كما تم تنفيذ:

```text
git diff --check
```

بدون أخطاء تنسيق.

تم رفع التعديلات بنجاح إلى:

```text
origin/phase-2-real-estate
```

### ملاحظة عن تشغيل الاختبارات

تشغيل كامل Laravel يعتمد على بيئة Windows المحلية التي تحتوي على PHP 8.4 وامتدادات Herd. بيئة Sandbox الحالية تستخدم PHP 8.3 ولا تحتوي على `ext-intl`، لذلك لا تمثل بيئة تشغيل المشروع المحلية بشكل كامل.

الاختبار الموصى به على جهاز Windows:

```powershell
cd C:\Projects\wasal
php artisan test
```

---

## 18. الحالة الحالية

الحالة الحالية للمشروع:

- فرع العمل: `phase-2-real-estate`.
- رفع الصور يعمل بعد ضبط مجلد PHP المؤقت.
- صور العقارات تظهر في الجداول المرتبطة.
- ألوان Filament الأصلية محفوظة.
- لوحة التحكم تعرض الإحصائيات العقارية والتشغيلية.
- اقتراحات الرموز الداخلية متاحة.
- بيانات عدن الجغرافية متاحة بعد تشغيل `GeographySeeder`.
- التعديلات منشورة على GitHub.

---

## 19. الخطوات المقترحة التالية

بعد مراجعة الواجهة، الخطوة التالية المناسبة هي استكمال واجهات الإدارة التشغيلية:

1. واجهة المستأجرين.
2. واجهة عقود الإيجار.
3. واجهة الدفعات المستحقة.
4. تسجيل الدفع اليدوي.
5. فلترة المتأخرات والدفعات القادمة.
6. خدمة تنظيف الألواح الشمسية.
7. الجدولة الفردية والمتكررة.
8. إشعار الإدارة قبل الموعد بـ24 ساعة.
9. تحسين صفحة المهام اليومية والأسبوعية.
10. إضافة اختبارات خاصة بالإيجارات والطلبات والخدمات.

---

## 20. الخلاصة

تم خلال هذا اليوم الانتقال من مجرد نماذج عقارية أساسية إلى واجهة إدارة أكثر عملية، مع دعم فعلي للصور والجغرافيا والرموز الداخلية والجداول التشغيلية ولوحة مؤشرات الإدارة.

أهم مشكلة تشغيلية ظهرت — وهي فشل رفع الصور — تم تشخيصها من خلال طلب الشبكة، واتضح أنها مشكلة PHP في إنشاء الملف المؤقت على Windows، ثم تم حلها بتحديد:

```ini
upload_tmp_dir
sys_temp_dir
```

كما تم احترام طلب المستخدم بإعادة ألوان Filament الأصلية، مع الإبقاء على تكبير الصور وتحسين قابلية قراءة الجداول.
