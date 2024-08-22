<div class="card-body p-0 table-wrapper">
    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>{{ __('Title') }}</th>
                <th>{{ __('Type') }}</th>
                <th>{{ __('Thumbnail') }}</th>
                {{-- <th>{{ __('Content') }}</th> --}}
                <th>{{ __('Status') }}</th>
                <th>{{ __('Action') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($news as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->title }}</td>
                    <td>{{ $item->type }}</td>

                    <td>
                        <img width="30%"
                            src="
                        @if ($item->thumbnail && file_exists(public_path('uploads/News/' . $item->thumbnail))) {{ asset('uploads/News/' . $item->thumbnail) }}
                        @else
                            {{ asset('uploads/image/default.png') }} @endif
                        "
                            alt="" class="profile_img_table">

                        <span class="ml-2">
                            {{ $item->title }}
                        </span>
                    </td>
                    {{-- <td>{{ $item->content }}</td> --}}
                    <td>
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input switcher_input status"
                                id="status_{{ $item->id }}" data-id="{{ $item->id }}"
                                {{ $item->status == 1 ? 'checked' : '' }} name="status">
                            <label class="custom-control-label" for="status_{{ $item->id }}"></label>
                        </div>
                    </td>

                    <td>
                        <a href="{{ route('admin.news.edit', $item->id) }}" class="btn btn-info btn-sm btn-edit">
                            <i class="fas fa-pencil-alt"></i>
                            {{ __('Edit') }}
                        </a>
                        <form action="{{ route('admin.news.destroy', $item->id) }}"
                            class="d-inline-block form-delete-{{ $item->id }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" data-id="{{ $item->id }}"
                                data-href="{{ route('admin.news.destroy', $item->id) }}"
                                class="btn btn-danger btn-sm btn-delete">
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
                <div class="col-12 text-center text-sm-left pl-3" style="margin-block: 20px">
                    {{ __('Showing') }} {{ $news->firstItem() }} {{ __('to') }} {{ $news->lastItem() }}
                    {{ __('of') }} {{ $news->total() }} {{ __('entries') }}
                </div>
                <div class="col-12 pagination-nav pr-3"> {{ $news->links() }}</div>
            </div>
        </div>
    </div>
</div>
