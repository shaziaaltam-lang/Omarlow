<header class="navbar navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="{{ route('dashboard') }}">
            <img src="{{ asset('assets/images/logo/logo.png') }}" alt="Omarlow" height="40">
            Omarlow
        </a>
        
        <div class="d-flex align-items-center">
            @include('partials.language-switcher')
            @include('partials.notifications')
            
            <div class="dropdown">
                <button class="btn btn-link dropdown-toggle text-white" type="button" data-bs-toggle="dropdown">
                    {{ auth()->user()->name }}
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="#">الملف الشخصي</a></li>
                    <li><a class="dropdown-item" href="#">الإعدادات</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item">تسجيل الخروج</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</header>
