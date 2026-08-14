OC.L10N.register(
    "sharecleanup",
    {
    "Share Cleanup": "Dọn dẹp chia sẻ",
    "Automatically ends shares older than the configured number of days. Shared files are tagged with the end date of the share, and the sharing user is notified at 90 % of the lifetime. Shares with their own expiration date are never ended. The files themselves are never deleted.": "Tự động kết thúc chia sẻ cũ hơn số ngày đã định cấu hình. Các tệp được chia sẻ được gắn thẻ với ngày kết thúc và người dùng chia sẻ sẽ được thông báo ở 90% thời gian sống. Các chia sẻ có ngày hết hạn riêng không bao giờ bị kết thúc. Bản thân các tệp không bị xóa.",
    "Maximum share age (days)": "Thời gian chia sẻ tối đa (ngày)",
    "Users are notified after {notify} days (90 %), the share ends after {total} days. Shares with their own expiration date are never ended.": "Người dùng được thông báo sau {notify} ngày (90%), chia sẻ kết thúc sau {total} ngày. Các chia sẻ có ngày hết hạn riêng không bao giờ bị kết thúc.",
    "Dry-run mode (only log, end nothing)": "Chế độ chạy thử (chỉ ghi nhật ký, không kết thúc gì)",
    "Enabled by default. Test with \"occ sharecleanup:run --dry-run\" and check the log, then disable.": "Được bật theo mặc định. Kiểm tra bằng \"occ sharecleanup:run --dry-run\" và kiểm tra nhật ký, sau đó tắt.",
    "Save": "Lưu",
    "Settings saved.": "Đã lưu cài đặt.",
    "Error saving settings: ": "Lỗi khi lưu cài đặt: ",
    "Please enter a number of days (minimum 1).": "Vui lòng nhập số ngày (tối thiểu 1).",
    "Manual run: occ sharecleanup:run [--days=N] [--dry-run] [--force]": "Chạy thủ công: occ sharecleanup:run [--days=N] [--dry-run] [--force]",
    "Share of \"%s\" ends soon": "Chia sẻ \"%s\" sẽ sớm kết thúc",
    "Your share of \"%1$s\" ends automatically on %2$s. The file itself is not deleted. If the share is still needed, share the file again afterwards.": "Chia sẻ của bạn về \"%1$s\" sẽ tự động kết thúc vào %2$s. Bản thân tệp không bị xóa. Nếu vẫn cần chia sẻ, hãy chia sẻ lại tệp sau đó."
},
    "nplurals=2; plural=(n != 1);"
);
