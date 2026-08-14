OC.L10N.register(
    "sharecleanup",
    {
    "Share Cleanup": "공유 정리",
    "Automatically ends shares older than the configured number of days. Shared files are tagged with the end date of the share, and the sharing user is notified at 90 % of the lifetime. Shares with their own expiration date are never ended. The files themselves are never deleted.": "설정된 일수보다 오래된 공유를 자동으로 종료합니다. 공유된 파일에는 종료일이 태그되며, 수명 주기의 90%에 도달했을 때 공유 사용자에게 알림이 전송됩니다. 자체 만료일이 있는 공유는 절대 종료되지 않습니다. 파일 자체는 삭제되지 않습니다.",
    "Maximum share age (days)": "최대 유 기간(일)",
    "Users are notified after {notify} days (90 %), the share ends after {total} days. Shares with their own expiration date are never ended.": "{notify}일(90%) 경과 후 사용자에게 알림이 전송되며, {total}일 후에 공유가 종료됩니다. 자체 만료일이 있는 공유는 종료되지 않습니다.",
    "Dry-run mode (only log, end nothing)": "시뮬레이션 모드(로그만 기록, 종료 안 함)",
    "Enabled by default. Test with \"occ sharecleanup:run --dry-run\" and check the log, then disable.": "기본적으로 활성화되어 있습니다. \"occ sharecleanup:run --dry-run\"으로 테스트하고 로그를 확인한 후 비활성화하세요.",
    "Save": "저장",
    "Settings saved.": "설정이 저장되었습니다.",
    "Error saving settings: ": "설정 저장 오류: ",
    "Please enter a number of days (minimum 1).": "일 수를 입력하세요(최소 1일).",
    "Manual run: occ sharecleanup:run [--days=N] [--dry-run] [--force]": "수동 실행: occ sharecleanup:run [--days=N] [--dry-run] [--force]",
    "Share of \"%s\" ends soon": "\"%s\" 공유가 곧 종료됩니다",
    "Your share of \"%1$s\" ends automatically on %2$s. The file itself is not deleted. If the share is still needed, share the file again afterwards.": "\"%1$s\" 공유가 %2$s에 자동으로 종료됩니다. 파일 자체는 삭제되지 않습니다. 공유가 계속 필요한 경우 나중에 다시 공유하세요."
},
    "nplurals=2; plural=(n != 1);"
);
