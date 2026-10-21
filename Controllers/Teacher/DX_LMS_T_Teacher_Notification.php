<?php
/**
 * Kelas Pengontrol Notifikasi Pengajar (Teacher Notification Controller).
 */

require_once 'includes/DX_LMS_M_Db.php';

class DX_LMS_T_Teacher_Notification extends DX_LMS_M_Controller
{
    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $teacherUser = getPortalUser('teacher');
        if (!$teacherUser) {
            header('Location: ?route=/login');
            exit();
        }

        $teacherId = (int) ($teacherUser['employee_id'] ?? 2);

        $dbNotifs = dbQuery("
            SELECT notification_id, notification_title, notification_message, notification_type, is_read, created_at
            FROM DX_LMS_T_Notifications
            WHERE employee_id = ?
            ORDER BY notification_id DESC
        ", [$teacherId]);

        $formatted = [];
        if (!empty($dbNotifs)) {
            foreach ($dbNotifs as $n) {
                $isUnread = (int) $n['is_read'] === 0;
                $type = strtolower($n['notification_type'] ?? 'info');
                $icon = 'notifications';
                $color = 'bg-primary/10 text-primary';

                if ($type === 'assignment') {
                    $icon = 'campaign';
                    $color = 'bg-red-50 text-red-600';
                } elseif ($type === 'approval' || $type === 'success') {
                    $icon = 'verified';
                    $color = 'bg-emerald-50 text-emerald-600';
                } elseif ($type === 'warning' || $type === 'rejected') {
                    $icon = 'warning';
                    $color = 'bg-amber-50 text-amber-600';
                }

                $formatted[] = [
                    'id' => $n['notification_id'],
                    'title' => $n['notification_title'],
                    'message' => $n['notification_message'],
                    'time' => timeAgo($n['created_at']),
                    'is_unread' => $isUnread,
                    'icon' => $icon,
                    'color_class' => $color,
                    'link' => '?route=/teacher',
                ];
            }
        }

        // Tandai sudah dibaca saat membuka halaman notifikasi
        try {
            dbExecute("UPDATE DX_LMS_T_Notifications SET is_read = 1 WHERE employee_id = ? AND is_read = 0", [$teacherId]);
        } catch (\Throwable $e) {
            // Abaikan jika error
        }

        require_once 'includes/DX_LMS_M_Teacher_MockData.php';
        $mockNotifications = DX_LMS_M_Teacher_MockData::getNotifications();
        $notifications = array_merge($formatted, $mockNotifications);

        $data = [
            'active_tab' => 'notifikasi',
            'base_path' => '',
            'user' => $teacherUser,
            'notifications' => $notifications,
            'notif_count' => 0,
        ];

        $this->view('DX_LMS_T_Teacher_Notification', $data);
    }
}
