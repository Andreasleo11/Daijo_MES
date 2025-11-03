<x-app-layout>
<form action="{{ route('generate.machine.list') }}" method="POST">
    @csrf
    <div class="mb-3">
        <label for="machine_name" class="form-label">Select Machine</label>
        <select name="machine_name" id="machine_name" class="form-select">
            <option value="">-- Select Machine --</option>
            @foreach($users as $id => $name)
                <option value="{{ $name }}">{{ $name }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="btn btn-primary">Generate Machine List</button>
</form>



</x-app-layout>