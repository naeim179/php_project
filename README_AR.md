# University Complaint Management System — Working Demo

نسخة عرض عملي لمشروع PHP (Student + Administrator) مرتبطة بالجداول الثلاثة التي أنشأتها سابقًا على MariaDB.

## قبل التشغيل

- على Windows، شغّل **Apache** و**MySQL** من XAMPP.
- قاعدة البيانات الموجودة عندك: `university_complaints_db`.
- الجداول الموجودة مسبقًا: `users`, `complaints`, `responses`.
- **لا تحذف الجداول ولا تعِد إنشاءها**؛ الحزمة تستخدم الجداول كما هي. ملف `database/schema.sql` مرجع اختياري لمن يبدأ من الصفر.
- كود `db.php` يستخدم إعداد XAMPP المحلي: `root` بدون كلمة مرور، والـ port هو `3306`؛ إذا غيرت الإعدادات عدل `db.php`.

## التثبيت — VS Code Terminal (Git Bash)

1. نزّل ZIP إلى مجلد Downloads في Windows.
2. داخل Git Bash افتح مجلد مشروعك:

   ```bash
   cd /d/php/htdocs/university_complaints
   ```

3. احتفظ بنسخة احتياطية من ملفاتك الحالية:

   ```bash
   mkdir -p backup_before_demo
   cp -n index.php db.php register.php login.php backup_before_demo/ 2>/dev/null || true
   ```

4. فك ملف ZIP في **نفس مجلد المشروع** (ليس في مجلد فرعي):

   ```bash
   unzip -o ~/Downloads/University_Complaint_60_Percent_Demo.zip
   ```

   إذا حُفظ ملف ZIP بمكان مختلف، غيّر مساره. وإذا لم يتوفر `unzip` في Git Bash، استخرج محتويات ZIP من Windows File Explorer وانسخها إلى هذا المجلد.

5. لإعداد حسابين تجريبيين (طالب + مسؤول) استخدم:

   ```bash
   /d/php/php/php.exe scripts/seed_demo.php
   ```

   انسخ البريد وكلمة المرور اللذين سيظهران في Terminal. عند تكرار الأمر، تتغير كلمات مرور الحسابين التجريبيين فقط.

6. افتح في المتصفح: `http://localhost/university_complaints/index.php`

## تجربة حيّة أمام الدكتور

1. **Student:** سجل دخول بالحساب التجريبي `student.demo@example.test` وكلمة المرور الناتجة من الأمر أعلاه.
2. اضغط **Submit Complaint** وأدخل مثالًا:
   - Title: `Wi-Fi unavailable in Lab 2`
   - Category: `IT`
   - Description: `The Wi-Fi has been unavailable in Lab 2 since this morning.`
3. يظهر رقم الشكوى وحالتها **Pending**. ارجع إلى **My Complaint History** وتأكد من وجودها.
4. اضغط **Logout** وسجّل دخول بحساب `admin.demo@example.test` وكلمة المرور الناتجة.
5. في **Admin Dashboard** ستظهر الشكوى وبجانبها اسم الطالب؛ افتحها وغيّر الحالة إلى **In Progress**.
6. من **Send Response** اكتب `IT team will inspect the network today.` واضغط **Send Response**.
7. ارجع إلى حساب الطالب وشوف الحالة الجديدة والرد داخل نفس الشكوى.
8. افتح **Use Case Diagram** من القائمة واربط كل خطوة بالوظيفة المقابلة في الرسم.

## عرض علاقات قاعدة البيانات بشكل ملموس

من Git Bash:

```bash
/d/php/mysql/bin/mysql.exe -u root university_complaints_db
```

وبداخل MariaDB نفّذ:

```sql
-- العلاقة الأولى: كل شكوى وصاحبها
SELECT c.id, c.title, u.name AS student_name
FROM complaints c
JOIN users u ON c.user_id = u.id;

-- العلاقة الثانية والثالثة: كل رد + الشكوى + المسؤول
SELECT r.id, c.title, a.name AS admin_name, r.message
FROM responses r
JOIN complaints c ON r.complaint_id = c.id
JOIN users a ON r.admin_id = a.id;

-- إثبات أن العلاقات معرفة كـ FOREIGN KEY بالفعل
SELECT TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
FROM information_schema.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA='university_complaints_db'
AND REFERENCED_TABLE_NAME IS NOT NULL;
```

أو افتح الملف `database/verify_relations.sql` الذي يحتوي جميع الاستعلامات.

## ملفات المشروع

| الملف | الوظيفة |
|---|---|
| `index.php` | صفحة البداية وتوجيه المستخدم حسب الدور |
| `register.php` | تسجيل الطالب (كلمة المرور تحفظ كـ hash) |
| `login.php`, `logout.php` | تسجيل الدخول والخروج باستخدام Sessions |
| `student.php` | لوحة الطالب وعرض شكاويه الخاصة |
| `new_complaint.php` | حفظ شكوى جديدة مرتبطة بالطالب |
| `admin.php` | لوحة المسؤول وعرض جميع الشكاوى باستخدام JOIN |
| `complaint.php` | تفاصيل الشكوى، تحديث حالتها وردود المسؤول |
| `diagram.php` | الـ Use Case Diagram الأصلي وعلاقات الجداول |
| `db.php` | الاتصال بقاعدة البيانات باستخدام PDO |
| `helpers.php` | الجلسات وفحص الصلاحيات وCSRF وتنسيق الصفحات |
| `assets/style.css` | تصميم الواجهة |
| `scripts/seed_demo.php` | حسابات عرض محلية من Terminal فقط |

## الحدود والأمان

- المشروع **نسخة جامعية محلية للعرض وليست نسخة إنتاجية**. لا ترفعها كما هي على الإنترنت.
- بيانات حسابات التجربة اصطناعية؛ تجنّب إدخال بيانات شخصية حقيقية.
- يوجد Prepared Statements لمنع SQL injection، و`password_hash` / `password_verify`، وفحص صلاحيات Student/Admin، وCSRF للعمليات التي تغيّر البيانات.
- تحتاج لاحقًا: مستخدم DB بصلاحيات أقل من `root`، حماية إضافية لرفع الملفات إذا أضفته، استعادة كلمة المرور، إعدادات نشر وحفظ أسرار آمنة، مراجعة تحقق الإدخال، اختبارات شاملة.
- تأكد عمليًا من عمل النسخة على XAMPP عندك، لأن اختبار الاتصال بقاعدة البيانات لا يمكن إجراؤه على جهازك من هنا.
