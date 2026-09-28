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
