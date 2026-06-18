<nav class="sidebar bg-light">
    <div class="sticky-top">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                    <i class="fas fa-home"></i> لوحة التحكم
                </a>
            </li>
            
            @if(auth()->user()->hasRole('admin'))
            <li class="nav-item">
                <a class="nav-link" href="#">
                    <i class="fas fa-users"></i> المستخدمون
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">
                    <i class="fas fa-lock"></i> الأدوار والصلاحيات
                </a>
            </li>
            @endif
            
            @if(auth()->user()->hasRole('lawyer'))
            <li class="nav-item">
                <a class="nav-link" href="#">
                    <i class="fas fa-briefcase"></i> الحالات
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">
                    <i class="fas fa-file"></i> المستندات
                </a>
            </li>
            @endif
            
            <li class="nav-item">
                <a class="nav-link" href="#">
                    <i class="fas fa-users-cog"></i> العملاء
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">
                    <i class="fas fa-file-invoice-dollar"></i> الفواتير
                </a>
            </li>
        </ul>
    </div>
</nav>
