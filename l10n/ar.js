OC.L10N.register(
    "sharecleanup",
    {
    "Share Cleanup": "تنظيف المشاركات",
    "Automatically ends shares older than the configured number of days. Shared files are tagged with the end date of the share, and the sharing user is notified at 90 % of the lifetime. Shares with their own expiration date are never ended. The files themselves are never deleted.": "ينهي تلقائياً المشاركات التي أقدم من عدد الأيام المحدد. يتم الوسم على الملفات المشاركة بتاريخ انتهاء المشاركة، ويتم إخطار المستخدم المشارك عند 90% من العمر الافتراضي. المشاركات التي لها تاريخ انتهاء خاص بها لا تنتهي أبداً. لا يتم حذف الملفات نفسها.",
    "Maximum share age (days)": "الحد الأقصى لمجال المشاركة (أيام)",
    "Users are notified after {notify} days (90 %), the share ends after {total} days. Shares with their own expiration date are never ended.": "يتم إخطار المستخدمين بعد {notify} أيام (90%)، وتنتهي المشاركة بعد {total} أيام. المشاركات ذات تاريخ انتهاء الصلاحية الخاص بها لا تنتهي أبداً.",
    "Dry-run mode (only log, end nothing)": "وضع التجربة (تسجيل فقط، لا إنهاء لأي شيء)",
    "Enabled by default. Test with \"occ sharecleanup:run --dry-run\" and check the log, then disable.": "مفعل افتراضياً. جرب باستخدام \"occ sharecleanup:run --dry-run\" وتفقد السجل، ثم قم بالتعطيل.",
    "Save": "حفظ",
    "Settings saved.": "تم حفظ الإعدادات.",
    "Error saving settings: ": "خطأ في حفظ الإعدادات: ",
    "Please enter a number of days (minimum 1).": "يرجى إدخال عدد الأيام (الحد الأدنى 1).",
    "Manual run: occ sharecleanup:run [--days=N] [--dry-run] [--force]": "التشغيل اليدوي: occ sharecleanup:run [--days=N] [--dry-run] [--force]",
    "Share of \"%s\" ends soon": "مشاركة \"%s\" ستنتهي قريباً",
    "Your share of \"%1$s\" ends automatically on %2$s. The file itself is not deleted. If the share is still needed, share the file again afterwards.": "مشاركتك لـ \"%1$s\" ستنتهي تلقائياً في %2$s. لا يتم حذف الملف نفسه. إذا كانت المشاركة لا تزال مطلوبة، قم بمشاركة الملف مرة أخرى بعد ذلك."
},
    "nplurals=2; plural=(n != 1);"
);
