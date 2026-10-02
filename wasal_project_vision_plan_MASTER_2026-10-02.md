# وصال — المرجع الرئيسي الشامل لفكرة المشروع وخطة التطوير

**الإصدار:** MASTER 2026-10-02  
**الغرض:** هذا الملف هو المرجع الاستراتيجي والوظيفي الكامل لمشروع «وصال». صُمم ليُنقل مع المشروع إلى أي محادثة جديدة بحيث تبقى فكرة المنتج، قواعد العمل، المعمارية، الصلاحيات، تجربة الاستخدام، تصميم قاعدة البيانات المستهدف، مراحل التنفيذ، وتعليمات التطوير محفوظة في ملف واحد.

> **نطاق هذا الملف:** الفكرة والخطة وقواعد المنتج.  
> **لا يتضمن:** أي خطة لدمج مشروع آخر مع وصال.  
> **ملف البنية الحالية منفصل:** `wasal_current_structure_state_MASTER_2026-10-02.md`.

## مصادر هذا المرجع

تم بناء هذا الملف من وثائق المشروع الأصلية المرفوعة:
- `PROJECT_SPEC.md`
- `ARCHITECTURE.md`
- `BUSINESS_RULES.md`
- `PERMISSIONS.md`
- `UI_GUIDE.md`
- `DATABASE.md`
- `CODEX_INSTRUCTIONS.md`
- `PHASE_01_CODEX_PROMPT.md`

**SHA-256 لحزمة الوثائق المصدرية:**  
`ba156aeaa74f241a9a3d26d18781663cf6f1eaa365a59e29a3ac21eb35f36b81`

---

# 1. الملخص التنفيذي

«وصال» منصة ويب عربية/إنجليزية للخدمات العقارية والحجوزات وإدارة الأملاك والخدمات المرتبطة بها. النسخة الأولى مصممة كموقع ويب Responsive يعمل جيدًا على الهاتف، مع REST API منظم منذ البداية لتطبيق Flutter مستقبلي.

النظام ليس مجرد لوحة إدارة؛ بل **Modular Monolith** يحتوي على وحدات تشغيلية مستقلة نسبيًا تشترك في هوية ومستخدمين وصلاحيات ومرجع جغرافي وعملات ومدفوعات وAudit.

الأقسام الأربعة الكبرى في V1:
1. **العقارات:** بيع، إيجار، بحث، فلترة، مفضلة، طلب عقار، إضافة إعلان، مراجعة واعتماد.
2. **الحجوزات:** فنادق، شاليهات، استراحات/طيرمانات، قاعات، غرف/باقات، التوفر، العربون، التقييمات.
3. **إدارة الأملاك:** العقارات المُدارة، الوحدات، المستأجرون، العقود، الدفعات، المصروفات، الصيانة، كشوف الملاك.
4. **الخدمات:** خدمات ديناميكية من لوحة الإدارة، مجانية أو مدفوعة، وقد تتضمن طلبًا أو موعدًا وسعرًا حسب نوع الخدمة.

وصال يعمل كوسيط تشغيلي:
- لا يظهر رقم المالك أو وسيلة الاتصال المباشرة للعميل.
- الموقع الدقيق لا يظهر للعامة.
- الإعلان أو التعديل الحساس يمر بمراجعة وصال قبل النشر.
- العمليات المالية لا تصبح موثقة/Verified إلا بعد اعتماد موظف مخول.
- العمليات الحساسة والمالية والقانونية يجب أن تُسجل في Audit Log.

---

# 2. القرارات المعمارية الثابتة

- Backend: Laravel.
- Frontend: Blade + Livewire + Alpine.js.
- CSS: Tailwind CSS.
- Admin: Filament.
- Database: MySQL.
- Architecture: Modular Monolith.
- API: REST تحت `/api/v1`.
- Queue: Database Queue عند الحاجة.
- Cache: Database/File Cache.
- Scheduler: Laravel Scheduler + Cron مناسب للاستضافة.
- Storage: Laravel Filesystem abstraction، Local أولًا وقابل لـ S3-compatible مستقبلًا.
- Mobile: Flutter مستقبلًا فوق نفس REST API.
- لا Redis كمتطلب أساسي.
- لا Horizon كمتطلب أساسي.
- لا Octane كمتطلب أساسي.
- لا WebSocket server دائم كمتطلب.
- لا Supervisor كمتطلب تشغيلي أساسي.
- لا Elasticsearch.
- الملفات لا تُحفظ BLOB داخل MySQL؛ يحفظ المسار والـmetadata فقط.
- منطق الأعمال يوضع في Services/Actions/Policies/Domain وليس داخل Blade أو Livewire مباشرة.

---

# 3. الحساب الموحد والمستخدمون

الحساب موحد؛ نفس الشخص يمكن أن يكون:
- عميلًا.
- مالكًا.
- مستأجرًا.
- مقدم منشأة حجز.
- أو موظفًا بحسب Role الممنوح له.

الوسيط العقاري يعامل كمستخدم عادي، ولا يحصل على دور تشغيلي خاص افتراضيًا.

التسجيل والدخول:
- رقم اتصال.
- رقم WhatsApp.
- اختيار OTP عبر WhatsApp أو Email.
- إذا اختير Email ولم يكن موجودًا، يُطلب من المستخدم.
- كلمة مرور موجودة أيضًا.
- العربية والإنجليزية مدعومتان.
- REST API يدعم تطبيق موبايل لاحقًا.

---

# 4. قواعد الخصوصية والأمان

- لا تعرض وسائل الاتصال المباشرة للمالك للعميل.
- لا تعرض الموقع الدقيق للعامة.
- البيانات الحساسة لا تظهر إلا حسب الصلاحية.
- كل عملية إدارية حساسة تسجل في Audit Log.
- الدفع لا يصبح Verified قبل اعتماد موظف مخول.
- العمليات المالية والقانونية لا تستخدم Hard Delete.
- العقود تحتاج Versioning.
- لا يسمح بإشغالين متداخلين لنفس الوحدة.
- Access Tokens وكلمات المرور لا توثق في الملفات العامة.
- `.env` لا يرفع إلى Git.

---

# 5. الأدوار الوظيفية المستهدفة

- user
- customer_service
- property_reviewer
- property_management
- accountant
- financial_approver
- system_admin

وتفاصيل صلاحيات كل فئة موثقة بالكامل في قسم «الوثائق المصدرية الكاملة» أدناه.

---

# 6. العقارات

الخصائص الرئيسية:
- أنواع عقارات ديناميكية.
- بيع/إيجار.
- خصائص ومزايا ديناميكية.
- إعلان يمر بالمراجعة.
- النسخة المنشورة تبقى ظاهرة حتى اعتماد التعديل الجديد.
- المالك أو موظف وصال يستطيع الإضافة.
- المستخدم العادي يستطيع الإضافة ضمن المسار العام ثم تخضع للمراجعة.
- البحث المتقدم.
- الفلاتر الديناميكية.
- المفضلة.
- المقارنة.
- البحث المحفوظ والتنبيهات.
- تجهيز الموقع الجغرافي والخريطة للاستخدام لاحقًا.
- طلب عقار بمواصفات تفصيلية.

---

# 7. الحجوزات

الفئات:
- فنادق.
- شاليهات.
- استراحات/طيرمانات.
- قاعات.
- فئات أخرى قابلة للإضافة.

مسار الحجز:
1. العميل يختار المنشأة والتفاصيل والتاريخ/المدة والغرفة/الباقة وعدد الأشخاص.
2. يرسل طلبًا.
3. فريق وصال يتحقق من التوفر مع المنشأة.
4. عند التوفر، يُطلب العربون.
5. المهلة الافتراضية للدفع: ساعتان.
6. العميل يُدخل بيانات الإيداع.
7. فريق وصال يراجع العملية.
8. يتم تثبيت الحجز مع المنشأة.
9. يصبح الحجز مؤكدًا.

