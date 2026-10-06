# تحديث مشروع وصال ورفع التعديلات إلى GitHub

هذا الدليل يشرح طريقة حفظ التعديلات الموجودة على جهازك ثم رفعها إلى مستودع GitHub من PowerShell. الأوامر لا تحذف التعديلات المحلية ولا تعيد ضبط المشروع.

## 1. فتح PowerShell داخل المشروع

```powershell
cd C:\Projects\wasal
```

## 2. التأكد من الفرع والمستودع

```powershell
git status
git branch --show-current
git remote -v
```

يجب التأكد أن `origin` يشير إلى مستودع Wasal الصحيح وأنك على الفرع الذي تريد تحديثه، مثل `main` أو `phase-1-admin-management`.

## 3. فحص التعديلات قبل الحفظ

```powershell
git diff --stat
git diff --check
```

لعرض تفاصيل التعديلات:

```powershell
git diff
```

لا تستخدم `git reset --hard` أو `git clean -fd`؛ فهما قد يحذفان تعديلات المشروع المحلية.

## 4. التحقق من اتصال GitHub

إذا كان GitHub CLI مثبتًا:

```powershell
gh auth status
```

إذا لم تكن مسجلًا:

```powershell
gh auth login
```

اختر:

1. GitHub.com
2. HTTPS
3. تسجيل الدخول من المتصفح أو رمز الجهاز حسب ما يعرضه GitHub CLI

إذا لم يكن `gh` مثبتًا، يمكن استخدام مدير بيانات Git عند تنفيذ `git push`، أو تسجيل الدخول من GitHub Desktop. لا تضع كلمة المرور أو رمز الوصول داخل ملفات المشروع.

## 5. حفظ التعديلات في Commit

أضف ملفات التعديلات الحالية:

```powershell
git add app database docs resources tests
```

راجع ما سيُحفظ قبل إنشاء Commit:

```powershell
git status
git diff --cached --stat
git diff --cached --check
```

إذا كانت القائمة صحيحة، أنشئ Commit:

```powershell
git commit -m "Complete property management improvements"
```

ثم تحقق:

```powershell
git log -1 --oneline
git status
```

يجب أن تكون الشجرة نظيفة، أو أن تبقى فقط ملفات لم تقصد إضافتها.

## 6. رفع الفرع إلى GitHub

احصل على اسم الفرع الحالي:

```powershell
$branch = git branch --show-current
$branch
```

ارفعه إلى GitHub:

```powershell
git push -u origin $branch
```

إذا كنت تريد رفع فرع محدد صراحة:

```powershell
git push -u origin main
```

أو:

```powershell
git push -u origin phase-1-admin-management
```

بعد النجاح:

```powershell
git status
git log -1 --oneline
```

ثم افتح صفحة المستودع في GitHub وتأكد من ظهور الـ Commit والملفات الجديدة.

## 7. إذا رفض GitHub عملية الرفع

### رسالة `non-fast-forward`

لا تستخدم `push --force` مباشرة. احفظ Commit المحلي أولًا ثم نفذ:

```powershell
git fetch origin
git pull --rebase origin $(git branch --show-current)
```

إذا ظهرت تعارضات:

```powershell
git status
```

افتح الملفات التي تحتوي على علامات التعارض، أصلحها، ثم:

```powershell
git add <file>
git rebase --continue
```

بعد انتهاء Rebase:

```powershell
git push origin $(git branch --show-current)
```

إذا كان التعارض غير واضح، أوقف العملية دون حذف الملفات:

```powershell
git rebase --abort
```

### رسالة عدم وجود صلاحية

تحقق من:

```powershell
gh auth status
git remote -v
```

لا تغير عنوان المستودع أو تستخدم `--force` قبل التأكد من الحساب والفرع.

## 8. تحديث نسخة أخرى من المشروع بعد الرفع

على الجهاز أو المجلد الآخر:

```powershell
cd C:\Projects\wasal
git fetch origin
git switch <branch>
git pull --ff-only origin <branch>
```

مثال:

```powershell
git switch main
git pull --ff-only origin main
```

بعد تنزيل التحديثات استخدم PHP 8.4 الخاص بمشروع Wasal:

```powershell
$php = "$env:USERPROFILE\.config\herd\bin\php84\php.exe"

& $php -v
composer install
& $php artisan migrate
& $php artisan optimize:clear
```

## 9. تشغيل المشروع بعد التحديث

### Laravel

```powershell
& $php artisan serve --host=127.0.0.1 --port=8000
```

### Vite أثناء التطوير

في نافذة PowerShell ثانية:

```powershell
cd C:\Projects\wasal
npm.cmd install
npm.cmd run dev
```

### Scheduler أثناء اختبار التنبيهات

في نافذة PowerShell ثالثة:

```powershell
cd C:\Projects\wasal
& $php artisan schedule:work
```

Scheduler مسؤول عن إنشاء الاستحقاقات والمهام والتنبيهات تلقائيًا. Vite مسؤول عن CSS وJavaScript وتحديث الواجهة، ولا ينشئ الإشعارات.

## 10. اختبار سريع بعد التحديث

```powershell
& $php artisan test
& $php artisan wasal:notify-due-tasks
& $php artisan route:list --path=property-management
```

إذا كان المطلوب اختبار المهمة والتنبيه فقط:

```powershell
& $php artisan test --filter=TaskNotificationTest
```

## تسلسل مختصر للاستخدام اليومي

```powershell
cd C:\Projects\wasal
git status
git diff --check
git add app database docs resources tests
git commit -m "Complete property management improvements"
git push -u origin $(git branch --show-current)
```

نفذ `git add` بعد مراجعة `git diff` فقط، ولا تستخدم أوامر الحذف أو إعادة الضبط لتجاوز أي مشكلة.
