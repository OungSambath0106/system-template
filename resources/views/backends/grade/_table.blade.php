<div class="card-body p-0 table-wrapper">
    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th class="">{{ __('Name') }}</th>
                <th>{{ __('Department') }}</th>
                <th>{{ __('Status') }}</th>
                <th>{{ __('Action') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($grades as $grade)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $grade->name }}</td>
                    {{-- <td>{{ $grade->department->name }}</td> --}}
                    <td>
                        @if ($grade->department)
                            {{-- @dd(0); --}}
                            {{ $grade->department->name }}|
                            {{ $grade->department->compus->name }}
                        @else
                            Null
                        @endif
                    </td>

                    <td>
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input switcher_input status"
                                id="status_{{ $grade->id }}" data-id="{{ $grade->id }}"
                                {{ $grade->status == 1 ? 'checked' : '' }} name="status">
                            <label class="custom-control-label" for="status_{{ $grade->id }}"></label>
                        </div>
                    </td>
                    <td>
                        <a href="{{ route('admin.grade.edit', $grade->id) }}" class="btn btn-info btn-sm btn-edit">
                            <i class="fas fa-pencil-alt"></i>
                            {{ __('Edit') }}
                        </a>


                        <form action="{{ route('admin.grade.destroy', $grade->id) }}"
                            class="d-inline-block form-delete-{{ $grade->id }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" data-id="{{ $grade->id }}"
                                data-href="{{ route('admin.grade.destroy', $grade->id) }}"
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
                <div class="col-12  text-center text-sm-left pl-3" style="margin-block: 20px">
                    {{ __('Showing') }} {{ $grades->firstItem() }} {{ __('to') }}
                    {{ $grades->lastItem() }} {{ __('of') }} {{ $grades->total() }}
                    {{ __('entries') }}
                </div>
                <div class="col-12  pagination-nav pr-3"> {{ $grades->links() }}</div>
            </div>
        </div>
    </div>


</div>