العربون:
- افتراضي 5%.
- قابل للتعديل حسب المكان/الباقة.
- غير مسترد افتراضيًا.
- استرداد استثنائي ممكن مع السبب وAudit.
- إذا تعذر الحجز بعد الدفع، يعرض بديل أولًا ثم رد كامل المبلغ إن رفض العميل.

الحالات المقترحة:
```text
NEW_REQUEST
CHECKING_AVAILABILITY
AVAILABLE_AWAITING_DEPOSIT
PAYMENT_SUBMITTED
PAYMENT_UNDER_REVIEW
PAYMENT_VERIFIED
CONFIRMING_WITH_PROPERTY
CONFIRMED
CANCELLED
REJECTED
EXPIRED
COMPLETED
```

---

# 8. إدارة الأملاك

تشمل:
- العقارات المُدارة.
- الوحدات.
- المستأجرون.
- العقود.
- الإشغال.
- دفعات الإيجار.
- المصروفات.
- الصيانة.
- المستندات.
- التقارير التشغيلية.
- كشوف الملاك.

قواعد:
- لا Hard Delete للعمليات القانونية أو المالية.
- العقود Versioned.
- لا إشغالين فعالين متداخلين لنفس الوحدة.
- الدفعات والمصروفات والصيانة مرتبطة بالعقار/الوحدة/العقد عند الحاجة.
- كشف المالك يراجع ويعتمد قبل الإرسال.

---

# 9. الخدمات

الخدمة:
- قد تكون مجانية أو مدفوعة.
- قد تكون طلبًا فقط.
- أو طلب + موعد.
- أو طلب + موعد + سعر.
- الحقول يمكن أن تكون ديناميكية حسب نوع الخدمة.

---

# 10. الهوية والواجهة

الهوية:
- أسود فاخر.
- ذهبي.
- بيج/عاجي دافئ.
- أبيض.
- رمادي مساعد.

الواجهة:
- RTL للعربية.
- LTR للإنجليزية.
- Responsive mobile-first.
- بطاقات كبيرة وواضحة.
- حواف دائرية.
- ظلال خفيفة.
- مساحات بيضاء جيدة.
- لمسات ذهبية محدودة.
- Sidebar إداري داكن والعنصر النشط ذهبي.

على الهاتف في إدارة الأملاك:
- الرئيسية.
- العقارات.
- الدفعات.
- العقود.
- المزيد.

على الكمبيوتر:
- Sidebar.
- الرئيسية.
- العقارات.
- الوحدات.
- المستأجرون.
- العقود.
- الدفعات.
- الصيانة.
- التقارير.
- المستندات.

---

# 11. مراحل التطوير الرسمية

## Phase 1 — Foundation
- Laravel foundation.
- Authentication.
- Users.
- Roles/Permissions.
- Admin shell.
- Audit.
- Geography.
- Currencies.
- Banks.
- Settings.
- API foundation.
- Tests.

## Phase 2 — Real Estate
- Property types/features.
- Listings.
- Review workflow.
- Favorites.
- Saved searches.
- Property requests.

## Phase 3 — Bookings & Hospitality
- Establishments.
- Rooms/Units.
- Packages.
- Availability.
- Booking workflow.
- Deposits.
- Reviews.

## Phase 4 — Property Management
- Units.
- Tenants.
- Contracts.
- Payments.
- Expenses.
- Maintenance.
- Owner statements.

## Phase 5 — Services / Promotions / Notifications / Reports / Hardening
- Services.
- Promotions.
- Notifications.
- Reports.
- QA.
- Performance.
- Security hardening.

---

# 12. Definition of Done العام

لا تعتبر مرحلة مكتملة إلا عند:
- نجاح migrations.
- نجاح automated tests.
- وجود authorization tests.
- وجود validation.
- UI responsive.
- عدم وجود N+1 واضح.
- Audit coverage للحالات الحساسة.
- توثيق أي قرار جديد.
- التأكد من صلاحيات المستخدمين.
- التوافق مع قيود الاستضافة.

---

# 13. قاعدة الأولوية عند التعارض

ترتيب الأولوية:
1. الملفات الحالية الفعلية في Repository.
2. أحدث تقرير/لقطة تقنية موثقة.
3. وثائق الخطة في هذا الملف.
4. الافتراضات.

لا يتم تخمين schema أو relationship إذا أمكن الرجوع للملف الحالي.

---

# 14. الوثائق المصدرية الكاملة

الأقسام التالية تُدرج الوثائق الأصلية كاملة كما تم رفعها، حتى لا تضيع أي قاعدة أو حقل أو قرار في النقل بين المحادثات.


---

# ملحق مصدر: `PROJECT_SPEC.md`

# وصال V1 — مواصفات المنتج

## 1. تعريف المنتج
وصال منصة ويب عربية/إنجليزية للخدمات العقارية والحجوزات وإدارة الأملاك والخدمات المرتبطة بها. النسخة الأولى موقع ويب احترافي Responsive ومهيأ للعمل جيدًا على الهاتف، مع REST API منظم لتطبيق Flutter مستقبلي.

## 2. النطاق الأساسي
يتكون V1 من أربعة أقسام رئيسية:
- العقارات: بيع، إيجار، بحث، فلترة، مفضلة، طلب عقار، إضافة إعلان، مراجعة واعتماد.
- الحجوزات: فنادق، شاليهات، استراحات/طيرمانات، قاعات، غرف/باقات، تقويم توفر، طلب حجز، عربون، تقييمات.
- إدارة الأملاك: عقارات مُدارة، وحدات، مستأجرون، عقود، دفعات، مصروفات، صيانة، تقارير وكشوف مالك.
- الخدمات: خدمات قابلة للإضافة من لوحة الإدارة، مجانية أو مدفوعة، مع طلبات ومواعيد حسب نوع الخدمة.

## 3. نموذج التشغيل
وصال وسيط تشغيلي. بيانات المالك ووسائل الاتصال المباشرة لا تظهر للعميل. الموقع الدقيق لا يظهر للعامة. الإعلانات والتعديلات الحساسة تمر بمراجعة وصال قبل النشر.

## 4. المستخدمون
الحساب موحد ويمكن أن يكون المستخدم عميلًا ومالكًا ومستأجرًا في الوقت نفسه. الوسيط العقاري يعامل كمستخدم عادي ولا يملك صلاحيات إدارية خاصة.

## 5. التسجيل والدخول
يطلب النظام رقم الاتصال ورقم WhatsApp. يختار المستخدم استلام رمز التحقق عبر WhatsApp أو البريد الإلكتروني، وإذا اختار البريد يُطلب منه إدخاله. يوجد أيضًا كلمة مرور للحساب. يدعم الحساب العربية والإنجليزية.

## 6. العقارات
الأنواع والغرض والفلاتر والمزايا قابلة للإدارة من لوحة التحكم. الإعلان الجديد أو التعديل يذهب للمراجعة. النسخة المنشورة تبقى ظاهرة حتى اعتماد التعديل الجديد.
يمكن للمالك أو موظف وصال إضافة العقار. المستخدم العادي يستطيع إضافة إعلان ضمن المسار المسموح ثم يخضع للمراجعة.

## 7. البحث العقاري
يدعم عرض القائمة أولًا، مع تجهيز الخريطة وإحداثيات الموقع للاستخدام لاحقًا أو كميزة اختيارية. يدعم البحث المتقدم والفلاتر الديناميكية حسب نوع العقار والمفضلة والمقارنة وحفظ البحث والتنبيهات.

