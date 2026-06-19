<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Client;
use Illuminate\Auth\Access\HandlesAuthorization;

class ClientPolicy
{
    use HandlesAuthorization;

    /**
     * تحديد ما إذا كان المستخدم يمكنه عرض أي عميل.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        // يسمح للمدير بعرض جميع العملاء.
        return $user->role === 'admin';
    }

    /**
     * تحديد ما إذا كان المستخدم يمكنه عرض العميل المحدد.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Client  $client
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Client $client)
    {
        // يسمح للمدير بعرض العميل المحدد.
        // يمكن إضافة منطق آخر هنا للسماح لمالك العميل أو الوكيل المسؤول بعرضه.
        return $user->role === 'admin';
    }

    /**
     * تحديد ما إذا كان المستخدم يمكنه إنشاء عميل.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        // يسمح للمدير بإنشاء عميل جديد.
        return $user->role === 'admin';
    }

    /**
     * تحديد ما إذا كان المستخدم يمكنه تحديث العميل المحدد.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Client  $client
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Client $client)
    {
        // يسمح للمدير بتحديث العميل المحدد.
        // يمكن إضافة منطق آخر هنا للسماح لمالك العميل أو الوكيل المسؤول بتحديثه.
        return $user->role === 'admin';
    }

    /**
     * تحديد ما إذا كان المستخدم يمكنه حذف العميل المحدد.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Client  $client
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Client $client)
    {
        // يسمح للمدير بحذف العميل المحدد.
        return $user->role === 'admin';
    }
}
