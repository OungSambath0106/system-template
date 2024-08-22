@push('css')
    <style>


    </style>
@endpush

<aside class="main-sidebar elevation-4 sidebar-light-info" style="">
    <!-- Brand Logo -->
    <a href="{{ route('admin.dashboard') }}" class="brand-link d-flex row justify-content-center align-items-center"
        style="">
        <img src="@if (session()->has('app_logo') && file_exists('uploads/business_settings/' . session()->get('app_logo'))) {{ asset('uploads/business_settings/' . session()->get('app_logo')) }} @else {{ asset('uploads/image/default.png') }} @endif"
            alt="AdminLTE Logo" class="brand-image pl-2 ml-2"
            style="width: 100%; object-fit: contain; margin-left: 0; height: 60px; max-height: 60px;">
        <span class="brand-text font-weight pl-2 ml-0 mt-2">{{ session()->get('app_name') }}</span>
    </a>



    <!-- Sidebar -->
    <div class="sidebar os-theme-dark">
        <div class="user-panel pb-3 mb-3 d-flex">
            <div class="image">
                {{-- <img src="{{ Session::get('current_user')->profile_photo_path }}" class="img-circle elevation-2" alt="User Image"> --}}
            </div>
            <div class="info">
                {{-- <a href="#" class="d-block">{{ Session::get('current_user')->name }}</a> --}}
            </div>
        </div>
        <!-- SidebarSearch Form -->
        {{-- <div class="form-inline">
        <div class="input-group" data-widget="sidebar-search">
          <input style="background-color: #012e5a;" class="form-control form-control-sidebar" type="search" placeholder="Search" aria-label="Search">
          <div class="input-group-append">
            <button class="btn btn-sidebar">
              <i class="fas fa-search fa-fw"></i>
            </button>
          </div>
        </div>
      </div> --}}

        <!-- Sidebar Menu -->
        <nav class="mt-4">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                data-accordion="false">
                <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->
                <li class="nav-item mt-4">
                    <a href="{{ route('admin.dashboard') }}"
                        class="nav-link @if (request()->routeIs('admin.dashboard')) active @endif">
                        <i class="nav-icon fas fa-home"></i>
                        <p>
                            {{ __('Dashboard') }}
                        </p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('admin.user.index') }}"
                        class="nav-link @if (request()->routeIs('admin.user*')) active @endif">
                        <i class="nav-icon fas fa-user-alt"></i>
                        <p>
                            {{ __('User') }}
                        </p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('admin.department.index') }}"
                        class="nav-link @if (request()->routeIs('admin.department*')) active @endif">
                        <i class="nav-icon fas fa-building"></i>
                        <p>
                            {{ __('Department') }}
                        </p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('admin.grade.index') }}"
                        class="nav-link @if (request()->routeIs('admin.grade*')) active @endif">
                        <i class="nav-icon fas fa-graduation-cap"></i>
                        <p>
                            {{ __('Grade') }}
                        </p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('admin.student.index') }}"
                        class="nav-link @if (request()->routeIs('admin.student*')) active @endif">
                        <i class="nav-icon fas fa-clipboard"></i>
                        <p>
                            {{ __('Student Report') }}
                        </p>
                    </a>
                </li>

                <!--<li class="nav-item @if (request()->routeIs('admin.product*')) menu-is-opening menu-open @endif">
                    {{-- menu-open --}}
                    <a href="#" class="nav-link @if (request()->routeIs('admin.product*')) active @endif">
                        <i class="nav-icon fa fa-boxes"></i>
                        <p>
                            {{ __('Product Setup') }}
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('admin.product.index') }}"
                                class="nav-link @if (request()->routeIs('admin.product.*')) active @endif">
                                <i class="fa-solid fa-circle nav-icon"></i>
                                <p>{{ __('Product') }}</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.product-category.index') }}"
                                class="nav-link @if (request()->routeIs('admin.product-category*')) active @endif">
                                <i class="fa-solid fa-circle nav-icon"></i>
                                <p>{{ __('Category') }}</p>
                            </a>
                        </li>
                    </ul>
                </li>-->
                <li class="nav-item @if (request()->routeIs('admin.course-category*')) menu-is-opening menu-open @endif">
                    {{-- menu-open --}}
                    <a href="#" class="nav-link @if (request()->routeIs('admin.course-category*')) active @endif">
                        <i class="nav-icon fa-solid fa-layer-group"></i>
                        <p>
                            {{ __('Course') }}
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('admin.course-category.index') }}"
                                class="nav-link @if (request()->routeIs('admin.course-category*')) active @endif">
                                <i class="fa-solid fa-circle nav-icon"></i>
                                <p>{{ __('Categories') }}</p>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.news.index') }}"
                        class="nav-link @if (request()->routeIs('admin.news*')) active @endif">
                        <i class="nav-icon fa-solid fa-calendar-days"></i>
                        <p>
                            {{ __('Event&News') }}
                        </p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.menu.index') }}"
                        class="nav-link @if (request()->routeIs('admin.menu*')) active @endif">
                        <i class="nav-icon fa-solid fa-list"></i>
                        <p>
                            {{ __('App Menu') }}
                        </p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.recruitment.index') }}"
                        class="nav-link @if (request()->routeIs('admin.recruitment*')) active @endif">
                        <i class="nav-icon fa-solid fa-users"></i>
                        <p>
                            {{ __('Recruitment') }}
                        </p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.promotion.index') }}"
                        class="nav-link @if (request()->routeIs('admin.promotion*')) active @endif">
                        <i class="nav-icon fa-solid fa-percent"></i>
                        <p>
                            {{ __('Promotion') }}
                        </p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.onboard.index') }}"
                        class="nav-link @if (request()->routeIs('admin.onboard*')) active @endif">
                        <i class="nav-icon fa-solid fa-keyboard"></i>
                        <p>
                            {{ __('Onboard Screen') }}
                        </p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('admin.compus.index') }}"
                        class="nav-link @if (request()->routeIs('admin.compus*')) active @endif">
                        <i class="nav-icon fas fa-map-marker"></i>
                        <p>
                            {{ __('Campus') }}
                        </p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('admin.baner-slider.index') }}"
                        class="nav-link @if (request()->routeIs('admin.baner-slider*')) active @endif">
                        <i class="nav-icon fas fa-ad"></i>
                        <p>
                            {{ __('Baner Slider') }}
                        </p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('admin.setting.index') }}"
                        class="nav-link @if (request()->routeIs('admin.setting*')) active @endif">
                        <i class="nav-icon fas fa-cog"></i>
                        <p>
                            {{ __('Setting') }}
                        </p>
                    </a>
                </li>

            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>
