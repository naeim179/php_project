# سيناريو عرض المشروع أمام الدكتور (5 دقائق)

**00:00–00:30 — المقدمة:** «هذا University Complaint Management System. عنا Student وAdmin حسب الـUse Case Diagram، وقاعدة MariaDB علائقية بثلاث جداول: users وcomplaints وresponses».

**00:30–01:30 — الطالب:** افتح حساب الطالب، أنشئ شكوى (مثال مشكلة Wi-Fi) وتأكد أنها ظهرت برقم خاص وحالة Pending في Dashboard.

**01:30–03:00 — الإدارة:** سجّل الخروج، ادخل بحساب Admin، اعرض اسم الطالب المرتبط بالشكوى. غيّر الحالة إلى In Progress وأضف ردًا.

**03:00–03:40 — المتابعة:** ادخل مجددًا بحساب الطالب، افتح نفس الشكوى وشاهد الحالة والرد.

**03:40–04:30 — العلاقات:** افتح `diagram.php` ثم نفّذ استعلامات JOIN وFOREIGN KEY من `database/verify_relations.sql` أمام الدكتور.

**04:30–05:00 — خلاصة:** «users.id مرتبط بـ complaints.user_id، وcomplaints.id مرتبط بـ responses.complaint_id، وusers.id مرتبط بـ responses.admin_id. هيك أثبتنا العلاقات على أرض الواقع».

## سؤال متوقع: هل الـ Use Case هو نفسه ERD؟

لا. **Use Case Diagram** يوضح الوظائف التي يستطيع الطالب والإدارة القيام بها؛ أما **ERD/Foreign Keys** فتوضح كيف ترتبط الجداول بالبيانات. التطبيق يعرض الاثنين.
