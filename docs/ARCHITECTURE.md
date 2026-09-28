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
