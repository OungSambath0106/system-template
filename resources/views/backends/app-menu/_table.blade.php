<div class="card-body p-0 table-wrapper">
    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>{{ __('Title') }}</th>
                <th>{{ __('Description') }}</th>
                <th>{{ __('Icon') }}</th>
                <th>{{ __('Image') }}</th>
                {{-- <th>{{ __('Sort_Order') }}</th> --}}
                <th>{{ __('Status') }}</th>
                <th>{{ __('Action') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($menus as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->title }}</td>
                    <td>{{ $item->description }}</td>
                    <td>
                        <img width="30%" height="auto" src="
                        @if ($item->icon && file_exists(public_path('uploads/menus/' . $item->icon))) {{ asset('uploads/menus/' . $item->icon) }}
                        @else
                            {{ asset('uploads/image/default.png') }} @endif
                        "
                            alt="" class="profile_img_table">

                        {{-- <span class="ml-2">
                            {{ $item->title }}
                        </span> --}}
                    </td>
                    <td>
                        <img width="30%" height="auto" src="
                        @if ($item->image && file_exists(public_path('uploads/menus/' . $item->image))) {{ asset('uploads/menus/' . $item->image) }}
                        @else
                            {{ asset('uploads/image/default.png') }} @endif
                        "
                            alt="" class="profile_img_table">

                        {{-- <span class="ml-2">
                            {{ $item->title }}
                        </span> --}}
                    </td>
                    {{-- <td>{{ $item->sort_order }}</td> --}}

                    <td>
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input switcher_input status"
                                id="status_{{ $item->id }}" data-id="{{ $item->id }}"
                                {{ $item->status == 1 ? 'checked' : '' }} name="status">
                            <label class="custom-control-label" for="status_{{ $item->id }}"></label>
                        </div>
                    </td>

                    <td>
                        <a href="{{ route('admin.menu.edit', $item->id) }}" class="btn btn-info btn-sm btn-edit">
                            <i class="fas fa-pencil-alt"></i>
                            {{ __('Edit') }}
                        </a>
                        <form action="{{ route('admin.menu.destroy', $item->id) }}"
                            class="d-inline-block form-delete-{{ $item->id }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" data-id="{{ $item->id }}"
                                data-href="{{ route('admin.menu.destroy', $item->id) }}"
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
                    {{ __('Showing') }} {{ $menus->firstItem() }} {{ __('to') }} {{ $menus->lastItem() }}
                    {{ __('of') }} {{ $menus->total() }} {{ __('entries') }}

                </div>
                <div class="col-12 pagination-nav pr-3"> {{ $menus->links() }}</div>
            </div>
        </div>
    </div>
</div>
