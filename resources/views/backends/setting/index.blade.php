@extends('backends.master')
@push('css')
    <style>
        .preview {
            margin-block: 12px;
            text-align: center;
        }

        .tab-pane {
            margin-top: 20px
        }
    </style>
@endpush
@section('contents')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h3>{{ __('Business Setting') }}</h3>
                </div>
                <div class="col-sm-6" style="text-align: right">
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="card-outline card-outline-tabs">
                <div class="card-header p-0 border-bottom-0">
                    @include('backends.setting.partials.tab')
                </div>
                <div class="">
                    <div class="tab-content" id="custom-tabs-four-tabContent">
                        <div class="tab-pane fade active show" id="custom-tabs-four-home" role="tabpanel"
                            aria-labelledby="custom-tabs-four-home-tab">
                            <form action="{{ route('admin.setting.update') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <div class="row">

                                    <div class="col-md-12">
                                        <div class="card">
                                            <div class="card-header">
                                                <h3 class="card-title">{{ __('Company Information') }}</h3>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-12 col-sm-6 col-md-4">
                                                        <div class="form-group">
                                                            <label for="company_name">{{ __('App Name') }}</label>
                                                            <input type="text" name="company_name" id="company_name"
                                                                class="form-control" value="{{ $company_name }}">
                                                        </div>
                                                    </div>
                                                    <div class="col-12 col-sm-6 col-md-4">
                                                        <div class="form-group">
                                                            <label for="phone">{{ __('Phone') }}</label>
                                                            <input type="text" name="phone" id="phone"
                                                                class="form-control" value="{{ $phone }}">
                                                        </div>
                                                    </div>
                                                    <div class="col-12 col-sm-6 col-md-4">
                                                        <div class="form-group">
                                                            <label for="">{{ __('Email') }}</label>
                                                            <input type="text" name="email" id="email"
                                                                class="form-control" value="{{ $email }}">
                                                        </div>
                                                    </div>
                                                    <div class="col-12 col-sm-6 col-md-4">
                                                        <div class="form-group">
                                                            <label for="company_address">{{ __('Company Address') }}</label>
                                                            <input type="text" name="company_address"
                                                                id="company_address" class="form-control"
                                                                value="{{ $company_address }}">
                                                        </div>
                                                    </div>
                                                    <div class="col-12 col-sm-6 col-md-4">
                                                        <div class="form-group">
                                                            <label for="copy_right_text">{{ __('Copyright Text') }}</label>
                                                            <input type="text" name="copy_right_text"
                                                                id="copy_right_text" class="form-control"
                                                                value="{{ $copy_right_text }}">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- <div class="card">
                                        <div class="card-header">
                                            <h3 class="card-title">{{ __('Bank Info') }}</h3>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="form-group col-md-4">
                                                    <label for="account_holder">{{ __('Account Holder') }}</label>
                                                    <input type="text" name="account_holder" id="account_holder" class="form-control" value="{{ $account_holder }}">
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label for="account_number">{{ __('Account Number') }}</label>
                                                    <input type="text" name="account_number" id="account_number" class="form-control" value="{{ $account_number }}">
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label for="bank">{{ __('Bank') }}</label>
                                                    <input type="text" name="bank" id="bank" class="form-control" value="{{ $bank }}">
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label for="swift_code">{{ __('Swift Code') }}</label>
                                                    <input type="text" name="swift_code" id="swift_code" class="form-control" value="{{ $swift_code }}">
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label for="bank_address">{{ __('Bank Address') }}</label>
                                                    <input type="text" name="bank_address" id="bank_address" class="form-control" value="{{ $bank_address }}">
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label for="account_holder_address">{{ __('Account Holder address') }}</label>
                                                    <textarea name="account_holder_address" id="account_holder_address" class="form-control" rows="3">{{ $account_holder_address }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div> --}}

                                        <div class="card">
                                            <div class="card-header">
                                                <h3 class="card-title">{{ __('Social Media') }}</h3>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <table class="table">
                                                        <thead>
                                                            <tr>
                                                                <th>{{ __('Title') }}</th>
                                                                <th>{{ __('Icon') }}</th>
                                                                <th>{{ __('Link') }}</th>
                                                                <th>{{ __('Status') }}</th>
                                                                <th>
                                                                    <button type="button"
                                                                        class="btn btn-success btn-sm btn_add">
                                                                        <i class="fa fa-plus-circle"></i>
                                                                    </button>
                                                                </th>
                                                            </tr>
                                                        </thead>
                                                        @include('backends.setting.partials._social_media_tbody')
                                                    </table>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card">
                                            <div class="card-header">
                                                <h3 class="card-title">{{ __('Website and system setup') }}</h3>
                                            </div>

                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-12">
                                                        @include('backends.setting.partials._lang_tab', [
                                                            'tab_id' => 'bank',
                                                        ])
                                                        <div class="tab-content" id="custom-content-below-tabContent">
                                                            @foreach (json_decode($language, true) as $key => $lang)
                                                                @if ($lang['status'] == 1)
                                                                    <?php
                                                                    $translate = [];
                                                                    foreach ($settings as $setting) {
                                                                        if (count($setting['translations'])) {
                                                                            // dd($setting['translations']);
                                                                            foreach ($setting['translations'] as $t) {
                                                                                // dd($t);
                                                                                if ($t->locale == $lang['code'] && $t->key == 'help_desk_description') {
                                                                                    $translate[$lang['code']]['help_desk_description'] = $t->value;
                                                                                }
                                                                            }
                                                                        }
                                                                    }
                                                                    ?>

                                                                    <div class="tab-pane fade {{ $lang['code'] == $default_lang ? 'show active' : '' }} mt-3"
                                                                        id="bank_lang_{{ $lang['code'] }}" role="tabpanel"
                                                                        aria-labelledby="lang_{{ $lang['code'] }}-tab">
                                                                        <input type="hidden" name="lang[]"
                                                                            value="{{ $lang['code'] }}">
                                                                        <div class="row">
                                                                            @if ($lang['code'] == 'en')
                                                                                <div class="col-12 col-sm-6 col-md-12">
                                                                                    <div class="form-group">
                                                                                        <label for="youtube_channel_url">
                                                                                            {{ __('Youtube channel url') }}
                                                                                        </label>
                                                                                        <textarea name="youtube_channel_url" id="youtube_channel_url" class="form-control" rows="2">{{ $youtube_channel_url }}</textarea>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="col-12 col-sm-6 col-md-4">
                                                                                    <div class="form-group">
                                                                                        <label
                                                                                            for="web_header_logo">{{ __('App logo') }}</label>
                                                                                        <div class="preview">
                                                                                            <img src="
                                                                                            @if ($web_header_logo && file_exists('uploads/business_settings/' . $web_header_logo)) {{ asset('uploads/business_settings/' . $web_header_logo) }}
                                                                                            @else
                                                                                                {{ asset('uploads/image/default.png') }} @endif
                                                                                            "
                                                                                                alt=""
                                                                                                height="120px">
                                                                                        </div>
                                                                                        <div class="custom-file">
                                                                                            <input type="file"
                                                                                                class="custom-file-input"
                                                                                                id="customFile"
                                                                                                name="web_header_logo">
                                                                                            <label
                                                                                                class="custom-file-label"
                                                                                                for="customFile">{{ __('Choose file') }}</label>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="col-12 col-sm-6 col-md-4">
                                                                                    <div class="form-group">
                                                                                        <label
                                                                                            for="fav_icon">{{ __('Fav icon') }}</label>
                                                                                        <div class="preview">
                                                                                            <img src="
                                                                                            @if ($fav_icon && file_exists('uploads/business_settings/' . $fav_icon)) {{ asset('uploads/business_settings/' . $fav_icon) }}
                                                                                            @else
                                                                                                {{ asset('uploads/image/default.png') }} @endif
                                                                                            "
                                                                                                alt=""
                                                                                                height="120px">
                                                                                        </div>
                                                                                        <div class="custom-file">
                                                                                            <input type="file"
                                                                                                class="custom-file-input"
                                                                                                id="customFile"
                                                                                                name="fav_icon">
                                                                                            <label
                                                                                                class="custom-file-label"
                                                                                                for="customFile">{{ __('Choose file') }}</label>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>

                                                                                <div class="col-12 col-sm-6 col-md-4">
                                                                                    <div class="form-group">
                                                                                        <label
                                                                                            for="help_desk_banner">{{ __('Help desk banner') }}</label>
                                                                                        <div class="preview">
                                                                                            <img src="
                                                                                            @if ($help_desk_banner && file_exists('uploads/business_settings/' . $help_desk_banner)) {{ asset('uploads/business_settings/' . $help_desk_banner) }}
                                                                                            @else
                                                                                                {{ asset('uploads/image/default.png') }} @endif
                                                                                            "
                                                                                                alt=""
                                                                                                height="120px">
                                                                                        </div>
                                                                                        <div class="custom-file">
                                                                                            <input type="file"
                                                                                                class="custom-file-input"
                                                                                                id="customFile"
                                                                                                name="help_desk_banner">
                                                                                            <label
                                                                                                class="custom-file-label"
                                                                                                for="customFile">{{ __('Choose file') }}</label>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            @endif
                                                                            <div class="form-group col-md-12">
                                                                                <label
                                                                                    for="help_desk_description_{{ $lang['code'] }}">{{ __('Help desk description') }}({{ strtoupper($lang['code']) }})</label>
                                                                                <textarea name="help_desk_description[]" id="help_desk_description_{{ $lang['code'] }}" class="form-control"
                                                                                    rows="3">{{ $translate[$lang['code']]['help_desk_description'] ?? $help_desk_description }}</textarea>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                @endif
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                                {{-- <div class="row">
                                                    <div class="col-6">
                                                        <div class="form-group">
                                                            <label for="web_header_logo">{{ __('App logo') }}</label>
                                                            <div class="preview">
                                                                <img src="
                                                            @if ($web_header_logo && file_exists('uploads/business_settings/' . $web_header_logo)) {{ asset('uploads/business_settings/' . $web_header_logo) }}
                                                            @else
                                                                {{ asset('uploads/image/default.png') }} @endif
                                                            "
                                                                    alt="" height="120px">
                                                            </div>
                                                            <div class="custom-file">
                                                                <input type="file" class="custom-file-input"
                                                                    id="customFile" name="web_header_logo">
                                                                <label class="custom-file-label"
                                                                    for="customFile">{{ __('Choose file') }}</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="form-group">
                                                            <label for="fav_icon">{{ __('Fav icon') }}</label>
                                                            <div class="preview">
                                                                <img src="
                                                            @if ($fav_icon && file_exists('uploads/business_settings/' . $fav_icon)) {{ asset('uploads/business_settings/' . $fav_icon) }}
                                                            @else
                                                                {{ asset('uploads/image/default.png') }} @endif
                                                            "
                                                                    alt="" height="120px">
                                                            </div>
                                                            <div class="custom-file">
                                                                <input type="file" class="custom-file-input"
                                                                    id="customFile" name="fav_icon">
                                                                <label class="custom-file-label"
                                                                    for="customFile">{{ __('Choose file') }}</label>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-6">
                                                        <div class="form-group">
                                                            <label for="contact_us_logo">{{ __('Contact us logo') }}</label>
                                                            <div class="preview">
                                                                <img src="
                                                            @if ($contact_us_logo && file_exists('uploads/business_settings/' . $contact_us_logo)) {{ asset('uploads/business_settings/' . $contact_us_logo) }}
                                                            @else
                                                                {{ asset('uploads/image/default.png') }} @endif
                                                            "
                                                                    alt="" height="120px">
                                                            </div>
                                                            <div class="custom-file">
                                                                <input type="file" class="custom-file-input"
                                                                    id="customFile" name="contact_us_logo">
                                                                <label class="custom-file-label"
                                                                    for="customFile">{{ __('Choose file') }}</label>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-6">
                                                        <div class="form-group">
                                                            <label
                                                                for="help_desk_banner">{{ __('Help desk banner') }}</label>
                                                            <div class="preview">
                                                                <img src="
                                                            @if ($help_desk_banner && file_exists('uploads/business_settings/' . $help_desk_banner)) {{ asset('uploads/business_settings/' . $help_desk_banner) }}
                                                            @else
                                                                {{ asset('uploads/image/default.png') }} @endif
                                                            "
                                                                    alt="" height="120px">
                                                            </div>
                                                            <div class="custom-file">
                                                                <input type="file" class="custom-file-input"
                                                                    id="customFile" name="help_desk_banner">
                                                                <label class="custom-file-label"
                                                                    for="customFile">{{ __('Choose file') }}</label>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-6">
                                                        <div class="form-group">
                                                            <label for="help_desk_description">
                                                                {{ __('Help desk description') }}
                                                            </label>
                                                            <textarea name="help_desk_description" id="help_desk_description" class="form-control" rows="7">{{ $help_desk_description }}</textarea>
                                                        </div>
                                                    </div>

                                                </div> --}}
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-12 mb-3">
                                                <button type="submit" class="btn btn-primary float-right">
                                                    <i class="fas fa-save"></i>
                                                    {{ __('Save') }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>

            </div>

        </div>
    </section>
    <div class="modal fade modal_form" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>
@endsection
@push('js')
    <script>
        $('.btn_add').click(function(e) {
            var tbody = $('.tbody');
            var numRows = tbody.find("tr").length;
            $.ajax({
                type: "get",
                url: window.location.href,
                data: {
                    "key": numRows
                },
                dataType: "json",
                success: function(response) {
                    $(tbody).append(response.tr);
                }
            });
        });

        $('.custom-file-input').change(function(e) {
            var reader = new FileReader();
            var preview = $(this).closest('.form-group').find('.preview img');
            console.log(preview);
            reader.onload = function(e) {
                preview.attr('src', e.target.result).show();
            }
            reader.readAsDataURL(this.files[0]);
        });

        // $('#custom-tabs-for-webcontent-tab').click(function (e) {
        //     e.preventDefault();
        //     $.ajax({
        //         type: "get",
        //         url: $(this).data('href'),
        //         // data: "data",
        //         dataType: "json",
        //         success: function (response) {
        //             // console.log(response);

        //         }
        //     });
        // });
    </script>
@endpush
