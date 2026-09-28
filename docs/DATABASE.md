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