## 8. طلب عقار
يمكن للمستخدم إنشاء طلب عقار بمواصفات تفصيلية: نوع، بيع/إيجار، ميزانية، غرف، صالات، حمامات، تأثيث، خصائص إضافية، وموقع مرغوب تقريبي. يمكنه طلب مساعدة فريق وصال في البحث الميداني.

## 9. الحجوزات
قسم مستقل عن البيع والإيجار. الفئات تشمل فنادق، شاليهات، استراحات/طيرمانات، قاعات، ويمكن إضافة فئات أخرى.
الفندق ككيان رئيسي يحتوي أنواع غرف/غرف. بقية المنشآت يمكن أن تستخدم وحدات أو باقات حسب طبيعتها.

## 10. مسار الحجز
المستخدم يختار المنشأة والتفاصيل والمدة/التاريخ والغرفة أو الباقة وعدد الأشخاص ثم يرسل طلب حجز.
فريق وصال يتواصل مع المنشأة للتحقق من التوفر.
عند التوفر يُطلب من العميل تأكيد الحجز بإيداع العربون.
المهلة الافتراضية للدفع ساعتان وقابلة للتعديل من الإدارة.
بعد إدخال بيانات الإيداع يراجع فريق وصال العملية، ثم يتواصل مع المنشأة لتثبيت الحجز، وبعدها يصبح الحجز مؤكدًا.

## 11. العربون والدفع
العربون الافتراضي 5% وقابل للتغيير لكل مكان.
العربون غير مسترد افتراضيًا، مع إمكانية استرجاعه كليًا أو جزئيًا من الإدارة في حالات خاصة مع تسجيل السبب.
الإيداع يتم إلى أحد الحسابات البنكية التابعة لوصال. العميل يختار الحساب ويُدخل رقم الإيداع والبنك والمبلغ والوقت، وصورة الإيصال اختيارية.
لا يصبح الدفع مؤكدًا إلا بعد مراجعة موظف مخول.
إذا تعذر الحجز بعد الدفع، يعرض بديل أولًا، وإذا رفضه العميل يعاد كامل المبلغ.

## 12. عمولة وصال للحجوزات
تأخذ وصال نسبة من قيمة الحجز. قيمة العمولة مرئية لوصال والمنشأة، ولا يلزم عرضها للعميل.

## 13. الباقات والتوفر
المنشأة يمكن أن تنشئ باقات مختلفة بالسعر والمدة والسعة وأوقات الدخول والخروج. الأسعار يمكن أن تختلف حسب الأيام والمواسم. يمكن إغلاق أيام محددة.
يوجد تقويم يوضح: متاح، محجوز، مغلق، طلب مؤقت.

## 14. التقييمات
التقييم متاح فقط بعد حجز مكتمل. التقييم نجوم + تعليق. الإدارة تستطيع إخفاء التقييم المخالف.

## 15. إدارة الأملاك
لوحة المالك تشمل الرئيسية، العقارات، الدفعات، العقود، المزيد.
تشمل المؤشرات: عدد العقارات، المؤجر، نسبة الإشغال، المحصل، المصروفات، صافي الدخل، الدفعات القادمة والمتأخرة.
يشمل القسم: العقارات، الوحدات، المستأجرين، العقود، الدفعات، المصروفات، الصيانة، التقارير، المستندات، كشوف الملاك.

## 16. إدارة الأملاك في V1
تبدأ بخدمة "طلب إدارة عقار" وتتيح تشغيل الإدارة الأساسية من قبل موظفي وصال. لا يُعطى المالك صلاحيات تشغيل حساسة كاملة؛ يشاهد ويتابع ويقدم الطلبات بينما ينفذ موظفو وصال الإجراءات الفعلية.

## 17. الخدمات
الخدمات ديناميكية من لوحة الإدارة، ويمكن أن تكون مجانية أو مدفوعة. يمكن لنوع الخدمة أن يستخدم نموذج طلب فقط أو طلب + موعد وسعر.
الدفع للخدمات المدفوعة يستخدم نفس نظام الإيداع البنكي.

## 18. الحساب
يشمل الملف الشخصي، المدينة، العملة، اللغة، المظهر، المفضلة، الإعدادات، الخصوصية، الدعم، طلبات العقار، الحجوزات، الإعلانات، المدفوعات، خدمات المستخدم.

## 19. العملات والمدن
النظام يدعم العربية والإنجليزية من البداية. المدن والعملات تُدار من لوحة الإدارة. يمكن تفعيل مدن محددة دون تعديل الكود.

## 20. الاستضافة
Hostinger Premium. يجب أن يعمل المشروع بدون متطلبات VPS دائمة، وبدون اعتماد إلزامي على Redis أو خدمات تعمل 24/7.


---

# ملحق مصدر: `ARCHITECTURE.md`

# وصال V1 — المعمارية التقنية

## Stack
- Backend: Laravel
- Frontend: Blade + Livewire + Alpine.js
- CSS: Tailwind CSS
- Admin: Filament
- Database: MySQL
- Architecture: Modular Monolith
- API: REST /api/v1
- Queue: Database Queue
- Cache: Database/File Cache
- Scheduler: Laravel Scheduler + Hostinger Cron
- Storage: Local storage initially with abstraction for future S3-compatible storage
- Mobile: Flutter مستقبلًا عبر نفس REST API

## Modules
- Identity & Access
- Users & Profiles
- Real Estate
- Property Requests / CRM
- Bookings & Hospitality
- Property Management
- Contracts
- Payments & Deposits
- Services
- Notifications
- Files & Media
- Reviews
- Promotions / Featured Listings
- Reports
- Audit
- Administration

## Hostinger constraints
لا تعتمد النسخة الأولى على:
- Redis كمتطلب أساسي
- Horizon
- Octane
- WebSocket server دائم
- Supervisor كمتطلب تشغيلي
- Elasticsearch
- Microservices

## API design
كل منطق الأعمال يوضع داخل Services/Actions/Policies وليس داخل Views.
الواجهات العامة ولوحة الإدارة وتطبيق Flutter المستقبلي تستخدم نفس طبقة Domain/Services.

## Storage
الملفات لا تخزن داخل MySQL كـ BLOB.
MySQL يحفظ metadata والمسارات فقط.
يجب استخدام Laravel Filesystem abstraction من البداية.

## Jobs
المهام مثل انتهاء مهلة الدفع، التذكيرات، الإشعارات، والنسخ الاحتياطي تعمل عبر Scheduler/Cron وقوائم database عند الحاجة.


---

# ملحق مصدر: `BUSINESS_RULES.md`

# وصال V1 — قواعد العمل

## الخصوصية
- لا يظهر رقم المالك أو وسائل اتصاله المباشرة للعميل.
- الموقع الدقيق لا يظهر للعامة.
- البيانات الحساسة لا تعرض إلا حسب الصلاحية.
- كل عملية إدارية حساسة يجب أن تسجل في Audit Log.

## العقارات
- كل إعلان جديد أو تعديل يحتاج مراجعة وصال قبل النشر.
- النسخة المنشورة لا تستبدل حتى اعتماد النسخة الجديدة.
- المالك يمكنه طلب تغيير حالة العقار، ووصال تعتمد.
- وصال تستطيع تغيير الحالة إلى مباع/مؤجر/غير متاح عند الحاجة التشغيلية حتى بدون طلب المالك، مع تسجيل العملية والسبب.
- الأنواع والمزايا والفلاتر ديناميكية من لوحة الإدارة.

