@extends('backends.master')
@section('contents')
    <!-- Content Wrapper. Contains page content -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('Edit Menu') }}</h1>
                </div>
            </div>
        </div>
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <!-- left column -->
                <div class="col-md-12">
                    <form method="POST" action="{{ route('admin.menu.update', $menu->id) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <!-- general form elements -->
                        <div class="card card-primary">
                            <!-- /.card-header -->
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12">
                                        <ul class="nav nav-tabs" id="custom-content-below-tab" role="tablist">
                                            {{-- @dump($languages) --}}
                                            @foreach (json_decode($language, true) as $lang)
                                                @if ($lang['status'] == 1)
                                                    <li class="nav-item">
                                                        <a class="nav-link text-capitalize {{ $lang['code'] == $default_lang ? 'active' : '' }}"
                                                            id="lang_{{ $lang['code'] }}-tab" data-toggle="pill"
                                                            href="#lang_{{ $lang['code'] }}" data-lang="{{ $lang['code'] }}"
                                                            role="tab" aria-controls="lang_{{ $lang['code'] }}"
                                                            aria-selected="false">{{ \App\helpers\AppHelper::get_language_name($lang['code']) . '(' . strtoupper($lang['code']) . ')' }}</a>
                                                    </li>
                                                @endif
                                            @endforeach

                                        </ul>
                                        <div class="tab-content" id="custom-content-below-tabContent">
                                            @foreach (json_decode($language, true) as $lang)
                                                @if ($lang['status'] == 1)
                                                    <?php
                                                    if (count($menu['translations'])) {
                                                        $translate = [];
                                                        foreach ($menu['translations'] as $t) {
                                                            if ($t->locale == $lang['code'] && $t->key == 'title') {
                                                                $translate[$lang['code']]['title'] = $t->value;
                                                            }
                                                            if ($t->locale == $lang['code'] && $t->key == 'description') {
                                                                $translate[$lang['code']]['description'] = $t->value;
                                                            }
                                                        }
                                                    }
                                                    ?>
                                                    <div class="tab-pane fade {{ $lang['code'] == $default_lang ? 'show active' : '' }} mt-3"
                                                        id="lang_{{ $lang['code'] }}" role="tabpanel"
                                                        aria-labelledby="lang_{{ $lang['code'] }}-tab">
                                                        <div class="row">
                                                            <div class="form-group col-md-12">
                                                                <input type="hidden" name="lang[]"
                                                                    value="{{ $lang['code'] }}">
                                                                <label
                                                                    for="title_{{ $lang['code'] }}">{{ __('Title') }}({{ strtoupper($lang['code']) }})</label>
                                                                <input type="text" id="title_{{ $lang['code'] }}"
                                                                    class="form-control @error('title') is-invalid @enderror"
                                                                    name="title[]" placeholder="{{ __('Enter title') }}"
                                                                    value="{{ $translate[$lang['code']]['title'] ?? $menu['title'] }}">

                                                                @error('title')
                                                                    <span class="invalid-feedback" role="alert">
                                                                        <strong>{{ $message }}</strong>
                                                                    </span>
                                                                @enderror
                                                            </div>
                                                            <div class="form-group col-md-12">

                                                                <label
                                                                    for="description_{{ $lang['code'] }}">{{ __('Description') }}({{ strtoupper($lang['code']) }})</label>
                                                                <textarea type="text" id="description_{{ $lang['code'] }}"
                                                                    class="form-control @error('description') is-invalid @enderror"
                                                                    name="description[]"
                                                                    placeholder="{{ __('Enter Description') }}"
                                                                    value="">{{ $translate[$lang['code']]['description'] ?? $menu['description'] }}</textarea>

                                                                @error('description')
                                                                    <span class="invalid-feedback" role="alert">
                                                                        <strong>{{ $message }}</strong>
                                                                    </span>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card no_translate_wrapper">
                            <div class="card-header">
                                <h3 class="card-title">{{ __('General Info') }}</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">


                                    <div class="form-group col-md-6 ">
                                        <label class="required_lable" for="sort_order">{{ __('Sort_Order') }}</label>
                                        <input type="number" name="sort_order" id="sort_order" min="1"
                                            max="100" class="form-control @error('sort_order') is-invalid @enderror"
                                            step="any" value="{{ old('sort_order', $menu->sort_order) }}">
                                        @error('sort_order')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                    <div class="col-md-10"></div>
                                    <div class="form-group col-md-6">
                                        <div class="form-group">
                                            <label for="exampleInputFile">{{ __('Icon') }}</label>
                                            <div class="input-group">
                                                <div class="custom-file">
                                                    <input type="file" class="custom-file-input icon-file-input"
                                                        id="exampleInputFile" name="icon">
                                                    <label class="custom-file-label"
                                                        for="exampleInputFile">{{ $menu->icon ?? __('Choose Icon') }}</label>
                                                </div>
                                            </div>
                                            <div class="preview text-center border rounded mt-2" style="height: 150px">
                                                <img src="
                                                @if ($menu->icon && file_exists(public_path('uploads/menus/' . $menu->icon))) {{ asset('uploads/menus/' . $menu->icon) }}
                                                @else
                                                    {{ asset('uploads/image/default.png') }} @endif
                                                "
                                                    alt="" height="100%">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <div class="form-group">
                                            <label for="exampleInputFile">{{ __('Image') }}</label>
                                            <div class="input-group">
                                                <div class="custom-file">
                                                    <input type="file" class="custom-file-input image-file-input"
                                                        id="exampleInputFile" name="image">
                                                    <label class="custom-file-label"
                                                        for="exampleInputFile">{{ $menu->image ?? __('Choose Image') }}</label>
                                                </div>
                                            </div>
                                            <div class="preview text-center border rounded mt-2" style="height: 150px">
                                                <img src="
                                                @if ($menu->image && file_exists(public_path('uploads/menus/' . $menu->image))) {{ asset('uploads/menus/' . $menu->image) }}
                                                @else
                                                    {{ asset('uploads/image/default.png') }} @endif
                                                "
                                                    alt="" height="100%">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 form-group">
                                <button type="submit" class="btn btn-primary float-right">
                                    <i class="fa fa-save"></i>
                                    {{ __('Save') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <!-- /.row -->
        </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
@endsection

@push('js')
    <script>
        $('.custom-file-input').change(function(e) {
            var reader = new FileReader();
            var preview = $(this).closest('.form-group').find('.preview img');
            reader.onload = function(e) {
                preview.attr('src', e.target.result).show();
            }
            reader.readAsDataURL(this.files[0]);
        });

        $(document).on('click', '.nav-tabs .nav-link', function(e) {
            if ($(this).data('lang') != 'en') {
                $('.no_translate_wrapper').addClass('d-none');
            } else {
                $('.no_translate_wrapper').removeClass('d-none');
            }
        });
    </script>
@endpush
