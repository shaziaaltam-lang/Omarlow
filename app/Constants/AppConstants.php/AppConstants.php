<?php

namespace App\Constants;

final class AppConstants
{
    /**
     * الحد الافتراضي لعدد العناصر في صفحة الترقيم.
     */
    public const DEFAULT_PAGINATION_LIMIT = 15;

    /**
     * حالة القضية: قيد الانتظار.
     */
    public const CASE_STATUS_PENDING = 'pending';

    /**
     * حالة القضية: نشطة.
     */
    public const CASE_STATUS_ACTIVE = 'active';

    /**
     * دور المستخدم: المدير.
     */
    public const USER_ROLE_ADMIN = 'admin';
}