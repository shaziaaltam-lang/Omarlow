import axios from 'axios';
import { createApp } from 'vue';

// 1. تهيئة وإعداد مكتبة Axios لإجراء طلبات HTTP آمنة وسلسة
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// استخراج رمز الحماية CSRF Token وتضمينه في ترويسات الطلبات الافتراضية
const csrfToken = document.head.querySelector('meta[name="csrf-token"]');
if (csrfToken) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken.content;
} else {
    console.error('تحذير: لم يتم العثور على رمز الحماية CSRF token. يرجى إدراج عنصر meta باسم csrf-token في واجهة الـ HTML الخاصة بك.');
}

// 2. إنشاء وتأسيس تطبيق Vue 3
const app = createApp({});

// 3. التسجيل التلقائي الذكي لكافة مكونات Vue الفردية (.vue)
// نقوم بمسح مجلد components بشكل ديناميكي لتسجيل المكونات تلقائياً باسم الملف
const components = import.meta.glob('./components/**/*.vue', { eager: true });

Object.entries(components).forEach(([path, definition]) => {
    // استخلاص اسم المكون من مسار الملف الأساسي
    // مثال: './components/ExampleComponent.vue' -> 'ExampleComponent'
    const componentName = path
        .split('/')
        .pop()
        .replace(/\.\w+$/, '');

    // تسجيل المكون عالمياً في كائن تطبيق Vue
    app.component(componentName, definition.default);
});

// 4. ربط وتثبيت تطبيق Vue في الصفحة البرمجية بـ DOM على المعرّف #app
const appElement = document.getElementById('app');
if (appElement) {
    app.mount('#app');
} else {
    console.warn('تنبيه: لم يتم العثور على عنصر DOM بالمعرّف #app، يرجى التحقق من تخطيط الصفحة Blade للتأكد من وجود العنصر المستهدف.');
}