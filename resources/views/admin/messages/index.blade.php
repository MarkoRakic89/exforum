@extends('layouts.contentNavbarLayout')
@php use Illuminate\Support\Str; @endphp

@section('content')
<h1>Messages</h1>
<div class="card">
    <div class="table-responsive text-nowrap">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Reservation</th>
                    <th>Sender</th>
                    <th>Body</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @foreach($messages as $message)
                <tr>
                    <td>{{ $message->id }}</td>
                    <td>{{ $message->reservation_id }}</td>
                    <td>{{ $message->sender->naziv }}</td>
                    <td>{{ Str::limit(strip_tags($message->body), 50) }}</td>
                    <td>{{ $message->created_at }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
{{ $messages->links('admin.bootstrap-5') }}
@endsection