<!DOCTYPE html>
<html lang="th">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>จัดการผู้ใช้ - Admin</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
        <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <style>body{font-family:'Prompt',sans-serif;background:#f8fafc}.admin-nav{background:#172554}.admin-nav a{color:white;text-decoration:none}</style>
    </head>
    <body>
        <x-admin-navbar />
        <main class="container py-5">
            <h1 class="h3 fw-bold mb-4">จัดการผู้ใช้</h1>
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif
            <div class="table-responsive bg-white rounded shadow-sm">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>
                                ชื่อ
                            </th>
                            <th>
                                อีเมล
                            </th>
                            <th>
                                บทบาท
                            </th>
                            <th>
                                สถานะ
                            </th>
                            <th>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                            <tr>
                                <td>
                                    {{ $user->first_name }} {{ $user->last_name }}
                                </td>
                                <td>
                                    {{ $user->email }}
                                </td>
                                <td>
                                    {{ $user->current_role }}
                                </td>
                                <td>
                                    <span class="badge text-bg-{{ $user->is_active ? 'success' : 'secondary' }}">
                                        {{ $user->is_active ? 'ใช้งาน' : 'ปิดใช้งาน' }}
                                    </span>
                                    @if(!$user->is_active)
                                        <p class="small mt-2 mb-0">เหตุผล: {{ $user->disabled_reason ?: 'ยังไม่ได้ระบุ' }}</p>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <form action="{{ route('admin.users.toggle-status', $user) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        @if($user->is_active && (string)$user->user_id !== (string)auth()->id())
                                            <label class="form-label small" for="reason-{{ $user->user_id }}">เหตุผลที่ปิดใช้งาน (ผู้ใช้จะเห็นข้อความนี้)</label>
                                            <textarea id="reason-{{ $user->user_id }}" name="disabled_reason" class="form-control mb-2" rows="2" required maxlength="2000"></textarea>
                                        @endif
                                        <button class="btn btn-sm btn-outline-{{ $user->is_active ? 'danger' : 'success' }}" @disabled((string)$user->user_id === (string)auth()->id())>{{ $user->is_active ? 'ปิดใช้งาน' : 'เปิดใช้งาน' }}</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $users->links() }}
            </div>
        </main>
    </body>
</html>
