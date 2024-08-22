<div class="card-body p-0 table-wrapper">
    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>{{ __('Title') }}</th>
                <th>{{ __('Curriculum Image') }}</th>
                <th>{{ __('Assessment Detail') }}</th>
                <th>{{ __('Icon') }}</th>
                <th>{{ __('Description') }}</th>
                <th>{{ __('Action') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($courses as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    {{-- <td>{{ $item->id }}</td> --}}
                    <td>{{ $item->title }}</td>
                    <td>
                        <img width="30%" height="auto" src="
                        @if ($item->curriculum_image && file_exists(public_path('uploads/Course/' . $item->curriculum_image))) {{ asset('uploads/Course/' . $item->curriculum_image) }}
                        @else
                            {{ asset('uploads/image/default.png') }} @endif
                        "
                            alt="" class="profile_img_table">
                    </td>
                    <td>
                        <img width="30%" height="auto" src="
                        @if ($item->assessment_detail && file_exists(public_path('uploads/Course/' . $item->assessment_detail))) {{ asset('uploads/Course/' . $item->assessment_detail) }}
                        @else
                            {{ asset('uploads/image/default.png') }} @endif
                        "
                            alt="" class="profile_img_table">


                    </td>
                    <td>
                        <img width="50%" height="auto" src="
                        @if ($item->icon && file_exists(public_path('uploads/Course/' . $item->icon))) {{ asset('uploads/Course/' . $item->icon) }}
                        @else
                            {{ asset('uploads/image/default.png') }} @endif
                        "
                            alt="" class="profile_img_table">


                    </td>
                    <td>{{ $item->description }}</td>

                    {{-- <td>
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input switcher_input status" id="status_{{ $product->id }}" data-id="{{ $product->id }}" {{ $product->status == 1 ? 'checked' : '' }} name="status">
                            <label class="custom-control-label" for="status_{{ $product->id }}"></label>
                        </div>
                    </td> --}}
                    <td>
                        <a href="{{ route('admin.course-category.edit', $item->id) }}" class="btn btn-info btn-sm btn-edit">
                            <i class="fas fa-pencil-alt"></i>
                            {{ __('Edit') }}
                        </a>


                        <form action="{{ route('admin.course-category.destroy', $item->id) }}" class="d-inline-block form-delete-{{ $item->id }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" data-id="{{ $item->id }}" data-href="{{ route('admin.course-category.destroy', $item->id) }}" class="btn btn-danger btn-sm btn-delete">
                                <i class="fa fa-trash-alt"></i>
                                {{ __('Delete') }}
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="row">
        <div class="col-12 d-flex flex-row flex-wrap">
            <div class="row" style="width: -webkit-fill-available;">
                <div class="col-12  text-center text-sm-left pl-3" style="margin-block: 20px">
                    {{ __('Showing') }} {{ $courses->firstItem() }} {{ __('to') }} {{ $courses->lastItem() }} {{ __('of') }} {{ $courses->total() }} {{ __('entries') }}
                </div>
                <div class="col-12  pagination-nav pr-3"> {{ $courses->links() }}</div>
            </div>
        </div>
    </div>


</div>
