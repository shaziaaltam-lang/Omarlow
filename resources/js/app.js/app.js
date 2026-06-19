import { createApp } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';

// 1. تهيئة وإعداد Axios للتعامل الآمن مع الواجهات الخلفية لـ Laravel
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// جلب رمز الحماية CSRF Token من ترويسة HTML وتضمينه في كافة طلبات Axios تلقائياً
const csrfToken = document.querySelector('meta[name="csrf-token"]');
if (csrfToken) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken.content;
} else {
    console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
}

// 2. إتاحة مكتبة SweetAlert2 على النطاق العام لتسهيل الاستخدام في صفحات Blade
window.Swal = Swal;

// دالة مساعدة عامة لإظهار تنبيهات التغذية الراجعة السريعة (Toasts)
const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer);
        toast.addEventListener('mouseleave', Swal.resumeTimer);
    }
});
window.Toast = Toast;

// 3. ربط وتفعيل منبثقات التأكيد لعمليات الحذف في جداول النظام (قضايا، فواتير، عملاء)
document.addEventListener('DOMContentLoaded', () => {
    // استهداف كافة النماذج التي تتطلب تأكيداً قبل الحذف والوقاية من الفقدان العشوائي للبيانات
    document.body.addEventListener('click', function (e) {
        const button = e.target.closest('.delete-confirm-btn');
        if (button) {
            e.preventDefault();
            const form = button.closest('form');
            const entityName = button.getAttribute('data-entity') || 'هذا السجل';
            
            Swal.fire({
                title: 'هل أنت متأكد؟',
                text: `لن تتمكن من استعادة بيانات (${entityName}) بعد إتمام هذه العملية!`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'نعم، احذف الآن!',
                cancelButtonText: 'إلغاء',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }
    });
});

// 4. بناء وتهيئة تطبيق Vue 3 وإرفاق المكونات التفاعلية
const app = createApp({
    data() {
        return {
            appName: 'نظام إدارة مكتب المحاماة والخدمات القانونية',
            isSidebarOpen: true
        };
    },
    methods: {
        toggleSidebar() {
            this.isSidebarOpen = !this.isSidebarOpen;
        }
    }
});

// إضافة مكتبات الطرف الثالث كخصائص عالمية داخل مكونات Vue
app.config.globalProperties.$http = axios;
app.config.globalProperties.$swal = Swal;
app.config.globalProperties.$toast = Toast;

// 5. تسجيل المكونات التفاعلية لـ Vue (مثال لمكونات متكاملة مع النظام)

// مكوّن لعرض إحصائيات سريعة وتحديثها تلقائياً ديناميكياً
app.component('DashboardStatsCounter', {
    props: ['initialValue', 'endpoint', 'title'],
    data() {
        return {
            currentValue: this.initialValue || 0
        };
    },
    mounted() {
        if (this.endpoint) {
            this.fetchStats();
            // تحديث دوري للإحصائيات كل دقيقة
            setInterval(this.fetchStats, 60000);
        }
    },
    methods: {
        fetchStats() {
            axios.get(this.endpoint)
                .then(response => {
                    this.currentValue = response.data.count || response.data.total || 0;
                })
                .catch(error => console.error('Error fetching statistics:', error));
        }
    },
    template: `
        <div class="p-6 bg-white rounded-lg shadow-sm border border-gray-100 flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">{{ title }}</p>
                <h3 class="text-2xl font-bold text-gray-900 mt-1">{{ currentValue }}</h3>
            </div>
            <div class="p-3 bg-indigo-50 text-indigo-600 rounded-full">
                <slot name="icon">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </slot>
            </div>
        </div>
    `
});

// مكوّن جرس الإشعارات لعرض الإشعارات اللحظية وحالتها
app.component('NotificationBell', {
    props: ['unreadCountUrl', 'markAsReadUrl'],
    data() {
        return {
            unreadCount: 0,
            showDropdown: false,
            notifications: []
        };
    },
    mounted() {
        this.getUnreadCount();
    },
    methods: {
        getUnreadCount() {
            if (!this.unreadCountUrl) return;
            axios.get(this.unreadCountUrl)
                .then(res => {
                    this.unreadCount = res.data.unread_count || 0;
                    this.notifications = res.data.notifications || [];
                });
        },
        toggleDropdown() {
            this.showDropdown = !this.showDropdown;
            if (this.showDropdown && this.unreadCount > 0 && this.markAsReadUrl) {
                axios.post(this.markAsReadUrl).then(() => {
                    this.unreadCount = 0;
                });
            }
        }
    },
    template: `
        <div class="relative inline-block text-left">
            <button @click="toggleDropdown" class="relative p-1 text-gray-400 hover:text-gray-500 focus:outline-none">
                <span class="sr-only">عرض الإشعارات</span>
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                <span v-if="unreadCount > 0" class="absolute top-0 right-0 block h-2.5 w-2.5 rounded-full bg-red-500 ring-2 ring-white"></span>
            </button>
            <div v-if="showDropdown" class="origin-top-right absolute left-0 mt-2 w-80 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 divide-y divide-gray-100 z-50">
                <div class="px-4 py-3">
                    <p class="text-sm leading-5 font-semibold text-gray-900">الإشعارات الأخيرة</p>
                </div>
                <div class="py-1 max-h-60 overflow-y-auto">
                    <p v-if="notifications.length === 0" class="text-xs text-gray-500 text-center py-4">لا توجد إشعارات حالياً</p>
                    <a v-for="notif in notifications" :key="notif.id" :href="notif.link || '#'" class="block px-4 py-3 hover:bg-gray-50">
                        <p class="text-xs text-gray-800">{{ notif.message }}</p>
                        <span class="text-[10px] text-gray-400">{{ notif.time_ago }}</span>
                    </a>
                </div>
            </div>
        </div>
    `
});

// 6. تثبيت وربط تطبيق Vue بصفحات الموقع التي تحتوي على المعرف الرئيسي
const appElement = document.getElementById('app');
if (appElement) {
    app.mount('#app');
}

console.log('Successfully initialized system assets, Vue 3, Axios, and SweetAlert2!');