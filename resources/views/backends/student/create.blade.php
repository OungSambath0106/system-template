@extends('backends.master')
@section('contents')
    <!-- Content Wrapper. Contains page content -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('Add New Student Report') }}</h1>
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
                    <form method="POST" action="{{ route('admin.student.store') }}" enctype="multipart/form-data">
                        @csrf
                        <!-- general form elements -->
                        <div class="card card-primary">
                            <!-- /.card-header -->
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12">
                                        <ul class="nav nav-tabs" id="custom-content-below-tab" role="tablist">
                                            {{-- @dump($language) --}}
                                            @foreach (json_decode($language, true) as $lang)
                                                @if ($lang['status'] == 1)
                                                    <li class="nav-item">
                                                        <a class="nav-link text-capitalize {{ $lang['code'] == $default_lang ? 'active' : '' }}"
                                                            id="lang_{{ $lang['code'] }}-tab" data-toggle="pill"
                                                            href="#lang_{{ $lang['code'] }}" data-lang="{{ $lang['code'] }}"
                                                            role="tab" aria-controls="lang_{{ $lang['code'] }}"
                                                            aria-selected="false">{{ \App\helpers\AppHelper::get_language_name($lang['name']) . '(' . strtoupper($lang['code']) . ')' }}</a>
                                                    </li>
                                                @endif
                                            @endforeach

                                        </ul>
                                        <div class="tab-content" id="custom-content-below-tabContent">
                                            @foreach (json_decode($language, true) as $key => $lang)
                                                @if ($lang['status'] == 1)
                                                    <div class="tab-pane row fade {{ $lang['code'] == $default_lang ? 'show active' : '' }} mt-3"
                                                        id="lang_{{ $lang['code'] }}" role="tabpanel"
                                                        aria-labelledby="lang_{{ $lang['code'] }}-tab">
                                                        <div class="form-group col-md-12">
                                                            <input type="hidden" name="lang[]"
                                                                value="{{ $lang['code'] }}">
                                                            <label
                                                                for="title_{{ $lang['code'] }}">{{ __('Title') }}({{ strtoupper($lang['code']) }})</label>
                                                            <input type="title" id="title_{{ $lang['code'] }}"
                                                                class="form-control @error('title') is-invalid @enderror"
                                                                name="title[]" placeholder="{{ __('Enter Title') }}"
                                                                value="">
                                                            @error('title')
                                                                <span class="invalid-feedback" role="alert">
                                                                    <strong>{{ $message }}</strong>
                                                                </span>
                                                            @enderror
                                                        </div>
                                                        <div class="form-group col-md-12">
                                                            <label
                                                                for="description_{{ $lang['code'] }}">{{ __('Description') }}({{ strtoupper($lang['code']) }})</label>
                                                            <textarea id="description_{{ $lang['code'] }}" class="form-control @error('description') is-invalid @enderror"
                                                                name="description[]" rows="2" value=""></textarea>
                                                            @error('description')
                                                                <span class="invalid-feedback" role="alert">
                                                                    <strong>{{ $message }}</strong>
                                                                </span>
                                                            @enderror
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

                                    <div class="form-group col-md-6">
                                        <label class="required_lable" for="department">{{ __('Department') }}</label>
                                        <select name="department" id="department"
                                            class="form-control select2 @error('department') is-invalid @enderror">
                                            <option value="">{{ __('Select Department') }}</option>
                                            @foreach ($departments as $department)
                                                <option value="{{ $department->id }}">{{ $department->name }} |
                                                    {{ $department->compus->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('department')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label class="required_lable" for="grade">{{ __('Grade') }}</label>
                                        <select name="grade" id="grade"
                                            class="form-control select2 @error('grade') is-invalid @enderror">
                                            <option value="">{{ __('Select grade') }}</option>
                                            @foreach ($grades as $id => $name)
                                                <option value="{{ $id }}">{{ $name }}</option>
                                            @endforeach
                                        </select>
                                        @error('grade')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>

                                    <div class="form-group col-md-6">
                                        <div class="form-group">
                                            <label for="exampleInputFile">{{ __('Image') }}</label>
                                            <div class="input-group">
                                                <div class="custom-file">
                                                    <input type="hidden" name="image" class="image_names_hidden">
                                                    <input type="file" class="custom-file-input" id="exampleInputFile"
                                                        name="image" accept="image/png, image/jpeg">
                                                    <label class="custom-file-label"
                                                        for="exampleInputFile">{{ __('Choose file') }}</label>
                                                </div>
                                            </div>
                                            <div class="preview preview-multiple text-center border rounded mt-2"
                                                style="height: 150px">
                                                <img src="{{ asset('uploads/image/default.png') }}" alt=""
                                                    height="100%">
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
        const compressor = new window.Compress();
        $('.custom-file-input').change(function(e) {
            compressor.compress([...e.target.files], {
                size: 4,
                quality: 0.75,
            }).then((output) => {
                var files = Compress.convertBase64ToFile(output[0].data, output[0].ext);
                var formData = new FormData();

                var image_names_hidden = $(this).closest('.custom-file').find('input[type=hidden]');
                var container = $(this).closest('.form-group').find('.preview');
                if (container.find('img').attr('src') === `{{ asset('uploads/image/default.png') }}`) {
                    container.empty();
                }
                formData.append('image', files);

                $.ajax({
                    url: "{{ route('save_temp_file') }}",
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        console.log(response);
                        if (response.status == 0) {
                            toastr.error(response.msg);
                        }
                        if (response.status == 1) {
                            container.empty();
                            var temp_file = response.temp_files;
                            var img_container = $('<div></div>').addClass('img_container');
                            var img = $('<img>').attr('src', "{{ asset('uploads/temp') }}" +
                                '/' + temp_file);
                            img_container.append(img);
                            container.append(img_container);

                            var new_file_name = temp_file;
                            console.log(new_file_name);

                            image_names_hidden.val(new_file_name);
                        }
                    }
                });
            });
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
