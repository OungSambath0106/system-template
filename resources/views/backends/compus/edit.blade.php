@extends('backends.master')
@section('contents')
    <!-- Content Wrapper. Contains page content -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('Edit Campus') }}</h1>
                </div>
            </div>
        </div>
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <!-- /.card-header -->
                    <form method="POST" action="{{ route('admin.compus.update', $compus->id) }}"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
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
                                                    <?php
                                                    if (count($compus['translations'])) {
                                                        $translate = [];
                                                        foreach ($compus['translations'] as $t) {
                                                            if ($t->locale == $lang['code'] && $t->key == 'name') {
                                                                $translate[$lang['code']]['name'] = $t->value;
                                                            }
                                                            if ($t->locale == $lang['code'] && $t->key == 'description') {
                                                                $translate[$lang['code']]['description'] = $t->value;
                                                            }
                                                        }
                                                    }
                                                    ?>
                                                    <div class="tab-pane row fade {{ $lang['code'] == $default_lang ? 'show active' : '' }} mt-3"
                                                        id="lang_{{ $lang['code'] }}" role="tabpanel"
                                                        aria-labelledby="lang_{{ $lang['code'] }}-tab">
                                                        <div class="form-group col-md-12">
                                                            <input type="hidden" name="lang[]"
                                                                value="{{ $lang['code'] }}">
                                                            <label
                                                                for="name_{{ $lang['code'] }}">{{ __('Name') }}({{ strtoupper($lang['code']) }})</label>
                                                            <input type="name" id="name_{{ $lang['code'] }}"
                                                                class="form-control @error('name') is-invalid @enderror"
                                                                name="name[]" placeholder="{{ __('Enter Name') }}"
                                                                value="{{ $translate[$lang['code']]['name'] ?? $compus['name'] }}">
                                                            @error('name')
                                                                <span class="invalid-feedback" role="alert">
                                                                    <strong>{{ $message }}</strong>
                                                                </span>
                                                            @enderror
                                                        </div>
                                                        <div class="form-group col-md-12">
                                                            <label
                                                                for="description_{{ $lang['code'] }}">{{ __('Description') }}({{ strtoupper($lang['code']) }})</label>
                                                            <textarea id="description_{{ $lang['code'] }}" class="form-control @error('description') is-invalid @enderror"
                                                                name="description[]" rows="2">{{ $translate[$lang['code']]['description'] ?? $compus['description'] }}</textarea>
                                                            @error('description')
                                                                <span class="invalid-feedback" role="alert">
                                                                    <strong>{{ $message }}</strong>
                                                                </span>
                                                            @enderror
                                                        </div>
                                                        <div class="form-group col-md-12">
                                                            <label
                                                                for="address_{{ $lang['code'] }}">{{ __('Address') }}({{ strtoupper($lang['code']) }})</label>
                                                            <textarea id="address_{{ $lang['code'] }}" class="form-control @error('address') is-invalid @enderror" name="address[]"
                                                                rows="2">{{ $translate[$lang['code']]['address'] ?? $compus['address'] }}</textarea>
                                                            @error('address')
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

                                    <div class="form-group col-md-6 ">
                                        <label class="required_lable" for="email">{{ __('Email') }}</label>
                                        <input type="text" name="email" id="email"
                                            class="form-control @error('email') is-invalid @enderror" step="any"
                                            value="{{ old('email', $compus->email) }}">
                                        @error('email')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>

                                    {{-- <div class="form-group col-md-6 ">
                                        <label class="required_lable" for="telegram">{{ __('Telegram') }}</label>
                                        <input type="text" name="telegram" id="telegram"
                                            class="form-control @error('telegram') is-invalid @enderror" step="any"
                                            value="{{ old('telegram', $compus->telegram) }}">
                                        @error('telegram')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>

                                    <div class="form-group col-md-6 ">
                                        <label class="required_lable" for="telegram_url">{{ __('Telegram URL') }}</label>
                                        <input type="text" name="telegram_url" id="telegram_url"
                                            class="form-control @error('telegram_url') is-invalid @enderror"
                                            step="any" value="{{ old('telegram_url', $compus->telegram_url) }}">
                                        @error('telegram_url')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div> --}}

                                    <div class="form-group col-md-6 ">
                                        <label class="required_lable" for="facebook_name">{{ __('Facebook Name') }}</label>
                                        <input type="text" name="facebook_name" id="facebook_name"
                                            class="form-control @error('facebook_name') is-invalid @enderror" step="any"
                                            value="{{ old('facebook_name', $compus->facebook_name) }}">
                                        @error('facebook_name')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>

                                    <div class="form-group col-md-6 ">
                                        <label class="required_lable" for="facebook_url">{{ __('Facebook URL') }}</label>
                                        <input type="text" name="facebook_url" id="facebook_url"
                                            class="form-control @error('facebook_url') is-invalid @enderror" step="any"
                                            value="{{ old('facebook_url', $compus->facebook_url) }}">
                                        @error('facebook_url')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>

                                    <div class="form-group col-md-12 ">
                                        {{-- <label class="required_lable" for="phone">{{ __('Phone Number') }}</label>
                                        <input type="text" name="phone" id="phone"
                                            class="form-control @error('phone') is-invalid @enderror" step="any"
                                            value="{{ old('phone', $compus->phone) }}">
                                        @error('phone')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror --}}
                                        <table class="table table-bordered table-striped table-hover rowfy mb-0">
                                            <thead>
                                                <tr>
                                                    <th class="col-12">Phone Number</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if (isset($compus->phone) && is_array($compus->phone))
                                                    @foreach ($compus->phone as $phonenum)
                                                        <tr>
                                                            <td>
                                                                <input type="text" class="form-control" name="phone[]"
                                                                    value="{{ $phonenum }}">
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                @else
                                                    <tr>
                                                        <td>
                                                            <input type="text" class="form-control" name="phone[]">
                                                        </td>
                                                    </tr>
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="form-group col-md-12">
                                        <table class="table table-bordered table-striped table-hover rowfy mb-0">
                                            <thead>
                                                <tr>
                                                    <th class="col-6">Telegram Number</th>
                                                    <th class="col-6">Telegram URL</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if ($compus->telegram && is_array($compus->telegram))
                                                    @foreach ($compus->telegram as $key => $telegramNumber)
                                                        <tr>
                                                            <td>
                                                                <input type="text" class="form-control"
                                                                    name="telegrams[telegram_number][]"
                                                                    value="{{ $telegramNumber['telegram_number'] }}">
                                                            </td>
                                                            <td>
                                                                <input type="url" class="form-control"
                                                                    name="telegrams[telegram_url][]"
                                                                    value="{{ $telegramNumber['telegram_url'] ?? '' }}">
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                @else
                                                    <tr>
                                                        <td>
                                                            <input type="text" class="form-control"
                                                                name="telegrams[telegram_number][]">
                                                        </td>
                                                        <td>
                                                            <input type="url" class="form-control"
                                                                name="telegrams[telegram_url][]">
                                                        </td>
                                                    </tr>
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="form-group col-md-12 ">
                                        <label class="required_lable"
                                            for="google_map_url">{{ __('Google Map URL') }}</label>
                                        <textarea name="google_map_url" id="google_map_url"
                                            class="form-control @error('google_map_url') is-invalid @enderror" step="any" rows="2">{{ old('google_map_url', $compus->google_map_url) }}</textarea>
                                        @error('google_map_url')
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
                                                <img src="{{ asset('uploads/compus/' . $compus->image) }}" alt=""
                                                    height="100%">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <div class="form-group">
                                            <label for="exampleInputFile">{{ __('Admission_Image') }}</label>
                                            <div class="input-group">
                                                <div class="custom-file">
                                                    <input type="hidden" name="admission_image"
                                                        class="image_names_hidden">
                                                    <input type="file" class="custom-file-input" id="exampleInputFile"
                                                        name="admission_image" accept="image/png, image/jpeg">
                                                    <label class="custom-file-label"
                                                        for="exampleInputFile">{{ __('Choose file') }}</label>
                                                </div>
                                            </div>
                                            <div class="preview preview-multiple text-center border rounded mt-2"
                                                style="height: 150px">
                                                <img src="{{ asset('uploads/compus/' . $compus->admission_image) }}"
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
                <!-- /.card -->
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
    </script>
@endpush
