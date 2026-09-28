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