## الحجوزات
- لا يوجد تأكيد نهائي فوري من المستخدم.
- الطلب يمر أولًا بتحقق وصال من التوفر.
- المهلة الافتراضية للدفع بعد الموافقة: ساعتان.
- العربون الافتراضي: 5%، قابل للتعديل لكل منشأة/باقة.
- العربون غير مسترد افتراضيًا.
- الإدارة تستطيع تنفيذ استرداد استثنائي مع سبب وسجل تدقيق.
- الدفع لا يصبح Verified إلا بعد اعتماد موظف مخول.
- إذا تعذر تنفيذ الحجز بعد الدفع، يعرض بديل أولًا، ثم رد كامل المبلغ إن رفض العميل.
- التقييم فقط بعد Booking مكتمل.

## حالات الحجز المقترحة
NEW_REQUEST
CHECKING_AVAILABILITY
AVAILABLE_AWAITING_DEPOSIT
PAYMENT_SUBMITTED
PAYMENT_UNDER_REVIEW
PAYMENT_VERIFIED
CONFIRMING_WITH_PROPERTY
CONFIRMED
CANCELLED
REJECTED
EXPIRED
COMPLETED

## إدارة الأملاك
- العمليات المالية والقانونية لا تُحذف حذفًا نهائيًا.
- العقود تستخدم versioning.
- لا يسمح بإشغالين فعالين متداخلين لنفس الوحدة.
- دفعات الإيجار والمصروفات والصيانة مرتبطة بعقار/وحدة/عقد عند الحاجة.
- كشف المالك يراجع ثم يعتمد قبل إرساله.

## الخدمات
- الخدمة قد تكون مجانية أو مدفوعة.
- طريقة التنفيذ قد تكون نموذج طلب فقط أو طلب + موعد + سعر حسب نوع الخدمة.


---

# ملحق مصدر: `PERMISSIONS.md`

# وصال V1 — الصلاحيات

## المستخدم العادي
- التسجيل والدخول
- تعديل الملف الشخصي
- المفضلة والمقارنة
- حفظ البحث والتنبيهات
- البحث عن العقارات والحجوزات
- إضافة عقار ضمن المسار العام
- إنشاء طلب عقار
- إنشاء طلب حجز
- إدخال بيانات الإيداع
- إنشاء طلب خدمة
- طلب إدارة عقار
- الاطلاع على حالاته وطلباته الخاصة فقط
- تقييم منشأة بعد حجز مكتمل

## المالك
نفس صلاحيات المستخدم العادي، إضافة إلى:
- متابعة عقاراته
- متابعة طلبات التعديل والحالة
- متابعة إدارة أملاكه
- الاطلاع على العقود والدفعات والتقارير المسموحة له
- متابعة الحجوزات إذا كان مزود منشأة معتمدة

## الوسيط العقاري
يعامل كمستخدم عادي، دون صلاحيات خاصة.

## مقدم منشأة حجز
- إدارة بيانات منشأته بعد الاعتماد
- إدارة الغرف/الوحدات/الباقات
- إدارة التوفر والأسعار
- الاطلاع على حجوزاته
- الاطلاع على عمولة وصال الخاصة بمنشأته
- لا ينشر إنشاء/تعديل حساس قبل مراجعة وصال

## موظف خدمة العملاء
- إدارة الطلبات والحجوزات
- التواصل والمتابعة
- تحديث حالات ضمن الصلاحية
- تسجيل ملاحظات
- لا يعتمد العمليات المالية إلا إذا مُنح صلاحية مستقلة

## مراجع العقارات
- مراجعة العقارات والتعديلات
- قبول/رفض مع سبب
- إدارة حالات النشر

## إدارة الأملاك
- المستأجرون
- العقود
- الإشغال
- الدفعات التشغيلية
- الصيانة
- التقارير التشغيلية

## المحاسب
- مراجعة الإيداعات
- المدفوعات
- المصروفات
- كشوف الملاك
- التقارير المالية

## مدير النظام
- المستخدمون
- الأدوار والصلاحيات
- الإعدادات
- العملات والمدن
- أنواع العقارات والمنشآت
- البنوك
- الخدمات
- Audit Logs
- إدارة عامة للنظام


---

# ملحق مصدر: `UI_GUIDE.md`

# وصال V1 — دليل الواجهة والهوية

## الهوية
الهوية الأساسية كما في المراجع التي اعتمدها صاحب المشروع:
- أسود فاخر
- ذهبي
- بيج/عاجي دافئ
- أبيض نظيف
- رمادي مساعد

## الاتجاه
- RTL للعربية
- LTR للإنجليزية
- Responsive mobile-first
- تصميم ويب احترافي يبدو كتجربة تطبيق على الهاتف

## الأسلوب
- بطاقات كبيرة وواضحة
- حواف دائرية
- ظلال خفيفة
- مساحات بيضاء جيدة
- تباين واضح
- لمسات ذهبية محدودة وراقية
- الشريط الجانبي الإداري داكن مع العنصر النشط بالذهبي

## قسم العقارات
- تبويب بيع / إيجار
- قائمة كطريقة عرض أساسية
- الخريطة اختيارية ومهيأة من البداية
- بطاقات عقار واضحة
- صفحة تفاصيل طويلة ومنظمة
- فلاتر ديناميكية حسب النوع

## قسم الحجوزات
مرجع UX مشابه للصور المعتمدة:
- تصنيفات أعلى الصفحة
- بحث وفلترة
- بطاقات منشآت بصور كبيرة
- تفاصيل منشأة
- مرافق
- باقات
- تقويم توفر
- صفحة حجوزاتي وحالات واضحة
- إشعارات

## إدارة الأملاك
على الهاتف:
- الرئيسية
- العقارات
- الدفعات
- العقود
- المزيد

على الكمبيوتر:
- Sidebar
- الرئيسية
- العقارات
- الوحدات
- المستأجرون
- العقود
- الدفعات
- الصيانة
- التقارير
- المستندات

## حالات النظام
استخدم status chips بألوان واضحة للحالات:
- نشط / معتمد
- قيد المراجعة
- بانتظار الدفع
- مرفوض
- ملغي
- مسودة


---

# ملحق مصدر: `DATABASE.md`

# وصال V1 — تصميم قاعدة البيانات

> هذه الوثيقة تقترح بنية قاعدة بيانات MySQL لمشروع وصال V1، بما يتوافق مع قرارات المنتج الحالية:
> عقارات + حجوزات + إدارة أملاك + خدمات + مدفوعات يدوية + لوحة إدارة + REST API مستقبلي.
> القاعدة مصممة كـ Modular Monolith وقابلة للتوسع لاحقًا دون كسر البنية الأساسية.

---

# مراجعة التصميم — قرارات مثبتة

تمت مراجعة المخطط قبل بدء الـ migrations، واعتمدت التعديلات التالية:
1. `property_owners` هو مصدر الحقيقة للملكية؛ لا نكرر المالك داخل `properties`.
2. خصائص العقار أصبحت قابلة للربط بالعقار أو الوحدة.
3. مرافق المنشآت والوحدات فصلت إلى Pivot Tables واضحة.
4. أضيف `units_quantity` و`booking_inventory_holds` لمنع Overbooking خصوصًا للفنادق.
5. أضيف تنبيه ضد تكرار رقم الإيداع داخل نفس البنك.
6. ربط الإشغال بالعقد عبر `contract_id`.
7. أضيفت `owner_statement_lines` لتفاصيل كشف المالك.
8. أضيفت حقول خدمات ديناميكية `service_form_fields`.
9. يبقى الموقع الدقيق والبيانات الحساسة محمية من Public API.

---

# 1. مبادئ عامة

