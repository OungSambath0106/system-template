<div class="card-body p-0 table-wrapper">
    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th class="">{{ __('Title') }}</th>
                <th>{{ __('Department') }}</th>
                <th>{{ __('Grade') }}</th>
                <th>{{ __('Status') }}</th>
                <th>{{ __('Action') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($students as $student)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $student->title }}</td>
                    <td>
                        @if ($student->department)
                            {{ $student->department->name }}
                            @if ($student->department->compus)
                                | {{ $student->department->compus->name }}
                            @else
                                | Null
                            @endif
                        @else
                            Null
                        @endif
                    </td>
                    <td>
                        @if ($student->grade)
                            {{ $student->grade->name }}
                        @else
                            Null
                        @endif
                    </td>
                    <td>
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input switcher_input status"
                                id="status_{{ $student->id }}" data-id="{{ $student->id }}"
                                {{ $student->status == 1 ? 'checked' : '' }} name="status">
                            <label class="custom-control-label" for="status_{{ $student->id }}"></label>
                        </div>
                    </td>
                    <td>
                        <a href="{{ route('admin.student.edit', $student->id) }}" class="btn btn-info btn-sm btn-edit">
                            <i class="fas fa-pencil-alt"></i>
                            {{ __('Edit') }}
                        </a>


                        <form action="{{ route('admin.student.destroy', $student->id) }}"
                            class="d-inline-block form-delete-{{ $student->id }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" data-id="{{ $student->id }}"
                                data-href="{{ route('admin.student.destroy', $student->id) }}"
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
                    {{ __('Showing') }} {{ $students->firstItem() }} {{ __('to') }}
                    {{ $students->lastItem() }} {{ __('of') }} {{ $students->total() }}
                    {{ __('entries') }}
                </div>
                <div class="col-12  pagination-nav pr-3"> {{ $students->links() }}</div>
            </div>
        </div>
    </div>


</div>
