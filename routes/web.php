<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ClassLevelController;
use App\Http\Controllers\StreamController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TeacherAssignmentController;
use App\Http\Controllers\MarkController;
use App\Http\Controllers\StudentAssignmentController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\VideoController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ChatAdminController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\FeeCategoryController;
use App\Http\Controllers\FeeStructureController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ExpenseCategoryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\DormitoryController;
use App\Http\Controllers\RoomAssignmentController;
use App\Http\Controllers\ClubController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\BookCheckoutController;
use App\Http\Controllers\ResourceController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\AiController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\BulkMessageController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\ExamController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/about', function () {
    return view('about');
})->name('about');

Route::get('/dashboard', function () {
    $userRole = auth()->user()->role;
    if ($userRole === \App\Enums\Role::STUDENT) {
        return redirect()->route('student.dashboard');
    }
    if ($userRole === \App\Enums\Role::PARENT) {
        return redirect()->route('parent.dashboard');
    }
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // User Management
    Route::group(['middleware' => ['role:root,headteacher']], function () {
        Route::get('users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('users', [UserController::class, 'store'])->name('users.store');
        Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::patch('users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::resource('class-levels', ClassLevelController::class);
        Route::resource('class-levels.streams', StreamController::class)->except(['show'])->shallow();
        Route::resource('subjects', SubjectController::class);
        Route::get('subjects/{subject}/manage-papers', [SubjectController::class, 'managePapers'])->name('subjects.manage-papers');
        Route::post('subjects/{subject}/papers', [SubjectController::class, 'storePapers'])->name('subjects.papers.store');
        Route::get('teacher-assignments/create', [TeacherAssignmentController::class, 'create'])->name('teacher-assignments.create');
        Route::post('teacher-assignments', [TeacherAssignmentController::class, 'store'])->name('teacher-assignments.store');
        Route::get('student-assignments', [StudentAssignmentController::class, 'index'])->name('student-assignments.index');
        Route::post('student-assignments', [StudentAssignmentController::class, 'store'])->name('student-assignments.store');
    });

    // Student Management
    Route::group(['middleware' => ['role:root,headteacher']], function () {
        Route::get('students', [StudentController::class, 'index'])->name('students.index');
        Route::post('students/import', [StudentController::class, 'import'])->name('students.import');
        Route::post('students/{user}/photo', [StudentController::class, 'updatePhoto'])->name('students.photo.update');
        Route::get('students/search', [StudentController::class, 'search'])->name('students.search');
        Route::get('students/upload', [StudentController::class, 'showUploadForm'])->name('students.upload.form');
        Route::get('students/template', [StudentController::class, 'downloadTemplate'])->name('students.template');
        Route::get('students/export/pdf', [StudentController::class, 'exportPdf'])->name('students.export.pdf');
        Route::get('students/export/excel', [StudentController::class, 'exportExcel'])->name('students.export.excel');
        Route::get('students/{user}/report-card/{stream}', [StudentController::class, 'generateReportCard'])->name('students.report-card');
        Route::get('students/{user}/id-card', [StudentController::class, 'generateIdCard'])->name('students.id-card');
    });

    // Financial Management
    Route::group(['middleware' => ['role:root,headteacher,bursar']], function () {
        Route::resource('fee-categories', FeeCategoryController::class)->except(['create', 'show', 'edit']);
        Route::resource('expense-categories', ExpenseCategoryController::class)->except(['create', 'show', 'edit']);
        Route::resource('fee-structures', FeeStructureController::class);
        Route::resource('invoices', InvoiceController::class)->except(['edit', 'update']);
        Route::post('invoices/{invoice}/payments', [PaymentController::class, 'store'])->name('invoices.payments.store');
        Route::resource('expenses', ExpenseController::class);
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    });

    // Library Management
    Route::group(['middleware' => ['role:root,headteacher,librarian']], function () {
        Route::resource('books', BookController::class);
        Route::get('checkouts', [BookCheckoutController::class, 'index'])->name('checkouts.index');
        Route::get('checkouts/create', [BookCheckoutController::class, 'create'])->name('checkouts.create');
        Route::post('checkouts', [BookCheckoutController::class, 'store'])->name('checkouts.store');
        Route::patch('checkouts/{checkout}', [BookCheckoutController::class, 'update'])->name('checkouts.update');
    });

    // Resource & Inventory Management
    Route::group(['middleware' => 'role:root,headteacher,bursar'], function() {
        Route::resource('inventory', InventoryController::class);
    });
    Route::group(['middleware' => 'role:root,headteacher'], function() {
        Route::resource('resources', ResourceController::class);
    });
    Route::group(['middleware' => 'role:root,headteacher,teacher'], function() {
        Route::get('bookings', [BookingController::class, 'index'])->name('bookings.index');
        Route::post('bookings', [BookingController::class, 'store'])->name('bookings.store');
        Route::delete('bookings/{booking}', [BookingController::class, 'destroy'])->name('bookings.destroy');
    });

    // Welfare, Activities & Announcements
    Route::group(['middleware' => ['role:root,headteacher']], function () {
        Route::resource('dormitories', DormitoryController::class);
        Route::post('dormitories/{dormitory}/rooms', [DormitoryController::class, 'storeRoom'])->name('dormitories.rooms.store');
        Route::delete('dormitory-rooms/{room}', [DormitoryController::class, 'destroyRoom'])->name('dormitory-rooms.destroy');
        Route::resource('room-assignments', RoomAssignmentController::class)->only(['index', 'store']);
        Route::delete('room-assignments/{userId}/{roomId}', [RoomAssignmentController::class, 'destroy'])->name('room-assignments.destroy');
        Route::resource('clubs', ClubController::class);
        Route::post('clubs/{club}/members', [ClubController::class, 'addMember'])->name('clubs.members.store');
        Route::delete('clubs/{club}/members/{member}', [ClubController::class, 'removeMember'])->name('clubs.members.destroy');
        Route::resource('announcements', AnnouncementController::class);
    });

    // Communication
    Route::group(['middleware' => ['role:root,headteacher']], function () {
        Route::get('bulk-messages/create', [BulkMessageController::class, 'create'])->name('bulk-messages.create');
        Route::post('bulk-messages', [BulkMessageController::class, 'store'])->name('bulk-messages.store');
    });

    // Document Generation
    Route::group(['middleware' => ['role:root,headteacher'], 'prefix' => 'documents', 'as' => 'documents.'], function () {
        Route::get('select-id-card', [DocumentController::class, 'selectIdCard'])->name('id-card.select');
        Route::post('generate-id-card', [DocumentController::class, 'generateIdCard'])->name('id-card.generate');
        Route::get('select-report-card', [DocumentController::class, 'selectReportCard'])->name('report-card.select');
        Route::post('generate-report-card', [DocumentController::class, 'generateReportCard'])->name('report-card.generate');
    });

    // Admin-only
    Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => ['auth', 'role:root,headteacher']], function () {
        Route::get('ai-reports', [AiController::class, 'index'])->name('ai.index');
        Route::get('chat', [ChatAdminController::class, 'index'])->name('chat.index');
        Route::get('backups', [BackupController::class, 'index'])->name('backups.index');
        Route::post('backups', [BackupController::class, 'store'])->name('backups.store');
        Route::get('backups/{fileName}/download', [BackupController::class, 'download'])->name('backups.download');
        Route::delete('backups/{fileName}', [BackupController::class, 'destroy'])->name('backups.destroy');
    });

    // Teacher-specific
    Route::group(['middleware' => 'role:teacher,headteacher'], function() {
        Route::get('marks', [MarkController::class, 'index'])->name('marks.index');
        Route::get('marks/enter/{paper_stream_user_id}', [MarkController::class, 'enter'])->name('marks.enter');
        Route::post('marks', [MarkController::class, 'store'])->name('marks.store');
        Route::resource('exams', ExamController::class)->only(['index', 'create', 'store']);
    });

    // General authenticated routes
    Route::get('videos', [VideoController::class, 'index'])->name('videos.index');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('teacher/chat', [ChatController::class, 'index'])->name('teacher.chat.index');

    // Add the show route for users at the end to avoid conflicts
    Route::get('users/{user}', [UserController::class, 'show'])->name('users.show');
});

require __DIR__.'/auth.php';