## 1.1 قواعد التصميم
- استخدام `BIGINT UNSIGNED` للمفاتيح الأساسية.
- جميع الجداول التشغيلية تستخدم:
  - `id`
  - `created_at`
  - `updated_at`
- استخدام `deleted_at` فقط للجداول التي يسمح فيها Soft Delete.
- يمنع Hard Delete للجداول الحساسة ماليًا أو قانونيًا.
- كل القيم التشغيلية للحالات تحفظ بالإنجليزية داخل DB/API، والعرض بالعربية/الإنجليزية من طبقة الترجمة.
- الملفات لا تحفظ كـ BLOB داخل MySQL.
- المبالغ تحفظ كـ `DECIMAL(18,2)`.
- العملات تحفظ برمز ISO أو رمز داخلي قابل للإدارة.
- التواريخ والأوقات تحفظ بتوقيت موحد ويُراعى timezone عند العرض.
- كل تغيير مالي أو قانوني أو حالة حساسة يسجل في `audit_logs`.

---

# 2. الهوية والمستخدمون والصلاحيات

## 2.1 users
الحساب الأساسي الموحد.

حقول مقترحة:
- id
- name
- email nullable
- email_verified_at nullable
- phone
- whatsapp_phone
- password
- preferred_language
- preferred_currency_id nullable
- preferred_city_id nullable
- status
- last_login_at nullable
- created_at
- updated_at
- deleted_at nullable

قيود:
- phone unique
- email unique nullable

---

## 2.2 otp_codes
رموز التحقق.

- id
- user_id nullable
- channel: `whatsapp | email`
- destination
- code_hash
- purpose
- expires_at
- verified_at nullable
- attempts
- created_at

---

## 2.3 auth_sessions
إدارة الجلسات والأجهزة.

- id
- user_id
- token_hash
- ip_address nullable
- user_agent nullable
- last_activity_at
- expires_at nullable
- revoked_at nullable
- created_at

---

## 2.4 user_devices
- id
- user_id
- device_name nullable
- platform nullable
- push_token nullable
- last_seen_at nullable
- created_at
- updated_at

---

## 2.5 roles
- id
- name
- slug
- description nullable

## 2.6 permissions
- id
- name
- slug
- module

## 2.7 role_user
- user_id
- role_id

## 2.8 permission_role
- role_id
- permission_id

ملاحظة:
الوسيط العقاري لا يملك دورًا مميزًا تشغيليًا؛ يعامل كمستخدم عادي ما لم تمنحه الإدارة دورًا آخر صراحة.

---

# 3. البيانات المرجعية

## 3.1 countries
## 3.2 governorates
## 3.3 cities
## 3.4 districts
## 3.5 neighborhoods

تسلسل الموقع:
`country -> governorate -> city -> district -> neighborhood`

---

## 3.6 currencies
- id
- code
- name_ar
- name_en
- symbol
- is_active
- exchange_rate nullable

---

## 3.7 banks
حسابات وصال البنكية.

- id
- name
- account_name
- account_number
- iban nullable
- currency_id
- instructions nullable
- is_active
- sort_order

---

# 4. العقارات الأساسية

## 4.1 property_types
أنواع العقارات قابلة للإدارة.

أمثلة:
- apartment
- villa
- land
- building
- house
- shop
- farm
- floor

حقول:
- id
- name_ar
- name_en
- slug
- is_active
- sort_order

---

## 4.2 properties
الكيان الحقيقي للعقار.

- id
- created_by_user_id
- property_type_id
- internal_code
- title_ar
- title_en nullable
- description_ar nullable
- description_en nullable
- status
- city_id
- district_id nullable
- neighborhood_id nullable
- street_name nullable
- public_location_text nullable
- exact_address nullable
- public_latitude nullable
- public_longitude nullable
- exact_latitude nullable
- exact_longitude nullable
- area nullable
- floors_count nullable
- units_count nullable
- year_built nullable
- created_at
- updated_at
- deleted_at nullable

ملاحظات:
- `exact_*` لا يعرض عبر Public API.
- ملكية العقار لا تخزن مباشرة داخل `properties` لتجنب تكرار مصدر الحقيقة؛ تستخدم `property_owners`.
- في V1 الواجهة تتعامل مع مالك أساسي واحد، لكن البنية تدعم التوسع مستقبلًا.

---

## 4.3 property_owners
لتجهيز تعدد الملاك مستقبلًا.

- id
- property_id
- user_id nullable
- external_owner_name nullable
- ownership_percentage nullable
- is_primary
- valid_from nullable
- valid_to nullable

بهذا نحافظ على تجربة "مالك واحد" حاليًا دون إغلاق باب تعدد الملاك مستقبلًا.

---

## 4.4 property_units
للوحدات داخل العقار.

- id
- property_id
- code
- name
- floor_number nullable
- unit_type nullable
- area nullable
- bedrooms nullable
- bathrooms nullable
- halls nullable
- status
- created_at
- updated_at
- deleted_at nullable

---

# 5. خصائص العقارات والفلاتر الديناميكية

## 5.1 property_attributes
تعريف خاصية/فلتر.

أمثلة:
- bedrooms
- bathrooms
- furnished
- elevator
- parking
- private_roof
- air_conditioning

حقول:
- id
- name_ar
- name_en
- key
- data_type: `boolean | integer | decimal | text | select | multi_select`
- is_filterable
- is_searchable
- is_active

---

## 5.2 property_type_attributes
تحدد الخصائص المسموحة لكل نوع عقار.

- property_type_id
- property_attribute_id
- is_required
- sort_order

---

## 5.3 property_attribute_values
القيم الفعلية للعقار أو الوحدة.

- id
- attributable_type: `property | property_unit`
- attributable_id
- property_attribute_id
- value_text nullable
- value_number nullable
- value_boolean nullable
- value_json nullable

قاعدة:
يجب التحقق من أن الخاصية مسموحة لنوع العقار/الوحدة قبل الحفظ.

---

# 6. الإعلانات العقارية

## 6.1 property_listings
الإعلان المنشور أو المسودة.

- id
- property_id
- listing_number
- purpose: `sale | rent`
- price
- currency_id
- price_period nullable
- public_title
- public_description
- status
- published_at nullable
- expires_at nullable
- featured_until nullable
- created_by_user_id
- reviewed_by_user_id nullable
- reviewed_at nullable
- created_at
- updated_at
- deleted_at nullable

حالات مقترحة:
- draft
- pending_review
- changes_requested
- approved
- published
- paused
- rejected
- expired
- sold
- rented
- unavailable

---

## 6.2 property_listing_versions
مهم جدًا لدعم المراجعة قبل تغيير النسخة المنشورة.

- id
- listing_id
- version_number
- payload_json
- status: `pending | approved | rejected`
- submitted_by_user_id
- reviewed_by_user_id nullable
- review_notes nullable
- created_at
- reviewed_at nullable

قاعدة:
النسخة المنشورة الحالية تبقى ظاهرة حتى اعتماد النسخة الجديدة.

---

# 7. الصور والملفات

## 7.1 media_files
مركز ملفات عام.

- id
- uploaded_by_user_id
- disk
- path
- original_name
- mime_type
- size_bytes
- visibility
- checksum nullable
- metadata_json nullable
- created_at

---

## 7.2 media_links
ربط أي ملف بأي كيان.

- id
- media_file_id
- entity_type
- entity_id
- purpose
- sort_order
- created_at

أمثلة `purpose`:
- property_gallery
- booking_gallery
- deposit_receipt
- contract_pdf
- maintenance_photo
- owner_document
- provider_license

---

# 8. المفضلة والمقارنة والبحث المحفوظ

## 8.1 favorites
- id
- user_id
- entity_type
- entity_id
- created_at

