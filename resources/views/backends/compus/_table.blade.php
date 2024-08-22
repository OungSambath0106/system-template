<div class="card-body p-0 table-wrapper">
    <table class="table">
        <thead>
            <tr>
                <th >#</th>
                <th>{{ __('Name') }}</th>
                <th>{{ __('Address') }}</th>
                <th>{{ __('Status') }}</th>
                <th>{{ __('Action') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($compuses as $compus)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $compus->name }}</td>
                    <td>{{ $compus->address }}</td>
                    <td>
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input switcher_input status" id="status_{{ $compus->id }}" data-id="{{ $compus->id }}" {{ $compus->status == 1 ? 'checked' : '' }} name="status">
                            <label class="custom-control-label" for="status_{{ $compus->id }}"></label>
                        </div>
                    </td>
                    <td>
                        <a href="{{ route('admin.compus.edit', $compus->id) }}" class="btn btn-info btn-sm btn-edit">
                            <i class="fas fa-pencil-alt"></i>
                            {{ __('Edit') }}
                        </a>

                        <form action="{{ route('admin.compus.destroy', $compus->id) }}" class="d-inline-block form-delete-{{ $compus->id }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" data-id="{{ $compus->id }}" data-href="{{ route('admin.compus.destroy', $compus->id) }}" class="btn btn-danger btn-sm btn-delete">
                                <i class="fa fa-trash-alt"></i>
                                {{ __('Delete') }}
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
