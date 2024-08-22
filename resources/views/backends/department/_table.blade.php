<div class="card-body p-0 table-wrapper">
    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th class="">{{ __('Name') }}</th>
                <th>{{ __('Campus') }}</th>
                <th>{{ __('Created By') }}</th>
                <th>{{ __('Status') }}</th>
                <th>{{ __('Action') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($departments as $department)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $department->name }}</td>
                    {{-- <td>{{ $department->compus->name }}</td> --}}
                    <td>
                        @if ($department->compus)
                            {{ $department->compus->name }}
                        @else
                            Null
                        @endif
                    </td>
                    <td>{{ $department->createdBy->name }}</td>
                    <td>
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input switcher_input status"
                                id="status_{{ $department->id }}" data-id="{{ $department->id }}"
                                {{ $department->status == 1 ? 'checked' : '' }} name="status">
                            <label class="custom-control-label" for="status_{{ $department->id }}"></label>
                        </div>
                    </td>
                    <td>
                        <a href="{{ route('admin.department.edit', $department->id) }}"
                            class="btn btn-info btn-sm btn-edit">
                            <i class="fas fa-pencil-alt"></i>
                            {{ __('Edit') }}
                        </a>


                        <form action="{{ route('admin.department.destroy', $department->id) }}"
                            class="d-inline-block form-delete-{{ $department->id }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" data-id="{{ $department->id }}"
                                data-href="{{ route('admin.department.destroy', $department->id) }}"
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
                    {{ __('Showing') }} {{ $departments->firstItem() }} {{ __('to') }}
                    {{ $departments->lastItem() }} {{ __('of') }} {{ $departments->total() }}
                    {{ __('entries') }}
                </div>
                <div class="col-12  pagination-nav pr-3"> {{ $departments->links() }}</div>
            </div>
        </div>
    </div>


</div>
