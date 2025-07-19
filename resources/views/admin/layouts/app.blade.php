<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'پنل مدیریت | سیستم قراردادهای پزشکی')</title>
    
    <!-- فونت‌های فارسی -->
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet" type="text/css" />
    
    <!-- CSS بوت‌استرپ نسخه RTL -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    
    <!-- CSS تایم‌لاین قرارداد -->
    <link rel="stylesheet" href="{{ asset('css/contract-timeline.css') }}">
    
    <!-- CSS سفارشی -->
    <style>
        :root {
            --sidebar-width: 250px;
        }
        
        body {
            font-family: 'Vazirmatn', Tahoma, Arial, sans-serif;
            background-color: #f8f9fa;
            direction: rtl;
        }
        
        .sidebar {
            position: fixed;
            top: 0;
            right: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background-color: #343a40;
            padding-top: 1rem;
            color: #fff;
        }
        
        .main-content {
            margin-right: var(--sidebar-width);
            padding: 20px;
        }
        
        .sidebar-header {
            padding: 0.5rem 1rem;
            border-bottom: 1px solid #495057;
            margin-bottom: 1rem;
        }
        
        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .sidebar-menu li {
            margin-bottom: 0.5rem;
        }
        
        .sidebar-menu a {
            display: block;
            padding: 0.75rem 1.5rem;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.2s;
        }
        
        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
            border-right: 3px solid #007bff;
        }
        
        .sidebar-menu a i {
            margin-left: 0.75rem;
        }
        
        .content-header {
            padding-bottom: 1rem;
            border-bottom: 1px solid #dee2e6;
            margin-bottom: 2rem;
        }
        
        .card {
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            margin-bottom: 1.5rem;
        }
        
        .card-header {
            background-color: rgba(0, 0, 0, 0.03);
            font-weight: bold;
        }
        
        .btn-actions {
            white-space: nowrap;
        }
        
        .table th {
            font-weight: bold;
        }
        
        .pagination {
            justify-content: center;
        }
        
        /* سازگاری موبایل */
        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(100%);
                transition: transform 0.3s ease;
                z-index: 1040;
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-right: 0;
            }
            
            .sidebar-toggler {
                display: block;
            }
        }
        
        .sidebar-toggler {
            display: none;
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 1050;
        }
        
        /* تنظیمات فرم‌ها */
        .form-label {
            font-weight: 600;
        }
        
        .required:after {
            content: " *";
            color: red;
        }
        
        /* نوتیفیکیشن */
        .notifications {
            position: fixed;
            top: 1rem;
            left: 1rem;
            z-index: 1060;
            width: 300px;
        }
        
        .notifications .alert {
            margin-bottom: 0.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
    </style>
    
    @yield('styles')
</head>
<body>
    <!-- دکمه تاگل منو در حالت موبایل -->
    <button class="btn btn-dark sidebar-toggler" type="button">
        <span class="navbar-toggler-icon"></span>
    </button>
    
    <!-- منوی کناری -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h4 class="mb-0">سیستم مدیریت</h4>
            <p class="small mb-0">قراردادهای پزشکی</p>
        </div>
        
        <ul class="sidebar-menu">
            <li>
                <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt"></i> داشبورد
                </a>
            </li>
            <li>
                <a href="{{ route('admin.medical-centers.index') }}" class="{{ request()->routeIs('admin.medical-centers.*') ? 'active' : '' }}">
                    <i class="fas fa-hospital"></i> مراکز درمانی
                </a>
            </li>
            <li>
                <a href="{{ route('admin.contracts.index') }}" class="{{ request()->routeIs('admin.contracts.*') ? 'active' : '' }}">
                    <i class="fas fa-file-contract"></i> قراردادها
                </a>
            </li>
            <li>
                <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <i class="fas fa-users"></i> کاربران
                </a>
            </li>
            <li>
                <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="fas fa-sign-out-alt"></i> خروج
                </a>
            </li>
        </ul>
        
        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
            @csrf
        </form>
    </div>
    
    <!-- محتوای اصلی -->
    <div class="main-content">
        <div class="content-header">
            <div class="row align-items-center">
                <div class="col">
                    <h2 class="mb-0">@yield('page-title')</h2>
                </div>
                <div class="col-auto">
                    @yield('page-actions')
                </div>
            </div>
        </div>
        
        <!-- نوتیفیکیشن‌ها -->
        <div class="notifications">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="بستن"></button>
                </div>
            @endif
            
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="بستن"></button>
                </div>
            @endif
        </div>
        
        <div class="content-body">
            @yield('content')
        </div>
    </div>
    
    <!-- JavaScript بوت‌استرپ -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- فونت آوسام -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    
    <!-- اسکریپت سفارشی -->
    <script>
        // تاگل منو در حالت موبایل
        document.querySelector('.sidebar-toggler')?.addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('show');
        });
        
        // بستن اتوماتیک نوتیفیکیشن‌ها
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
    
    @yield('scripts')
</body>
</html>
