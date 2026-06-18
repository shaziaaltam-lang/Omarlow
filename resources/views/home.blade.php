@extends('layouts.app')

@section('title', 'لوحة التحكم')

@section('content')
<div class="dashboard-container">
    <div class="row">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">الحالات</h5>
                    <h2 class="text-primary">12</h2>
                    <p class="text-muted">حالة قيد المتابعة</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">العملاء</h5>
                    <h2 class="text-info">25</h2>
                    <p class="text-muted">عميل نشط</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">الفواتير</h5>
                    <h2 class="text-warning">8</h2>
                    <p class="text-muted">فاتورة معلقة</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">المستندات</h5>
                    <h2 class="text-success">156</h2>
                    <p class="text-muted">مستند محفوظ</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5>آخر الحالات</h5>
                </div>
                <div class="card-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>رقم الحالة</th>
                                <th>العنوان</th>
                                <th>العميل</th>
                                <th>الحالة</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>#001</td>
                                <td>قضية تعويض</td>
                                <td>أحمد محمد</td>
                                <td><span class="badge bg-success">نشطة</span></td>
                            </tr>
                            <tr>
                                <td>#002</td>
                                <td>قضية إيجار</td>
                                <td>فاطمة علي</td>
                                <td><span class="badge bg-warning">قيد الاستئناف</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>الأنشطة الأخيرة</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <small class="text-muted">قبل ساعة</small>
                            <p>تم إنشاء حالة جديدة</p>
                        </li>
                        <li class="list-group-item">
                            <small class="text-muted">قبل 3 ساعات</small>
                            <p>تم رفع مستند جديد</p>
                        </li>
                        <li class="list-group-item">
                            <small class="text-muted">أمس</small>
                            <p>تم إنشاء فاتورة جديدة</p>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