## 8.2 property_comparisons
- id
- user_id
- name nullable
- created_at

## 8.3 property_comparison_items
- comparison_id
- listing_id

## 8.4 saved_searches
- id
- user_id
- name
- filters_json
- alerts_enabled
- last_notified_at nullable
- created_at
- updated_at

---

# 9. طلبات العقار CRM

## 9.1 property_requests
- id
- reference_number
- user_id nullable
- source
- purpose
- property_type_id nullable
- city_id nullable
- district_id nullable
- neighborhood_id nullable
- min_price nullable
- max_price nullable
- currency_id nullable
- requirements_json
- wants_field_search
- assigned_to_user_id nullable
- status
- created_at
- updated_at

مصادر:
- web
- phone
- whatsapp
- office
- admin

---

## 9.2 property_request_status_history
- id
- property_request_id
- from_status nullable
- to_status
- changed_by_user_id
- notes nullable
- created_at

---

## 9.3 property_request_matches
ربط طلب العميل بعقار مقترح.

- id
- property_request_id
- listing_id
- proposed_by_user_id
- status
- notes nullable
- created_at

---

# 10. الحجوزات والمنشآت

## 10.1 hospitality_categories
أمثلة:
- hotel
- chalet
- rest_house
- hall

- id
- name_ar
- name_en
- slug
- is_active
- sort_order

---

## 10.2 hospitality_establishments
- id
- owner_user_id
- category_id
- name_ar
- name_en nullable
- description_ar nullable
- description_en nullable
- city_id
- district_id nullable
- address_text nullable
- public_latitude nullable
- public_longitude nullable
- exact_latitude nullable
- exact_longitude nullable
- check_in_time nullable
- check_out_time nullable
- default_deposit_percentage default 5.00
- commission_percentage nullable
- status
- reviewed_by_user_id nullable
- reviewed_at nullable
- created_at
- updated_at
- deleted_at nullable

---

## 10.3 hospitality_units
لغرف الفندق أو الوحدات القابلة للحجز.

- id
- establishment_id
- name_ar
- name_en nullable
- unit_type
- code nullable
- capacity_adults
- capacity_children nullable
- quantity default 1
- base_price nullable
- currency_id nullable
- status
- created_at
- updated_at

---

## 10.4 hospitality_amenities
- id
- name_ar
- name_en
- icon nullable
- is_active

## 10.5 establishment_amenity
- establishment_id
- amenity_id

## 10.6 hospitality_unit_amenity
- hospitality_unit_id
- amenity_id

السبب:
فصل العلاقتين يمنع الصفوف غير الصالحة التي تحتوي على `establishment_id` و`hospitality_unit_id` معًا أو كلاهما فارغ.

---

# 11. الباقات والأسعار

## 11.1 booking_packages
- id
- establishment_id
- hospitality_unit_id nullable
- name_ar
- name_en nullable
- description nullable
- price
- currency_id
- start_time nullable
- end_time nullable
- max_guests nullable
- deposit_percentage nullable
- is_active
- created_at
- updated_at

---

## 11.2 seasonal_prices
- id
- establishment_id
- hospitality_unit_id nullable
- booking_package_id nullable
- start_date
- end_date
- price
- currency_id
- priority
- created_at

---

# 12. التوفر

## 12.1 availability_blocks
- id
- establishment_id
- hospitality_unit_id nullable
- start_at
- end_at
- reason
- block_type: `closed | maintenance | external_booking | manual`
- created_by_user_id
- created_at

---

# 13. الحجوزات

## 13.1 bookings
- id
- booking_number
- user_id
- establishment_id
- hospitality_unit_id nullable
- booking_package_id nullable
- check_in_at
- check_out_at
- adults
- children nullable
- guests_total
- units_quantity default 1
- subtotal
- deposit_percentage
- deposit_amount
- security_deposit_amount default 0
- total_amount
- currency_id
- status
- payment_deadline_at nullable
- availability_hold_until nullable
- assigned_to_user_id nullable
- notes nullable
- created_at
- updated_at

الحالات:
- NEW_REQUEST
- CHECKING_AVAILABILITY
- AVAILABLE_AWAITING_DEPOSIT
- PAYMENT_SUBMITTED
- PAYMENT_UNDER_REVIEW
- PAYMENT_VERIFIED
- CONFIRMING_WITH_PROPERTY
- CONFIRMED
- CANCELLED
- REJECTED
- EXPIRED
- COMPLETED

---

## 13.2 booking_status_history
- id
- booking_id
- from_status nullable
- to_status
- changed_by_user_id
- notes nullable
- created_at

## 13.3 booking_inventory_holds
حجز مؤقت للمخزون عند تأكيد التوفر من وصال وحتى انتهاء مهلة الدفع.

- id
- booking_id
- hospitality_unit_id
- quantity
- starts_at
- ends_at
- expires_at
- released_at nullable
- created_at

قاعدة:
- ينشأ الـ Hold فقط بعد تحقق وصال من التوفر.
- ينتهي تلقائيًا عند انتهاء مهلة الدفع أو رفض/إلغاء الطلب.
- عند التأكيد النهائي يتحول أثره إلى حجز مؤكد ويظل احتساب المخزون مانعًا للتداخل.

---

# 14. الإيداعات والمدفوعات

## 14.1 payment_transactions
نظام دفع يدوي موحد للحجوزات والخدمات والإعلانات المميزة وغيرها.

- id
- reference_number
- payer_user_id
- payable_type
- payable_id
- bank_id
- amount
- currency_id
- deposit_reference
- deposited_at nullable
- receipt_media_id nullable
- status
- verified_by_user_id nullable
- verified_at nullable
- rejection_reason nullable
- created_at
- updated_at

حالات:
- submitted
- under_review
- verified
- rejected
- refunded
- partially_refunded

قواعد إضافية:
- يجب تنبيه الموظف عند تكرار `bank_id + deposit_reference` وعدم اعتماد الإيداع المكرر دون مراجعة.
- لا يعتبر رقم الإيداع وحده دليلًا نهائيًا؛ الاعتماد يتم يدويًا بواسطة موظف مخول.

---

## 14.2 refunds
- id
- payment_transaction_id
- amount
- reason
- approved_by_user_id
- processed_at nullable
- notes nullable
- created_at

قاعدة:
الاسترداد استثنائي ويجب تسجيل السبب.

---

# 15. التقييمات

## 15.1 reviews
- id
- user_id
- reviewable_type
- reviewable_id
- booking_id nullable
- rating
- comment nullable
- status
- moderated_by_user_id nullable
- created_at
- updated_at

قاعدة:
تقييم منشأة حجز يتطلب حجزًا مكتملًا.

---

# 16. الإعلانات المميزة

## 16.1 promotion_packages
- id
- name
- duration_days
- price
- currency_id
- is_active

## 16.2 promotions
- id
- promotable_type
- promotable_id
- promotion_package_id
- requested_by_user_id
- payment_transaction_id nullable
- starts_at nullable
- ends_at nullable
- status
- created_at

---

# 17. إدارة الأملاك

## 17.1 property_management_requests
- id
- user_id
- property_id nullable
- details
- status
- assigned_to_user_id nullable
- created_at
- updated_at

---

## 17.2 managed_properties
- id
- property_id
- owner_user_id
- management_start_date
- management_end_date nullable
- management_fee_type
- management_fee_value
- status
- created_at
- updated_at

---

# 18. المستأجرون والإشغال

## 18.1 tenants
- id
- user_id nullable
- name
- phone
- email nullable
- identity_number nullable
- notes nullable
- created_at
- updated_at

---

