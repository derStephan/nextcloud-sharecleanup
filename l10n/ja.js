OC.L10N.register(
    "sharecleanup",
    {
    "Share Cleanup": "共有クリーンアップ",
    "Automatically ends shares older than the configured number of days. Shared files are tagged with the end date of the share, and the sharing user is notified at 90 % of the lifetime. Shares with their own expiration date are never ended. The files themselves are never deleted.": "設定された日数より古い共有を自動的に終了します。共有ファイルには終了日がタグ付けされ、有効期限の90%に達した時点で共有ユーザーに通知されます。独自の有効期限を持つ共有は終了しません。ファイル自体は削除されません。",
    "Maximum share age (days)": "共有の最大保持期間（日）",
    "Users are notified after {notify} days (90 %), the share ends after {total} days. Shares with their own expiration date are never ended.": "{notify} 日（90%）経過後にユーザーに通知され、{total} 日後に共有が終了します。独自の有効期限を持つ共有は終了しません。",
    "Dry-run mode (only log, end nothing)": "ドライランモード（ログ記録のみ、終了させない）",
    "Enabled by default. Test with \"occ sharecleanup:run --dry-run\" and check the log, then disable.": "デフォルトで有効です。「occ sharecleanup:run --dry-run」でテストしてログを確認してから無効化してください。",
    "Save": "保存",
    "Settings saved.": "設定を保存しました。",
    "Error saving settings: ": "設定の保存エラー: ",
    "Please enter a number of days (minimum 1).": "日数を入力してください（最小1）。",
    "Manual run: occ sharecleanup:run [--days=N] [--dry-run] [--force]": "手動実行: occ sharecleanup:run [--days=N] [--dry-run] [--force]",
    "Share of \"%s\" ends soon": "「%s」の共有まもなく終了します",
    "Your share of \"%1$s\" ends automatically on %2$s. The file itself is not deleted. If the share is still needed, share the file again afterwards.": "「%1$s」の共有は %2$s に自動的に終了します。ファイル自体は削除されません。引き続き共有が必要な場合は、後で再度共有してください。"
},
    "nplurals=2; plural=(n != 1);"
);
