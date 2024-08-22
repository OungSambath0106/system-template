<div class="card-body p-0 table-wrapper">
    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>{{ __('Image') }}</th>
                <th>{{ __('Full Name') }}</th>
                <th>{{ __('Username') }}</th>
                <th>{{ __('Email') }}</th>
                <th>{{ __('Gender') }}</th>
                <th>{{ __('Created At') }}</th>
                <th>{{ __('Status') }}</th>
                {{-- <th>{{ __('DOB') }}</th> --}}
                <th>{{ __('Action') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($users as $user)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>
                        <img src="
                        @if ($user->image && file_exists(public_path('uploads/users/' . $user->image))) {{ asset('uploads/users/' . $user->image) }}
                        @else
                            {{ asset('uploads/default-profile.png') }} @endif
                        "
                            alt="" class="profile_img_table user-image img-circle elevation-2">
                    </td>
                    <td>{{ $user->full_name }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->gender }}</td>
                    <td>{{ $user->created_at->format('M-d-Y') }}</td>
                    <td>
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input switcher_input status"
                                id="status_{{ $user->id }}" data-id="{{ $user->id }}"
                                {{ $user->status == 1 ? 'checked' : '' }} name="status">
                            <label class="custom-control-label" for="status_{{ $user->id }}"></label>
                        </div>
                    </td>
                    {{-- <td>{{ $user->dob }}</td> --}}
                    <td>
                        <a href="{{ route('admin.user.edit', $user->id) }}" class="btn btn-info btn-sm btn-edit">
                            <i class="fas fa-pencil-alt"></i>
                            {{ __('Edit') }}
                        </a>

                        <form action="{{ route('admin.user.destroy', $user->id) }}"
                            class="d-inline-block form-delete-{{ $user->id }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" data-id="{{ $user->id }}"
                                data-href="{{ route('admin.user.destroy', $user->id) }}"
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

    {{-- <div class="row">
        <div class="col-12 d-flex flex-row flex-wrap">
            <div class="row" style="width: -webkit-fill-available;">
                <div class="col-12 col-sm-6 text-center text-sm-left pl-3" style="margin-block: 20px">
                    {{ __('Showing') }} {{ $grades->firstItem() }} {{ __('to') }} {{ $grades->lastItem() }} {{ __('of') }} {{ $grades->total() }} {{ __('entries') }}
                </div>
                <div class="col-12 col-sm-6 pagination-nav pr-3"> {{ $grades->links() }}</div>
            </div>
        </div>
    </div> --}}


</div>