## 18.2 tenancies
- id
- managed_property_id
- property_unit_id
- tenant_id
- contract_id nullable
- starts_at
- ends_at
- rent_amount
- currency_id
- payment_frequency
- status
- created_at
- updated_at

قاعدة:
لا يسمح بإشغالين فعالين متداخلين لنفس الوحدة.

---

# 19. العقود

## 19.1 contracts
- id
- contract_number
- contract_type
- related_type
- related_id
- status
- starts_at nullable
- ends_at nullable
- created_by_user_id
- created_at
- updated_at

## 19.2 contract_versions
- id
- contract_id
- version_number
- content_json nullable
- pdf_media_id nullable
- status
- accepted_at nullable
- accepted_by_user_id nullable
- acceptance_ip nullable
- created_at

لا يتم حذف النسخ السابقة.

---

# 20. استحقاقات الإيجار والدفعات

## 20.1 rent_due_items
- id
- tenancy_id
- due_date
- amount
- currency_id
- status
- paid_amount default 0
- created_at
- updated_at

## 20.2 rent_payments
- id
- tenancy_id
- due_item_id nullable
- payment_transaction_id nullable
- amount
- currency_id
- paid_at
- recorded_by_user_id
- notes nullable
- created_at

---

# 21. المصروفات

## 21.1 expenses
- id
- managed_property_id nullable
- property_unit_id nullable
- maintenance_request_id nullable
- category
- amount
- currency_id
- occurred_at
- description
- receipt_media_id nullable
- recorded_by_user_id
- status
- created_at

---

# 22. الصيانة

## 22.1 maintenance_requests
- id
- reference_number
- managed_property_id
- property_unit_id nullable
- tenant_id nullable
- requested_by_user_id nullable
- title
- description nullable
- priority
- status
- owner_approval_required
- owner_approved_at nullable
- approved_by_user_id nullable
- technician_name nullable
- estimated_cost nullable
- final_cost nullable
- currency_id nullable
- created_at
- updated_at

حالات مقترحة:
- new
- under_review
- awaiting_owner_approval
- approved
- assigned
- in_progress
- completed
- cancelled

---

# 23. كشوف الملاك

## 23.1 owner_statements
- id
- owner_user_id
- managed_property_id nullable
- period_start
- period_end
- gross_income
- expenses_total
- maintenance_total
- management_fees
- net_amount
- currency_id
- status
- reviewed_by_user_id nullable
- approved_by_user_id nullable
- pdf_media_id nullable
- created_at

حالات:
- draft
- under_review
- approved
- sent

## 23.2 owner_statement_lines
تفاصيل الكشف بدل الاعتماد على المجاميع فقط.

- id
- owner_statement_id
- line_type: `rent | expense | maintenance | management_fee | adjustment`
- reference_type nullable
- reference_id nullable
- description
- amount
- direction: `credit | debit`
- created_at

---

# 24. الخدمات

## 24.1 service_categories
- id
- name_ar
- name_en
- is_active

## 24.2 services
- id
- service_category_id
- name_ar
- name_en
- description_ar nullable
- description_en nullable
- pricing_type
- price nullable
- currency_id nullable
- requires_appointment
- is_active
- created_at
- updated_at

---

## 24.3 service_form_fields
حقول ديناميكية تحددها الإدارة لكل خدمة.

- id
- service_id
- key
- label_ar
- label_en nullable
- field_type
- is_required
- options_json nullable
- validation_rules_json nullable
- sort_order
- is_active

## 24.4 service_requests
- id
- reference_number
- service_id
- user_id
- status
- requested_date nullable
- appointment_at nullable
- amount nullable
- currency_id nullable
- payment_transaction_id nullable
- assigned_to_user_id nullable
- details_json nullable
- created_at
- updated_at

---

# 25. الإشعارات

## 25.1 notifications
يمثل الحدث نفسه.

- id
- user_id
- type
- title
- body
- data_json nullable
- read_at nullable
- created_at

## 25.2 notification_deliveries
يمثل قناة الإرسال.

- id
- notification_id
- channel: `in_app | whatsapp | email`
- destination nullable
- status
- attempts
- last_attempt_at nullable
- provider_response nullable
- created_at

---

# 26. المهام والملاحظات

## 26.1 tasks
- id
- title
- description nullable
- related_type nullable
- related_id nullable
- assigned_to_user_id nullable
- created_by_user_id
- due_at nullable
- status
- created_at
- updated_at

## 26.2 notes
- id
- related_type
- related_id
- author_user_id
- visibility
- body
- created_at

---

# 27. سجل التدقيق

## 27.1 audit_logs
Append-only.

- id
- actor_user_id nullable
- action
- entity_type
- entity_id nullable
- old_values_json nullable
- new_values_json nullable
- ip_address nullable
- user_agent nullable
- created_at

لا يوجد Update/Delete عادي لهذا الجدول.

---

# 28. إعدادات النظام

## 28.1 settings
- id
- key
- value_json
- group
- is_public
- updated_by_user_id nullable
- updated_at

أمثلة:
- booking_payment_timeout_minutes = 120
- default_booking_deposit_percentage = 5
- default_language = ar
- default_currency
- maintenance_owner_approval rules
- notification templates

---

# 29. أهم العلاقات

```text
User
 ├─ Properties
 ├─ Property Requests
 ├─ Bookings
 ├─ Payments
 ├─ Service Requests
 └─ Reviews

Property
 ├─ Property Units
 ├─ Listings
 ├─ Files
 └─ Managed Property

Hospitality Establishment
 ├─ Units
 ├─ Packages
 ├─ Amenities
 ├─ Availability Blocks
 ├─ Bookings
 └─ Reviews

Booking
 ├─ Status History
 ├─ Payment Transaction
 └─ Review (after completion)

Managed Property
 ├─ Tenancies
 ├─ Contracts
 ├─ Rent Due Items
 ├─ Expenses
 ├─ Maintenance
 └─ Owner Statements
```

---

# 30. الفهارس المهمة

ينبغي إنشاء Indexes على الأقل على:
- users.phone
- users.email
- properties.city_id
- properties.property_type_id
- property_listings.status
- property_listings.purpose
- property_listings.price
- property_listings.published_at
- property_requests.status
- bookings.status
- bookings.check_in_at
- bookings.check_out_at
- booking_inventory_holds.hospitality_unit_id + starts_at + ends_at
- payment_transactions.status
- payment_transactions.deposit_reference
- hospitality_establishments.city_id
- hospitality_establishments.category_id
- rent_due_items.due_date
- maintenance_requests.status
- audit_logs.entity_type + entity_id

للبحث الجغرافي المتقدم يمكن إضافة SPATIAL columns/indexes لاحقًا عند الحاجة دون أن يكون ذلك شرطًا للنسخة الأولى.

---

# 31. ترتيب إنشاء Migrations في Codex

1. Identity / Users / RBAC
2. Geography / Currencies / Banks
3. Media Center
4. Properties / Units / Attributes
5. Listings / Listing Versions
6. Favorites / Saved Searches / Property Requests
7. Hospitality / Units / Amenities / Packages
8. Availability / Bookings / Booking History
9. Payments / Refunds
10. Reviews / Promotions
11. Managed Properties / Tenants / Tenancies
12. Contracts / Contract Versions
13. Rent / Expenses / Maintenance / Statements
14. Services / Service Requests
15. Notifications / Tasks / Notes
16. Audit / Settings

---

# 32. قرارات مؤجلة

يجب عدم بناء هذه كمتطلبات أساسية في V1 ما لم يصدر قرار لاحق:
- تطبيق Flutter
- Redis/Horizon
- Microservices
- دفع إلكتروني ببطاقات
- دردشة مباشرة بين العميل والمالك
- WebSocket real-time
- Elasticsearch
- خرائط متقدمة كشرط أساسي
- تعدد ملاك ظاهر في واجهة المستخدم


