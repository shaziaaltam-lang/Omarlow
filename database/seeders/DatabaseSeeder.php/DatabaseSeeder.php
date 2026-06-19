<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Client;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. استدعاء بذور التهيئة الأساسية للنظام بالتمرير المتسلسل
        $this->call([
            RolesAndPermissionsSeeder::class,
            CaseTypesSeeder::class,
        ]);

        // 2. إنشاء مستخدم تجريبي افتراضي للإدارة وتسجيل الدخول الفوري
        $adminUser = User::factory()->create([
            'name' => 'المشرف العام',
            'email' => 'admin@system.com',
        ]);

        // 3. توليد مجموعة إضافية من المستخدمين (المحامين أو الموظفين)
        $users = User::factory()->count(10)->create();

        // دمج حساب المدير مع بقية المستخدمين لتوزيع العملاء عليهم بالتساوي
        $allStaff = collect([$adminUser])->concat($users);

        // 4. توليد عملاء تجريبيين وربطهم بالمستخدمين لضمان العلاقات الثنائية
        $allStaff->each(function ($user) {
            Client::factory()->count(5)->create([
                'user_id' => $user->id,
            ]);
        });
    }
}