---

# ملحق مصدر: `CODEX_INSTRUCTIONS.md`

# تعليمات Codex — مشروع وصال

أنت تعمل على مشروع Production حقيقي باسم وصال.

## قواعد عامة
1. اقرأ PROJECT_SPEC.md وARCHITECTURE.md وBUSINESS_RULES.md وPERMISSIONS.md وUI_GUIDE.md قبل أي تعديل.
2. لا تخترع متطلبات جديدة.
3. لا تغير قواعد العمل دون تسجيل القرار في docs/DECISIONS.md.
4. استخدم Modular Monolith.
5. ضع منطق الأعمال في Actions/Services/Domain classes وليس في Blade/Livewire مباشرة.
6. استخدم Policies/Permissions لكل عملية حساسة.
7. اكتب migrations وseeders وfactories واختبارات.
8. استخدم database transactions للعمليات متعددة الخطوات.
9. كل تغيير مالي أو قانوني أو حالة حساسة يجب أن يكتب Audit Log.
10. لا تستخدم hard delete للمدفوعات والعقود وAudit والأحداث المالية.
11. الواجهة عربية RTL افتراضيًا، مع دعم English.
12. يجب أن تعمل على Hostinger Premium بدون Redis/Horizon/Octane كمتطلبات أساسية.
13. لا تخزن الملفات كـ BLOB.
14. استخدم Laravel Filesystem abstraction.
15. حافظ على REST API /api/v1 للتطبيق المستقبلي.

## ترتيب التنفيذ
Phase 1:
- Laravel foundation
- Authentication
- Users
- Roles/Permissions
- Admin shell
- Audit
- Settings: cities, currencies, banks, categories

Phase 2:
- Real Estate
- Property types/features
- Listings
- Review workflow
- Favorites
- Saved searches
- Property requests

Phase 3:
- Bookings & Hospitality
- Establishments
- Rooms/Units
- Packages
- Availability
- Booking workflow
- Deposits
- Reviews

Phase 4:
- Property Management
- Units
- Tenants
- Contracts
- Payments
- Expenses
- Maintenance
- Owner statements

Phase 5:
- Services
- Promotions
- Notifications
- Reports
- Hardening / QA / performance

## Definition of Done
لا تعتبر المرحلة منتهية إلا عند:
- نجاح migrations
- نجاح automated tests
- وجود authorization tests
- وجود validation
- mobile responsive UI
- no obvious N+1 queries
- audit coverage للحالات الحساسة
- توثيق أي قرار جديد


---

# ملحق مصدر: `PHASE_01_CODEX_PROMPT.md`

# وصال — Prompt المرحلة الأولى لـ Codex

نفذ Phase 1 فقط. لا تبدأ العقارات أو الحجوزات أو إدارة الأملاك بعد.

## اقرأ أولًا
- PROJECT_SPEC.md
- ARCHITECTURE.md
- BUSINESS_RULES.md
- PERMISSIONS.md
- UI_GUIDE.md
- DATABASE.md
- CODEX_INSTRUCTIONS.md

## الهدف
إنشاء أساس Production-ready لمشروع وصال يعمل على Hostinger Premium ويكون جاهزًا لبقية الوحدات.

## المطلوب

### 1. تهيئة المشروع
- Laravel مستقر وحديث ومتوافق مع استضافة Hostinger Premium.
- MySQL.
- Blade + Livewire + Alpine.js + Tailwind CSS.
- Filament للوحة الإدارة.
- إعداد RTL للعربية وLTR للإنجليزية.
- إعداد `.env.example` واضح بدون أسرار.

### 2. الهوية
طبّق هوية وصال:
- أسود فاخر
- ذهبي
- عاجي/بيج دافئ
- أبيض
- رمادي مساعد
- بطاقات بحواف دائرية
- مساحات نظيفة
- Sidebar داكن وعناصر نشطة ذهبية

لا تنسخ واجهات المراجع حرفيًا؛ استخدم الهوية والأسلوب المعتمد فقط.

### 3. المصادقة
أنشئ:
- users
- otp_codes
- auth_sessions
- user_devices

المسار:
1. المستخدم يدخل رقم الاتصال ورقم WhatsApp.
2. يختار استلام OTP عبر WhatsApp أو Email.
3. إذا اختار Email ولم يكن مسجلًا، يطلب النظام البريد.
4. يتم التحقق من OTP.
5. المستخدم لديه كلمة مرور أيضًا.
6. وفر استعادة كلمة المرور بطريقة آمنة.

في بيئة التطوير:
- لا تتصل فعليًا بمزود WhatsApp.
- أنشئ Notification/OTP Provider interface مع Fake provider للتطوير والاختبارات.
- أنشئ Email provider باستخدام Laravel Mail.

### 4. RBAC
أنشئ:
- roles
- permissions
- role_user
- permission_role

وفر الأدوار الأولية:
- user
- customer_service
- property_reviewer
- property_management
- accountant
- financial_approver
- system_admin

الوسيط العقاري لا يأخذ دورًا خاصًا افتراضيًا.

### 5. البيانات المرجعية
أنشئ:
- countries
- governorates
- cities
- districts
- neighborhoods
- currencies
- banks

كلها قابلة للإدارة من Filament.

### 6. الإعدادات
أنشئ `settings` بنظام typed access قدر الإمكان.
Seed:
- booking_payment_timeout_minutes = 120
- default_booking_deposit_percentage = 5
- default_language = ar

### 7. Audit
أنشئ `audit_logs` Append-only.
سجل على الأقل:
- تسجيل الدخول الحساس
- تغيير الدور/الصلاحيات
- تغيير بيانات البنك
- تغيير إعدادات النظام
- تعطيل/تفعيل مستخدم

### 8. لوحة الإدارة
Filament dashboard يتضمن:
- Users
- Roles
- Permissions
- Geography
- Currencies
- Banks
- Settings
- Audit Logs

الصلاحيات تطبق فعليًا وليست مجرد إخفاء عناصر الواجهة.

### 9. API
أنشئ `/api/v1` من البداية:
- auth endpoints الأساسية
- profile
- reference data endpoints العامة المناسبة

استخدم API Resources وForm Requests وPolicies.

### 10. Hostinger compatibility
لا تستخدم كمتطلب:
- Redis
- Horizon
- Octane
- Supervisor
- WebSocket server
- Elasticsearch

استخدم:
- database/file cache
- database queue عندما نحتاجها
- Scheduler قابل للتشغيل من cron

### 11. الاختبارات
اكتب اختبارات تغطي:
- registration
- OTP verification
- login/logout
- password recovery
- RBAC
- admin authorization
- bank/settings audit logging
- API authorization

### 12. ممنوع
- لا تبدأ properties.
- لا تبدأ bookings.
- لا تبدأ property management.
- لا تبدأ services.
- لا تنشئ ميزات لم تُذكر.
- لا تجعل الـ frontend مصدر قواعد العمل.

## Definition of Done
قبل الإنهاء:
1. شغّل migrations من قاعدة فارغة.
2. شغّل seeders.
3. شغّل الاختبارات وأصلح الفشل.
4. تحقق من RTL وResponsive.
5. تحقق من أن مستخدمًا عاديًا لا يستطيع دخول صفحات الإدارة الحساسة.
6. اكتب `docs/PHASE_01_REPORT.md` يوضح:
   - ما تم بناؤه
   - migrations
   - routes
   - tests
   - أي قرارات اتخذت
   - أي نقاط تحتاج قرارًا من صاحب المشروع
7. توقف بعد Phase 1 وانتظر المراجعة.
